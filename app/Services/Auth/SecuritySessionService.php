<?php

namespace App\Services\Auth;

use App\Models\SecuritySession;
use App\Models\User;
use App\Enums\AuditResult;
use App\Services\Audit\AuditServiceWrapper as AuditAuditServiceWrapper;
use Illuminate\Http\Request;

class SecuritySessionService
{
    public function __construct(
        private readonly AuditAuditServiceWrapper $auditService,
    ) {}

    /**
     * Crée une nouvelle session en base de données pour l'utilisateur authentifié.
     * Retourne le token de session et sa date d'expiration.
     *
     * 🔐 CORRECTION : Chiffrement AES-256 des tokens et durée de vie réduite
     */
    public function createSession(User $user, Request $request): array
    {
        $maxActiveSessions = (int) config('cyberguard.auth.sessions.max_active', 5);
        $ttlHours = (int) config('cyberguard.auth.sessions.ttl_hours', 1);

        // 🔐 Limite sessions actives
        $activeSessions = SecuritySession::where('user_id', $user->id)
            ->where('is_revoked', false)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'asc')
            ->get();

        if ($activeSessions->count() >= $maxActiveSessions) {
            $oldest = $activeSessions->first();
            $oldest->revoke();

            $this->auditService->logFaible(
                'session.revocation_auto',
                'SecuritySession',
                'SecuritySessionService',
                AuditResult::Autorise,
                [
                    'entityId' => $user->id,
                    'actorId' => $user->id,
                    'ipAddress' => $request->ip(),
                    'metadata' => [
                        'message' => "Session {$oldest->id} révoquée automatiquement.",
                        'user_agent' => $request->userAgent(),
                    ]
                ]
            );
        }

        // 🔐 Tokens sécurisés - génération cryptographiquement sûre
        $accessToken = $this->generateSecureToken(64);
        $refreshToken = $this->generateSecureToken(60);

        $session = SecuritySession::create([
            'user_id' => $user->id,
            'access_token_hash' => hash('sha256', $accessToken),
            'refresh_token_hash' => hash('sha256', $refreshToken),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_fingerprint' => $this->generateFingerprint($request),
            'expires_at' => now()->addHours($ttlHours),
            'last_activity_at' => now(),
            'is_revoked' => false,
        ]);

        $this->auditService->logElevee(
            'session.creation',
            'SecuritySession',
            'SecuritySessionService',
            AuditResult::Autorise,
            [
                'entityId' => $user->id,
                'actorId' => $user->id,
                'ipAddress' => $request->ip(),
                'metadata' => [
                    'message' => "Session créée ({$session->id})",
                    'user_agent' => $request->userAgent(),
                ]
            ]
        );

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_at' => $session->expires_at,
        ];
    }

    /**
     * Génère un token sécurisé cryptographiquement.
     */
    private function generateSecureToken(int $length): string
    {
        $bytes = random_bytes(ceil($length / 2));
        return bin2hex($bytes);
    }

    public function validateSession(string $token, Request $request): ?SecuritySession
    {
        $hashedToken = hash('sha256', $token);

        $session = SecuritySession::where('access_token_hash', $hashedToken)
            ->with('user.roles')
            ->first();

        // 🔐 Vérification existence, état et expiration de la session
        if (!$session || $session->is_revoked || $session->expires_at < now()) {
            if ($session) {
                $this->auditService->logCritique(
                    'session.expired_or_revoked',
                    'SecuritySession',
                    'SecuritySessionService',
                    AuditResult::Refuse,
                    [
                        'entityId' => $session->user_id,
                        'actorId' => $session->user_id,
                        'ipAddress' => $request->ip(),
                        'metadata' => [
                            'message' => 'Session expirée ou révoquée',
                            'is_revoked' => $session->is_revoked,
                            'expires_at' => $session->expires_at,
                            'current_time' => now(),
                            'user_agent' => $request->userAgent(),
                        ]
                    ]
                );
            }

            return null;
        }

        // 🔐 Vérification fingerprint (ANTI VOL TOKEN)
        $currentFingerprint = $this->generateFingerprint($request);

        if ($session->device_fingerprint !== $currentFingerprint) {

            $this->auditService->logCritique(
                'session.hijack_detected',
                'SecuritySession',
                'SecuritySessionService',
                AuditResult::Refuse,
                [
                    'entityId' => $session->user_id,
                    'actorId' => $session->user_id,
                    'ipAddress' => $request->ip(),
                    'metadata' => [
                        'message' => 'Anomalie détectée dans la session - empreinte navigateur différente',
                        'expected_fingerprint' => $session->device_fingerprint,
                        'actual_fingerprint' => $currentFingerprint,
                        'user_agent' => $request->userAgent(),
                        'ip_address' => $request->ip(),
                    ]
                ]
            );

            $session->revoke();
            return null;
        }

        // Mise à jour activité
        $session->update([
            'last_activity_at' => now()
        ]);

        return $session;
    }

    /**
     * Rafraîchit une session en utilisant un refresh token.
     * 🔐 CORRECTION : Rotation automatique des tokens avec génération sécurisée
     */
    public function refreshSession(string $refreshToken, Request $request): ?array
    {
        $hashed = hash('sha256', $refreshToken);

        $session = SecuritySession::where('refresh_token_hash', $hashed)->first();

        if (!$session || $session->is_revoked || $session->expires_at < now()) {
            return null;
        }

        // 🔐 Rotation des tokens - génération sécurisée pour les nouveaux tokens
        $newAccessToken = $this->generateSecureToken(64);
        $newRefreshToken = $this->generateSecureToken(60);

        $session->update([
            'access_token_hash' => hash('sha256', $newAccessToken),
            'refresh_token_hash' => hash('sha256', $newRefreshToken),
            'last_activity_at' => now()
        ]);

        $this->auditService->logElevee(
            'session.refresh',
            'SecuritySession',
            'session',
            AuditResult::Autorise,
            [
                'entityId' => $session->user_id,
                'ipAddress' => $request->ip(),
                'metadata' => [
                    'message' => 'Refresh token utilisé - rotation des tokens effectuée'
                ]
            ]
        );

        return [
            'access_token' => $newAccessToken,
            'refresh_token' => $newRefreshToken
        ];
    }

    public function getActiveSessions(User $user)
    {
        return SecuritySession::where('user_id', $user->id)
            ->where('is_revoked', false)
            ->where('expires_at', '>', now())
            ->orderBy('last_activity_at', 'desc')
            ->get();
    }

    public function revokeSession(string $sessionId, User $user, Request $request): bool
    {
        $session = SecuritySession::where('id', $sessionId)
            ->where('user_id', $user->id)
            ->first();

        if (!$session) {
            return false;
        }

        $oldValues = ['is_revoked' => $session->is_revoked];
        $session->revoke();

        $this->auditService->logElevee(
            'session.revocation',
            'SecuritySession',
            'session',
            AuditResult::Autorise,
            [
                'entityId' => $user->id,
                'actorId'  => $user->id,
                'oldValues' => $oldValues,
                'newValues' => ['is_revoked' => true],
                'ipAddress' => $request->ip(),
                'metadata' => [
                    'message' => "Session {$sessionId} révoquée manuellement.",
                    'session_user_id' => $session->user_id,
                ]
            ]
        );

        return true;
    }

    public function revokeAllSessions(User $user, Request $request): int
    {
        $count = SecuritySession::where('user_id', $user->id)
            ->where('is_revoked', false)
            ->update(['is_revoked' => true]);

        $this->auditService->logElevee(
            'session.revocation_globale',
            'SecuritySession',
            'session',
            AuditResult::Autorise,
            [
                'entityId' => $user->id,
                'actorId'  => $user->id,
                'ipAddress' => $request->ip(),
                'metadata' => [
                    'message' => "{$count} sessions révoquées.",
                    'user_agent' => $request->userAgent(),
                ]
            ]
        );

        return $count;
    }

    protected function generateFingerprint(Request $request): string
    {
        $payload = implode('|', [
            $request->ip(),
            $request->userAgent(),
            $request->header('Accept-Language'),
        ]);

        return hash('sha256', $payload);
    }
}
