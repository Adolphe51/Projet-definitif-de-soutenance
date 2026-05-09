<?php

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireRole
{
    /**
     * Usage: ->middleware('role:admin')
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $allowedRoles = [];
        foreach ($roles as $r) {
            foreach (explode(',', (string) $r) as $piece) {
                $piece = trim($piece);
                if ($piece !== '') {
                    $allowedRoles[] = $piece;
                }
            }
        }

        // If no role was configured, do not block the request.
        if ($allowedRoles === []) {
            return $next($request);
        }

        $user = $request->attributes->get('user') ?? Auth::user();

        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Authentification requise.'], 401)
                : redirect()->route('login')->withErrors(['session' => 'Authentification requise.']);
        }

        foreach ($allowedRoles as $role) {
            if (method_exists($user, 'hasRole') && $user->hasRole($role)) {
                return $next($request);
            }
        }

        return $request->expectsJson()
            ? response()->json(['message' => 'Accès refusé.'], 403)
            : abort(403, 'Accès refusé.');
    }
}
