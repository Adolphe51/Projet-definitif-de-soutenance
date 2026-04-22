<?php

namespace App\Http\Middleware\Auth;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Support\Facades\Log;

class EnhancedCsrfProtection extends Middleware
{
    /**
     * Routes exemptées de la vérification CSRF.
     */
    protected $except = [
        //
    ];

    /**
     * Stocke les tokens CSRF générés pour chaque requête
     */
    protected array $csrfTokens = [];

    protected function getTokenFromRequest($request)
    {
        return $request->bearerToken() ?: parent::getTokenFromRequest($request);
    }

    /**
     * Vérifie que le token CSRF en entrant correspond
     */
    protected function tokensMatch($request)
    {
        $token = $this->getTokenFromRequest($request);
        $match = true;

        if (!$token) {
            Log::warning('CSRF token missing', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route' => $request->route()?->getName() ?? 'unknown',
            ]);
            $match = false;
        } elseif (!parent::tokensMatch($request)) {
            Log::warning('CSRF token mismatch', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route' => $request->route()?->getName() ?? 'unknown',
                'token_provided' => substr($token, 0, 8) . '...',
            ]);
            $match = false;
        } else {
            $sessionUserAgent = session('csrf_user_agent');
            if ($sessionUserAgent && $sessionUserAgent !== $request->userAgent()) {
                Log::warning('User agent changed', [
                    'ip' => $request->ip(),
                    'previous_user_agent' => $sessionUserAgent,
                    'current_user_agent' => $request->userAgent(),
                ]);
                $match = false;
            }
        }

        if ($match) {
            session(['csrf_user_agent' => $request->userAgent()]);
        }

        return $match;
    }
}
