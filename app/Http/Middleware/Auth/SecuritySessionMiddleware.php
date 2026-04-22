<?php

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\AuditResult;
use App\Services\Auth\SecuritySessionService;
use App\Services\Audit\AuditServiceWrapper;

class SecuritySessionMiddleware
{
    public function __construct(
        private readonly SecuritySessionService $sessionService,
        private readonly AuditServiceWrapper $auditService,
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        // 1. Récupérer et valider le token
        $token = $request->bearerToken() ?? $request->cookie('access_token');

        if (!$token) {
            // Journaliser tentative sans token
            $this->auditService->logCritique(
                'session.missing',
                'SecuritySession',
                'middleware',
                AuditResult::Refuse,
                [
                    'entityId' => null,
                    'actorId' => null,
                    'ipAddress' => $request->ip(),
                    'metadata' => [
                        'message' => 'Requête sans token de session.',
                        'user_agent' => $request->userAgent(),
                    ]
                ]
            );

            return $request->expectsJson()
                ? response()->json(['message' => 'Token de session requis'], 401)
                : redirect()->route('login')->withErrors(['session' => 'Token de session requis']);
        }

        // 2. Valider la session via SecuritySessionService
        $session = $this->sessionService->validateSession($token, $request);

        if (!$session) {
            // Journaliser session invalide
            $this->auditService->logElevee(
                'session.invalid',
                'SecuritySession',
                'middleware',
                AuditResult::Refuse,
                [
                    'entityId' => null,
                    'actorId' => null,
                    'ipAddress' => $request->ip(),
                    'metadata' => [
                        'message' => 'Token de session invalide ou expiré.',
                        'user_agent' => $request->userAgent(),
                    ]
                ]
            );

            return $request->expectsJson()
                ? response()->json(['message' => 'Session invalide ou expirée'], 401)
                : redirect()->route('login')->withErrors(['session' => 'Session invalide ou expirée']);
        }

        // 3. Vérifier l'empreinte navigateur (déjà faite dans validateSession)

        // 4. Vérifier si l'utilisateur est actif
        if (!$session->user->is_active) {
            $this->auditService->logCritique(
                'acces.compte_desactive',
                'User',
                'middleware',
                AuditResult::Refuse,
                [
                    'entityId' => $session->user->id,
                    'actorId' => $session->user->id,
                    'ipAddress' => $request->ip(),
                    'metadata' => [
                        'message' => "Accès avec compte désactivé",
                        'route' => $request->route()?->getName(),
                        'url' => $request->fullUrl(),
                        'user_agent' => $request->userAgent(),
                    ]
                ]
            );

            return response()->json([
                'message' => 'Votre compte est désactivé. Contactez l\'administrateur.',
            ], 403);
        }

        // 5. Définir l'utilisateur pour Laravel Auth
        Auth::setUser($session->user);
        $request->attributes->set('user', $session->user);
        $request->attributes->set('session', $session);

        return $next($request);
    }
}
