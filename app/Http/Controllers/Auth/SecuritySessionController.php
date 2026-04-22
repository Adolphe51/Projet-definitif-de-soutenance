<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\SecuritySessionService as AuthSecuritySessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecuritySessionController extends Controller
{
    public function __construct(
        private readonly AuthSecuritySessionService $sessionService,
    ) {
    }

    /**
     * Liste les sessions actives de l'utilisateur courant.
     */
    public function index(Request $request)
    {
        $user = $request->attributes->get('user') ?? Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $sessions = $this->sessionService->getActiveSessions($user);

        return response()->json([
            'sessions' => $sessions->map(fn($s) => [
                'id' => $s->id,
                'ip_address' => $s->ip_address,
                'user_agent' => $s->user_agent,
                'last_activity_at' => $s->last_activity_at->toISOString(),
                'expires_at' => $s->expires_at->toISOString(),
                'is_current' => hash_equals(
                    $s->access_token_hash,
                    hash('sha256', (string) ($request->bearerToken() ?? $request->cookie('access_token', '')))
                ),
            ]),
        ]);
    }

    /**
     * Révoque une session spécifique.
     */
    public function revoke(string $id, Request $request)
    {
        $user = $request->attributes->get('user') ?? Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié'], 401);
        }

        $revoked = $this->sessionService->revokeSession($id, $user, $request);

        if (!$revoked) {
            return response()->json(['message' => 'Session introuvable'], 404);
        }

        return response()->json(['message' => 'Session révoquée']);
    }
}
