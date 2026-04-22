<?php

namespace App\Services\Auth;

use App\Enums\AuditResult;
use App\Models\AuthCode;
use App\Models\User;
use App\Services\Audit\AuditServiceWrapper as AuditAuditServiceWrapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OTPService
{
    public function __construct(
        private readonly AuditAuditServiceWrapper $audit_service_wrapper
    ) {}

    /**
     * Générer un code OTP pour un utilisateur
     *
     * @param User $user
     * @param int $ttlMinutes
     * @return AuthCode
     */
    public function sendOtp(string $email, Request $request): array
    {
        $ttlMinutes = (int) config('cyberguard.auth.otp.ttl_minutes', 3);
        $codeLength = (int) config('cyberguard.auth.otp.code_length', 8);

        // ✅ NE PAS créer d'utilisateur ici
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Anti-enumération (sécurité)
            sleep(1);

            // Journaliser tentative d'envoi OTP sur email inconnu
            $this->audit_service_wrapper->logCritique(
                'otp.unknown_email',
                'User',
                'OTPService',
                AuditResult::Refuse,
                [
                    'ipAddress' => $request->ip(),
                    'metadata' => [
                        'message' => "Tentative d'envoi OTP sur email inconnu : {$email}",
                        'user_agent' => $request->userAgent(),
                    ]
                ]
            );

            return [
                'success' => false
            ];
        }

        // Invalider les anciens OTP
        AuthCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $maxNumber = (10 ** $codeLength) - 1;
        $code = str_pad((string) random_int(0, $maxNumber), $codeLength, '0', STR_PAD_LEFT);

        $authCode = AuthCode::create([
            'user_id'    => $user->id,
            'email'      => $email,
            'code_hash'  => hash('sha256', $code),
            'expires_at' => now()->addMinutes($ttlMinutes),
            'attempts'   => 0,
            'ip_address' => $request->ip(),
        ]);

        // Envoi email
        Mail::to($user->email)->send(
            new \App\Mail\OTPMail($authCode, $code)
        );

        // Audit
        $this->audit_service_wrapper->logElevee(
            'otp.envoi',
            'User',
            'OTPService',
            AuditResult::Autorise,
            [
                'entityId' => $user->id,
                'actorId' => $user->id,
                'ipAddress' => $request->ip(),
                'metadata' => [
                    'message' => 'OTP envoyé après authentification réussie.',
                    'user_agent' => $request->userAgent(),
                ]
            ]
        );

        return [
            'success' => true,
            'email' => $email,
            'debug_otp' => app()->environment('local') ? $code : null
        ];
    }

    /**
     * Vérifier un code OTP pour un utilisateur
     * 🔐 CORRECTION : Refactorisé pour réduire les retours (sonarqube)
     *
     * @param string $email
     * @param string $code
     * @param Request $request
     * @return array
     */
    public function verifyOtp(string $email, string $code, Request $request): array
    {
        return $this->validateOtpRequest($email, $code, $request);
    }

    /**
     * Valide la requête OTP et retourne le résultat
     * 🔐 CORRECTION : Refactorisé pour réduire les retours (sonarqube: max 3 returns)
     */
    private function validateOtpRequest(string $email, string $code, Request $request): array
    {
        // Étape 1: Vérifier l'utilisateur
        $user = User::where('email', $email)->first();
        if (!$user) {
            return $this->handleUnknownUser($email, $request);
        }

        // Étape 2: Récupérer le code OTP valide
        $authCode = $this->getValidAuthCode($user);
        if (!$authCode) {
            return $this->handleNoAuthCode($user, $request);
        }

        // Étape 3: Traiter la vérification (tentatives + code + succès)
        return $this->processVerification($authCode, $user, $code, $request);
    }

    /**
     * Traite la vérification OTP (tentatives, code et succès)
     */
    private function processVerification(AuthCode $authCode, User $user, string $code, Request $request): array
    {
        $maxAttempts = (int) config('cyberguard.auth.otp.max_attempts', 3);

        // Vérifier les tentatives
        if ($authCode->hasExceededAttempts($maxAttempts)) {
            return $this->handleExceededAttempts($authCode, $user, $request);
        }

        // Vérifier le code
        if (!hash_equals($authCode->code_hash, hash('sha256', $code))) {
            return $this->handleWrongCode($authCode, $user, $request, $maxAttempts);
        }

        // Code correct - vérifier le compte
        return $this->handleSuccess($authCode, $user, $request);
    }

    private function handleUnknownUser(string $email, Request $request): array
    {
        $this->audit_service_wrapper->logCritique(
            'otp.verification',
            'User',
            'OTPService',
            AuditResult::Refuse,
            [
                'ipAddress' => $request->ip(),
                'metadata' => [
                    'message' => "Email inconnu : {$email}",
                    'user_agent' => $request->userAgent(),
                ]
            ]
        );

        return ['success' => false, 'message' => 'Code invalide ou expiré.'];
    }

    private function handleNoAuthCode(User $user, Request $request): array
    {
        $this->audit_service_wrapper->logElevee(
            'otp.verification',
            'User',
            'OTPService',
            AuditResult::Refuse,
            [
                'entityId' => $user->id,
                'actorId' => $user->id,
                'ipAddress' => $request->ip(),
                'metadata' => [
                    'message' => 'Code OTP expiré ou introuvable.',
                    'user_agent' => $request->userAgent(),
                ]
            ]
        );

        return ['success' => false, 'message' => 'Code invalide ou expiré.'];
    }

    private function handleExceededAttempts(AuthCode $authCode, User $user, Request $request): array
    {
        $authCode->markAsUsed();

        $this->audit_service_wrapper->logElevee(
            'otp.verification',
            'User',
            'OTPService',
            AuditResult::Refuse,
            [
                'entityId' => $user->id,
                'actorId' => $user->id,
                'ipAddress' => $request->ip(),
                'metadata' => [
                    'message' => "Nombre maximal de tentatives OTP dépassé ({$authCode->attempts}).",
                    'user_agent' => $request->userAgent(),
                ]
            ]
        );

        return ['success' => false, 'message' => 'Trop de tentatives, demandez un nouveau code.'];
    }

    private function handleWrongCode(AuthCode $authCode, User $user, Request $request, int $maxAttempts): array
    {
        $authCode->incrementAttempts();

        $this->audit_service_wrapper->logElevee(
            'otp.wrong_code',
            'User',
            'OTPService',
            AuditResult::Refuse,
            [
                'entityId' => $user->id,
                'actorId' => $user->id,
                'ipAddress' => $request->ip(),
                    'metadata' => [
                        'message' => "OTP incorrect (tentative {$authCode->attempts})",
                        'user_agent' => $request->userAgent(),
                        'remaining_attempts' => max(0, $maxAttempts - $authCode->attempts),
                    ]
                ]
            );

        if ($authCode->attempts >= $maxAttempts) {
            $this->audit_service_wrapper->logCritique(
                'otp.brute_force_attempt',
                'User',
                'OTPService',
                AuditResult::Refuse,
                [
                    'entityId' => $user->id,
                    'actorId' => $user->id,
                    'ipAddress' => $request->ip(),
                    'metadata' => [
                        'message' => 'Tentative de force brute détectée sur OTP',
                        'attempt_count' => $authCode->attempts,
                        'timeframe' => config('cyberguard.auth.otp.ttl_minutes', 3) . ' minutes',
                        'user_agent' => $request->userAgent(),
                    ]
                ]
            );
        }

        return ['success' => false, 'message' => 'Code invalide ou expiré'];
    }

    private function handleSuccess(AuthCode $authCode, User $user, Request $request): array
    {
        $authCode->markAsUsed();

        if (!$user->isActive()) {
            return $this->handleDisabledAccount($user, $request);
        }

        $this->audit_service_wrapper->logElevee(
            'otp.verification',
            'User',
            'OTPService',
            AuditResult::Autorise,
            [
                'entityId' => $user->id,
                'actorId' => $user->id,
                'ipAddress' => $request->ip(),
                'metadata' => [
                    'message' => '2FA validé avec succès.',
                    'user_agent' => $request->userAgent(),
                ]
            ]
        );

        return ['success' => true, 'user' => $user];
    }

    private function handleDisabledAccount(User $user, Request $request): array
    {
        $this->audit_service_wrapper->logCritique(
            'otp.verification',
            'User',
            'OTPService',
            AuditResult::Refuse,
            [
                'entityId' => $user->id,
                'actorId' => $user->id,
                'ipAddress' => $request->ip(),
                'metadata' => [
                    'message' => 'Compte désactivé.',
                    'user_agent' => $request->userAgent(),
                ]
            ]
        );

        return ['success' => false, 'message' => 'Compte désactivé'];
    }

    /**
     * Récupère un code d'authentification valide pour l'utilisateur
     */
    private function getValidAuthCode(User $user): ?AuthCode
    {
        return AuthCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }
}
