<?php

namespace App\Http\Middleware;

use App\Models\BlockedIp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockedIp
{
    /**
     * Bloque les IPs listées dans la table blocked_ips.
     * Exclut les routes d'administration /dashboard, /attacks, etc.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip() ?? '127.0.0.1';

        // Ne pas bloquer les accès à l'interface admin CyberGuard
        $adminPaths = ['dashboard', 'attacks', 'simulations', 'alerts', 'honeypot', 'geo', 'api'];
        foreach ($adminPaths as $p) {
            if (str_starts_with($request->path(), $p)) {
                return $next($request);
            }
        }

        if (BlockedIp::isBlocked($ip)) {
            return response()->json([
                'error'   => 'Your IP address has been blocked.',
                'ip'      => $ip,
                'contact' => 'security@cyberguard.local',
            ], 403);
        }

        return $next($request);
    }
}
