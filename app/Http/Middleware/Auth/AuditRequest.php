<?php

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\AuditResult;
use App\Enums\AuditImportance;
use App\Services\Audit\AuditServiceWrapper as AuditAuditServiceWrapper;

class AuditRequest
{
    public function __construct(
        private readonly AuditAuditServiceWrapper $auditService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $user = $request->attributes->get('user') ?? Auth::user();
        $statusCode = $response->getStatusCode();

        $resultat = match (true) {
            $statusCode >= 200 && $statusCode < 300 => AuditResult::Autorise,
            $statusCode >= 400 && $statusCode < 500 => AuditResult::Refuse,
            default => AuditResult::Erreur,
        };

        $importance = match ($request->method()) {
            'GET' => AuditImportance::Faible,
            'POST', 'PUT', 'PATCH' => AuditImportance::Moyenne,
            'DELETE' => AuditImportance::Elevee,
            default => AuditImportance::Faible,
        };

        $this->auditService->log(
            "{$request->method()} {$request->path()}",
            'Audit',
            $request->path(),
            $resultat,
            $importance,
            [
                'entityId' => $user?->id,
                'actorId' => $user?->id,
                'ipAddress' => $request->ip(),
                'metadata' => [
                    'message' => "HTTP {$statusCode}",
                    'user_agent' => $request->userAgent(),
                ]
            ]
        );

        return $response;
    }
}
