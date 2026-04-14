<?php

namespace App\Services\Audit;

use App\DTO\AuditContext;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class AuditLogger
{
    public static function log(AuditContext $context): void
    {
        DB::transaction(function () use ($context) {

            $lastLog = AuditLog::orderByDesc('created_at')->lockForUpdate()->first();
            $previousHash = $lastLog?->current_hash;

            $payloadData = [
                'actor_id'      => $context->actorId,
                'action'        => $context->action,
                'entity_type'   => $context->entityType,
                'entity_id'     => $context->entityId,
                'old_values'    => $context->oldValues,
                'new_values'    => $context->newValues,
                'metadata'      => $context->metadata,
                'previous_hash' => $previousHash,
                'timestamp'     => now()->toIso8601String(),
            ];

            ksort($payloadData);

            $payload = json_encode(
                $payloadData,
                JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            );

            $currentHash = hash(
                'sha256',
                ($previousHash ?? '') . $payload
            );

            AuditLog::create([
                'actor_id'      => $context->actorId,
                'action'        => $context->action,
                'entity_type'   => $context->entityType,
                'entity_id'     => $context->entityId,
                'ressource'     => $context->ressource,
                'old_values'    => $context->oldValues,
                'new_values'    => $context->newValues,
                'metadata'      => $context->metadata,
                'previous_hash' => $previousHash,
                'current_hash'  => $currentHash,
                'ip_address'    => $context->ipAddress,
                'resultat'      => $context->resultat->value,
                'importance'    => $context->importance->value
            ]);
        });
    }
}
