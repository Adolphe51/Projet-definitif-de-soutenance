<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\User;
use App\Services\Auth\OTPService as AuthOTPService;
use App\Services\Auth\SecuritySessionService as AuthSecuritySessionService;
use App\Services\Auth\AuthenticationLoggingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(
        private readonly AuthOTPService $otpService,
        private readonly AuthSecuritySessionService $sessionService,
    ) {
    }

    /**
     * Formulaire de connexion
     */
    public function create()
    {
        return view()->exists('auth.login')
            ? view('auth.login')
            : response(
                '<h1>Login</h1><p>Please POST credentials to /login</p>',
                200,
                ['Content-Type' => 'text/html']
            );
    }

    /**
     * Étape 1 : Envoyer un code OTP à l'adresse email.
     */
    public function sendOtp(SendOtpRequest $request)
    {
        $email = (string) $request->string('email');
        $password = (string) $request->string('password');
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            AuthenticationLoggingService::logAuthenticationAttempt(
                $email,
                false,
                $request,
                'Invalid email or password'
            );

            sleep(1);

            return back()->with('error', 'Identifiants incorrects');
        }

        if (!$user->isActive()) {
            AuthenticationLoggingService::logAuthenticationAttempt(
                $email,
                false,
                $request,
                'Disabled account'
            );

            return back()->with('error', 'Compte désactivé');
        }

        AuthenticationLoggingService::logAuthenticationAttempt(
            $email,
            true,
            $request
        );

        $request->session()->regenerate();
        $this->storePendingAuthentication($request, $user);

        $result = $this->otpService->sendOtp(email: $email, request: $request);
        if (!$result['success']) {
            $this->clearPendingAuthentication($request);

            return redirect()->back()->withInput()
                ->with('error', 'Impossible d’envoyer le code OTP');
        }

        if (app()->environment('local')) {
            session([
                'otp_email' => $email,
                'debug_otp' => $result['debug_otp'] ?? null,
            ]);
            session()->flash('debug_otp_toast', $result['debug_otp'] ?? null);
        } else {
            session(['otp_email' => $email]);
        }

        return redirect()->route('otp.verify.form')
            ->with('success', 'Code OTP envoyé à votre email. Veuillez vérifier votre boîte de réception.');
    }

    /**
     * Renouveler un code OTP (uniquement avec l'email, pas besoin du mot de passe)
     */
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first());
        }

        $email = (string) $request->string('email');
        $pendingAuth = $this->getPendingAuthentication($request);

        if (
            !$pendingAuth
            || !hash_equals($pendingAuth['email'], $email)
        ) {
            $this->clearPendingAuthentication($request);

            return redirect()->route('login')
                ->with('error', 'Votre session de connexion a expiré. Veuillez recommencer.');
        }

        $result = $this->otpService->sendOtp(email: $email, request: $request);

        if (!$result['success']) {
            return redirect()->back()->withInput()
                ->with('error', 'Impossible d’envoyer le code OTP');
        }

        // Stocker l'email en session et afficher le debug OTP en local
        if (app()->environment('local')) {
            session([
                'otp_email' => $email,
                'debug_otp' => $result['debug_otp'] ?? null,
            ]);
            session()->flash('debug_otp_toast', $result['debug_otp'] ?? null);
        } else {
            session(['otp_email' => $email]);
        }

        return redirect()->route('otp.verify.form')
            ->with('success', 'Code OTP renvoyé à votre email. Veuillez vérifier votre boîte de réception.');
    }

    /**
     * Formulaire étape 2
     */
    public function showVerifyForm()
    {
        $pendingAuth = $this->getPendingAuthentication(request());
        $email = $pendingAuth['email'] ?? null;

        if (!$email) {
            return redirect()->route('login')
                ->with('error', 'Votre session de connexion a expiré. Veuillez recommencer.');
        }

        return view('auth.verify-otp', compact('email'));
    }

    /**
     * Étape 3 : Vérifier le code OTP et créer une session.
     */
    public function verifyOtp(VerifyOtpRequest $request)
    {
        $pendingAuth = $this->getPendingAuthentication($request);
        $email = (string) $request->string('email');
        $code = (string) $request->string('code');

        if (
            !$pendingAuth
            || !hash_equals($pendingAuth['email'], $email)
        ) {
            $this->clearPendingAuthentication($request);

            return redirect()->route('login')
                ->with('error', 'Votre session de connexion a expiré. Veuillez recommencer.');
        }

        $result = $this->otpService->verifyOtp(email: $email, code: $code, request: $request);

        if (!$result['success']) {
            AuthenticationLoggingService::logOtpAttempt(
                $email,
                false,
                $request,
                $result['message'] ?? 'Invalid or expired code'
            );
            return redirect()->back()
                ->withInput()
                ->with('error', $result['message']);
        }

        if ((int) $result['user']->id !== (int) $pendingAuth['user_id']) {
            $this->clearPendingAuthentication($request);

            AuthenticationLoggingService::logOtpAttempt(
                $email,
                false,
                $request,
                'Pending authentication mismatch'
            );

            return redirect()->route('login')
                ->with('error', 'Session de connexion invalide. Veuillez recommencer.');
        }

        AuthenticationLoggingService::logOtpAttempt(
            $email,
            true,
            $request
        );

        $tokens = $this->sessionService->createSession($result['user'], $request);

        Auth::login($result['user']);
        $request->session()->regenerate();
        $this->clearPendingAuthentication($request);
        $secureCookie = config('session.secure');
        $secureCookie = is_null($secureCookie) ? $request->isSecure() : (bool) $secureCookie;

        $cookie = Cookie::make(
            'access_token',
            $tokens['access_token'],
            60, // 1h
            '/',
            null,
            $secureCookie,
            true,
            false,
            'strict'
        );

        return redirect()->route('admin.dashboard')
            ->withCookie($cookie)
            ->with('success', 'Authentification réussie ! Bienvenue sur CyberGuard.');
    }

    private function storePendingAuthentication(Request $request, User $user): void
    {
        $request->session()->put('pending_auth', [
            'user_id' => $user->id,
            'email' => $user->email,
            'created_at' => now()->timestamp,
        ]);
    }

    private function getPendingAuthentication(Request $request): ?array
    {
        $pendingAuth = $request->session()->get('pending_auth');

        if (!is_array($pendingAuth)) {
            return null;
        }

        $createdAt = (int) ($pendingAuth['created_at'] ?? 0);
        $ttlMinutes = (int) config('cyberguard.auth.otp.pending_auth_ttl_minutes', 10);
        $expiredAt = now()->subMinutes($ttlMinutes)->timestamp;

        if ($createdAt <= $expiredAt) {
            $this->clearPendingAuthentication($request);

            return null;
        }

        return $pendingAuth;
    }

    private function clearPendingAuthentication(Request $request): void
    {
        $request->session()->forget([
            'pending_auth',
            'otp_email',
            'debug_otp',
            'debug_otp_toast',
        ]);
    }
}
