<?php

namespace App\Listeners;

use App\Events\IntranetDataChanged;
use App\Services\AttackDetectionService;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ProcessIntranetDataChange implements ShouldQueue
{
    use InteractsWithQueue;

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
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'intranet_' . $event->entityType . '_' . $event->action,
            'entity_type' => 'intranet_' . $event->entityType,
            'entity_id' => $event->data['id'] ?? null,
            'old_values' => null,
            'new_values' => $event->data,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'importance' => 'medium',
            'result' => 'success',
        ]);

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

        // Détection d'injection SQL potentielle dans les données
        if ($this->containsSqlInjectionPatterns($data)) {
            $this->attackDetectionService->detectAttack('sql_injection', [
                'source' => 'intranet_' . $event->entityType,
                'data' => $data,
                'ip_address' => $ip,
            ]);
        }

        // Détection de tentatives de modification massive
        if ($event->action === 'update' && $this->isBulkUpdate($data)) {
            $this->attackDetectionService->detectAttack('bulk_data_manipulation', [
                'source' => 'intranet_' . $event->entityType,
                'data' => $data,
                'ip_address' => $ip,
            ]);
        }

        // Détection d'accès non autorisé aux ressources sensibles
        if ($event->entityType === 'resource' && $event->action === 'download') {
            $this->attackDetectionService->detectAttack('unauthorized_access', [
                'source' => 'intranet_resource',
                'resource_id' => $data['id'] ?? null,
                'ip_address' => $ip,
            ]);
        }

        // Détection de tentatives d'énumération d'utilisateurs
        if ($event->entityType === 'student' && $event->action === 'search') {
            $this->attackDetectionService->detectAttack('user_enumeration', [
                'source' => 'intranet_student_search',
                'search_terms' => $data,
                'ip_address' => $ip,
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

    /**
     * Vérifier si c'est une mise à jour massive.
     */
    protected function isBulkUpdate(array $data): bool
    {
        // Considérer comme bulk si plus de 10 champs modifiés ou si contient des patterns suspects
        return count($data) > 10 || isset($data['bulk_update']) || isset($data['mass_update']);
    }
}
