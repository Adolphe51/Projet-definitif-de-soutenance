<?php

namespace App\Services\Auth;

use App\Enums\AuditImportance;
use App\Enums\AuditResult;
use App\Models\User;
use App\Services\Audit\AuditServiceWrapper;
use App\Services\IntrusionDetectionService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class AuthenticationLoggingService
{
    /**
     * Journalise une tentative de connexion (réussie ou échouée)
     */
    public static function logAuthenticationAttempt(
        string $email,
        bool $success,
        Request $request,
        ?string $reason = null
    ): void {
        $sourceIp = $request->ip();
        $userAgent = $request->userAgent();

        $user = User::where('email', $email)->first();
        $userId = $user?->id;

        // Logs système
        Log::channel('auth')->info('Authentication attempt', [
            'email' => $email,
            'success' => $success,
            'source_ip' => $sourceIp,
            'user_agent' => $userAgent,
            'reason' => $reason,
            'timestamp' => now()->toIso8601String(),
        ]);

        // Journalisation d'audit
        AuditServiceWrapper::log(
            $success ? 'login_success' : 'login_failed',
            'User',
            'login',
            $success ? AuditResult::Autorise : AuditResult::Refuse,
            $success ? AuditImportance::Elevee : AuditImportance::Critique,
            [
                'user' => $user,
                'entityId' => $userId,
                'ipAddress' => $sourceIp,
                'metadata' => [
                    'user_agent' => $userAgent,
                    'reason' => $reason,
                ],
                'newValues' => [
                    'email' => $email,
                    'status' => $success ? 'authenticated' : 'denied',
                ],
            ]
        );

        // Mise à jour de l'IP de l'utilisateur
        if ($success && $user) {
            $user->update([
                'last_ip' => $sourceIp,
                'last_login' => now(),
                'login_attempts' => 0,
            ]);
        }

        // Détection d'intrusion
        if (!$success) {
            $user?->increment('login_attempts');
            IntrusionDetectionService::analyzeAuthenticationAttempt(
                $email,
                $sourceIp,
                false,
                $reason
            );
        }
    }

    /**
     * Journalise une tentative d'OTP
     */
    public static function logOtpAttempt(
        string $email,
        bool $success,
        Request $request,
        ?string $reason = null
    ): void {
        $sourceIp = $request->ip();
        $userAgent = $request->userAgent();

        $user = User::where('email', $email)->first();
        $userId = $user?->id;

        Log::channel('auth')->info('OTP verification attempt', [
            'email' => $email,
            'success' => $success,
            'source_ip' => $sourceIp,
            'user_agent' => $userAgent,
            'reason' => $reason,
            'timestamp' => now()->toIso8601String(),
        ]);

        AuditServiceWrapper::log(
            $success ? 'otp_verified' : 'otp_failed',
            'User',
            'login',
            $success ? AuditResult::Autorise : AuditResult::Refuse,
            $success ? AuditImportance::Elevee : AuditImportance::Critique,
            [
                'user' => $user,
                'entityId' => $userId,
                'ipAddress' => $sourceIp,
                'metadata' => [
                    'user_agent' => $userAgent,
                    'reason' => $reason,
                ],
                'newValues' => [
                    'email' => $email,
                    'status' => $success ? 'otp_verified' : 'otp_denied',
                ],
            ]
        );

        // Détection d'intrusion
        if (!$success) {
            IntrusionDetectionService::analyzeAuthenticationAttempt(
                $email,
                $sourceIp,
                false,
                'OTP failed: ' . ($reason ?? 'Invalid code')
            );
        }
    }

    /**
     * Réinitialise les tentatives échouées pour un utilisateur
     */
    public static function resetLoginAttempts(User $user): void
    {
        $user->update(['login_attempts' => 0]);
    }
}
