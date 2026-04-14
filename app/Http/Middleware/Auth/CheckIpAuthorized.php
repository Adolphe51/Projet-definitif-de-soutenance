<?php

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use App\Enums\AuditResult;
use App\Services\Audit\AuditServiceWrapper as AuditAuditServiceWrapper;

class CheckIpAuthorized
{
    public function __construct(
        private readonly AuditAuditServiceWrapper $auditService,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $user = $request->attributes->get('user');
        if (!$user?->profile || empty($user->profile->ip_autorisee)) {
            return $next($request);
        }

        $allowedIps = array_map('trim', explode(',', $user->profile->ip_autorisee));
        $clientIp = $request->ip();

        if (!in_array($clientIp, $allowedIps)) {
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
                        'message' => "IP {$clientIp} non autorisée (autorisées : {$user->profile->ip_autorisee})",
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
}
