<?php

namespace App\Http\Middleware;

use App\Models\BlockedIp;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
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
        $temporaryBlockUntil = Cache::get("blocked_ip:{$ip}:rate_limit");

        if (is_numeric($temporaryBlockUntil) && (int) $temporaryBlockUntil > now()->timestamp) {
            $remainingSeconds = max(1, (int) $temporaryBlockUntil - now()->timestamp);

            return $this->buildBlockedResponse(
                $request,
                'Too many authentication attempts. Your IP is temporarily blocked.',
                $ip,
                $remainingSeconds,
                429
            );
        }

        // Ne pas bloquer les accès à l'interface admin CyberGuard
        $adminPaths = ['dashboard', 'attacks', 'simulations', 'alerts', 'honeypot', 'geo', 'api'];
        foreach ($adminPaths as $p) {
            if (str_starts_with($request->path(), $p)) {
                return $next($request);
            }
        }

        if (BlockedIp::isBlocked($ip)) {
            return $this->buildBlockedResponse(
                $request,
                'Your IP address has been blocked.',
                $ip,
                null,
                403
            );
        }

        return $next($request);
    }

    private function buildBlockedResponse(
        Request $request,
        string $message,
        string $ip,
        ?int $retryAfter,
        int $status
    ): Response {
        if ($request->expectsJson()) {
            $payload = [
                'error' => $message,
                'ip' => $ip,
            ];

            if ($retryAfter !== null) {
                $payload['retry_after'] = $retryAfter;
            } else {
                $payload['contact'] = 'security@cyberguard.local';
            }

            return response()->json($payload, $status);
        }

        return redirect()
            ->to(Route::has('login') ? route('login') : url('/'))
            ->with('error', $message);
    }
}
