<?php

namespace App\DTO;

use App\Enums\AuditImportance;
use App\Enums\AuditResult;

class AuditContext
{
    public function __construct(
        public string $action,
        public string $ressource,
        public ?int $entityId = null,
        public ?string $entityType = null,
        public ?int $actorId = null,
        public array $oldValues = [],
        public array $newValues = [],
        public ?string $ipAddress = null,
        public array $metadata = [],
        public AuditResult $resultat = AuditResult::Erreur,
        public AuditImportance $importance = AuditImportance::Moyenne
    ) {}
}
