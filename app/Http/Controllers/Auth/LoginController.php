<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\OTPService as AuthOTPService;
use App\Services\Auth\SecuritySessionService as AuthSecuritySessionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct(
        private readonly AuthOTPService $otpService,
        private readonly AuthSecuritySessionService $sessionService,
    ) {}

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
    public function sendOtp(Request $request)
    {
        // Validation manuelle avec les mêmes règles que SendOtpRequest
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first());
        }

        $credentials = $request->all();
        $email = $credentials['email'];
        $password = $credentials['password'];

        // 🔐 Vérification email + password
        if (!Auth::attempt([
            'email' => $email,
            'password' => $password
        ])) {
            // On peut faire un audit ici via OTPService si méthode ajoutée
            return back()->with('error', 'Identifiants incorrects');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            // Simuler l'envoi pour éviter l’énumération
            sleep(1);
            return redirect()->back()->withInput()
                ->with('error', 'Si un compte existe, un code a été envoyé.');
        }

        $result = $this->otpService->sendOtp(email: $email, request: $request);

        if (!$result['success']) {
            return redirect()->back()->withInput()
                ->with('error', 'Impossible d’envoyer le code OTP');
        }

        // Dev only: stocker OTP pour debug
        if (app()->environment('local')) {
            session([
                'otp_email' => $email,
                'debug_otp' => $result['debug_otp'] ?? null,
            ]);
            // Afficher le code OTP en toast pour le développement
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
        // Validation uniquement sur l'email
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first());
        }

        $email = $request->email;

        // Appel direct à OTPService->sendOtp() avec uniquement l'email
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
            // Afficher le code OTP en toast pour le développement
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
        $email = session('otp_email');
        if (!$email) {
            return redirect()->route('login')
                ->with('error', 'Veuillez saisir votre email.');
        }

        return view('auth.verify-otp', compact('email'));
    }

    /**
     * Étape 3 : Vérifier le code OTP et créer une session.
     */
    public function verifyOtp(Request $request)
    {
        // Validation manuelle avec les mêmes règles que VerifyOtpRequest
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'code' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return back()->with('error', $validator->errors()->first());
        }

        $validated = $request->all();
        $email = $validated['email'];
        $code  = $validated['code'];

        $result = $this->otpService->verifyOtp(email: $email, code: $code, request: $request);

        if (!$result['success']) {
            return redirect()->back()
                ->withInput()
                ->with('error', $result['message']);
        }

        // Création session sécurisée
        $tokens = $this->sessionService->createSession($result['user'], $request);

        Auth::login($result['user']); // Laravel recognize user

        $cookie = Cookie::make(
            'access_token',
            $tokens['access_token'],
            60, // 1h
            '/',
            null,
            true,
            true // httpOnly
        );

        return redirect()->route('admin.dashboard')
            ->withCookie($cookie)
            ->with('success', 'Authentification réussie ! Bienvenue sur CyberGuard.');
    }
}
