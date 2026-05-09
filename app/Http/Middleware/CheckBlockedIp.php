<?php

namespace App\Http\Middleware;

use App\Models\BlockedIp;
use App\Services\AutoBlockService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockedIp
{
    public function __construct(
        private readonly AutoBlockService $autoBlockService,
    ) {
    }

    /**
     * Bloque les IPs listées dans la table blocked_ips.
     * Autorise uniquement les admins authentifiés ou les IPs de confiance
     * à accéder à l'interface CyberGuard lorsqu'une IP est bloquée.
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

        if (BlockedIp::isBlocked($ip)) {
            if ($this->autoBlockService->canBypassBlockedIpForAdmin($request)) {
                return $next($request);
            }

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
