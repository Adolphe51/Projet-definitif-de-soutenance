<?php

namespace App\Listeners;

use App\Events\IntranetDataChanged;
use App\Enums\AuditImportance;
use App\Enums\AuditResult;
use App\Services\AttackDetectionService;
use App\Services\Audit\AuditServiceWrapper;
use Illuminate\Support\Facades\Log;

class ProcessIntranetDataChange
{
    protected AttackDetectionService $attackDetectionService;

    /**
     * Create the event listener.
     */
    public function __construct(AttackDetectionService $attackDetectionService)
    {
        $this->attackDetectionService = $attackDetectionService;
    }

    /**
     * Handle the event.
     */
    public function handle(IntranetDataChanged $event): void
    {
        // Log l'événement pour audit
        AuditServiceWrapper::log(
            'intranet_' . $event->entityType . '_' . $event->action,
            'intranet_' . $event->entityType,
            'intranet',
            AuditResult::Autorise,
            AuditImportance::Moyenne,
            [
                'actorId' => $event->actorId,
                'entityId' => $event->data['id'] ?? null,
                'oldValues' => null,
                'newValues' => $event->data,
                'ipAddress' => $event->ipAddress,
                'metadata' => [
                    'user_agent' => $event->userAgent,
                    'event_actor_id' => $event->actorId,
                ],
            ]
        );

        // Analyser pour détecter des patterns d'attaque potentiels
        $this->analyzeForAttacks($event);

        Log::info('Intranet data change processed', [
            'entity_type' => $event->entityType,
            'action' => $event->action,
            'ip' => $event->ipAddress,
        ]);
    }

    /**
     * Analyser les changements pour détecter des attaques potentielles.
     */
    protected function analyzeForAttacks(IntranetDataChanged $event): void
    {
        $data = $event->data;
        $ip = $event->ipAddress;
        $payload = $this->flattenPayload($data);

        // Détection d'injection SQL potentielle dans les données
        if ($this->containsSqlInjectionPatterns($data)) {
            $this->attackDetectionService->detectAttack('SQL Injection', [
                'source' => 'intranet_' . $event->entityType,
                'source_scope' => 'internal',
                'source_channel' => 'intranet',
                'source_label' => 'Application metier',
                'is_geolocatable' => false,
                'payload' => $payload,
                'ip_address' => $ip,
                'prefer_real_geo' => false,
                'description' => "Pattern SQL suspect sur {$event->entityType} ({$event->action})",
            ]);
        }

        if ($this->containsXssPatterns($data)) {
            $this->attackDetectionService->detectAttack('XSS', [
                'source' => 'intranet_' . $event->entityType,
                'source_scope' => 'internal',
                'source_channel' => 'intranet',
                'source_label' => 'Application metier',
                'is_geolocatable' => false,
                'payload' => $payload,
                'ip_address' => $ip,
                'prefer_real_geo' => false,
                'description' => "Pattern XSS suspect sur {$event->entityType} ({$event->action})",
            ]);
        }
    }

    /**
     * Vérifier si les données contiennent des patterns d'injection SQL.
     */
    protected function containsSqlInjectionPatterns(array $data): bool
    {
        $sqlPatterns = [
            '/\bUNION\b/i',
            '/\bSELECT\b.*\bFROM\b/i',
            '/\bDROP\b.*\bTABLE\b/i',
            '/\bDELETE\b.*\bFROM\b/i',
            '/\bINSERT\b.*\bINTO\b/i',
            '/\bUPDATE\b.*\bSET\b/i',
            '/--/',
            '/\/\*.*\*\//',
            '/\bOR\b.*=.*\bOR\b/i',
            '/\bAND\b.*=.*\bAND\b/i',
        ];

        foreach ($data as $value) {
            if (is_string($value)) {
                foreach ($sqlPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    protected function containsXssPatterns(array $data): bool
    {
        $xssPatterns = [
            '/<script\b/i',
            '/javascript:/i',
            '/onerror\s*=/i',
            '/onclick\s*=/i',
            '/<img\b[^>]*onerror/i',
        ];

        foreach ($data as $value) {
            if (!is_string($value)) {
                continue;
            }

            foreach ($xssPatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function flattenPayload(array $data): string
    {
        $parts = [];

        foreach ($data as $value) {
            if (is_scalar($value)) {
                $parts[] = (string) $value;
            }
        }

        return implode(' | ', $parts);
    }
}
