<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Enums\AuditResult;
use App\Services\Audit\AuditServiceWrapper as AuditAuditServiceWrapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutService
{
    public function __construct(
        private readonly SecuritySessionService $sessionService,
        private readonly AuditAuditServiceWrapper $auditService,
    ) {}

    /**
     * Déconnecte l'utilisateur en révoquant la session courante.
     */
    public function logout(User $user, ?string $sessionToken, Request $request): void
    {
        // 1. Récupérer et invalider la session active en base
        if ($sessionToken) {
            $session = $this->sessionService->validateSession($sessionToken, $request);
            if ($session) {
                $this->sessionService->revokeSession($session->id, $user, $request);
            }
        }

        // 2. Invalider complètement la session Laravel
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 3. Enregistrer l'audit
        $this->auditService->logElevee(
            'Déconnexion volontaire',
            'User',
            'LogoutService',
            AuditResult::Autorise,
            [
                'entityId' => $user->id,
                'actorId' => $user->id,
                'ipAddress' => $request->ip(),
                'metadata' => [
                    'message' => 'Déconnexion volontaire.',
                    'user_agent' => $request->userAgent(),
                ]
            ]
        );
    }

    /**
     * Déconnecte l'utilisateur de toutes ses sessions.
     */
    public function logoutAll(User $user, Request $request): int
    {
        return $this->sessionService->revokeAllSessions($user, $request);
    }
}
