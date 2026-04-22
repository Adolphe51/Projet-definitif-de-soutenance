<?php

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\AuditResult;
use App\Services\Audit\AuditServiceWrapper as AuditAuditServiceWrapper;

class CheckIpAuthorized
{
    public function __construct(
        private readonly AuditAuditServiceWrapper $auditService,
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        $user = $request->attributes->get('user') ?? Auth::user();
        $authorizedIps = $this->resolveAuthorizedIps($user);

        if ($authorizedIps === []) {
            return $next($request);
        }

        $clientIp = $request->ip();

        if (!in_array($clientIp, $authorizedIps, true)) {
            $this->auditService->logCritique(
                'acces.ip_non_autorisee',
                'Middleware',
                'middleware',
                AuditResult::Refuse,
                [
                    'entityId' => $user->id,
                    'actorId' => $user->id,
                    'ipAddress' => $clientIp,
                    'metadata' => [
                        'message' => "IP {$clientIp} non autorisée (autorisées : " . implode(', ', $authorizedIps) . ')',
                        'user_agent' => $request->userAgent(),
                    ]
                ]
            );

            return $request->expectsJson()
                ? response()->json(['message' => 'Accès refusé depuis cette adresse IP.'], 403)
                : abort(403, 'Accès refusé depuis cette adresse IP.');
        }

        return $next($request);
    }

    private function resolveAuthorizedIps($user): array
    {
        if (!$user) {
            return [];
        }

        $rawIps = $user->getAttribute('ip_autorisee')
            ?? $user->getAttribute('authorized_ips');

        if (!is_string($rawIps) || trim($rawIps) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $rawIps))));
    }
}
