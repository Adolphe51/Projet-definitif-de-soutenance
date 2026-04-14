<?php

namespace App\Services\Audit;

use App\DTO\AuditContext;
use App\Enums\AuditImportance;
use App\Enums\AuditResult;

class AuditServiceWrapper
{
    /**
     * Enregistre une entrée d'audit via AuditLogger.
     *
     * @param array $options ['user' => User|null, 'oldValues' => array, 'newValues' => array, 'metadata' => array, 'ipAddress' => string|null, 'entityId' => int|null]
     */
    public static function log(
        string $action,
        string $entityType,
        string $ressource,
        AuditResult $resultat,
        AuditImportance $importance,
        array $options = []
    ) {
        $user = $options['user'] ?? null;
        $oldValues = $options['oldValues'] ?? [];
        $newValues = $options['newValues'] ?? [];
        $metadata = $options['metadata'] ?? [];
        $ipAddress = $options['ipAddress'] ?? request()->ip();
        $entityId = $options['entityId'] ?? null;

        $context = new AuditContext(
            action: $action,
            entityType: $entityType,
            ressource: $ressource,
            entityId: $entityId,
            actorId: $user?->id,
            oldValues: $oldValues,
            newValues: $newValues,
            metadata: $metadata,
            ipAddress: $ipAddress,
            resultat: $resultat,
            importance: $importance
        );

        \App\Services\Audit\AuditLogger::log($context);
    }

    public static function logFaible(
        string $action,
        string $entityType,
        string $ressource,
        AuditResult $resultat,
        array $options = []
    ) {
        self::log($action, $entityType, $ressource, $resultat, AuditImportance::Faible, $options);
    }

    public static function logElevee(
        string $action,
        string $entityType,
        string $ressource,
        AuditResult $resultat,
        array $options = []
    ) {
        self::log($action, $entityType, $ressource, $resultat, AuditImportance::Elevee, $options);
    }

    public static function logCritique(
        string $action,
        string $entityType,
        string $ressource,
        AuditResult $resultat,
        array $options = []
    ) {
        self::log($action, $entityType, $ressource, $resultat, AuditImportance::Critique, $options);
    }
}
