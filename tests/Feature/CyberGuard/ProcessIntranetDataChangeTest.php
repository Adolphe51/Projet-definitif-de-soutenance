<?php

namespace Tests\Feature\CyberGuard;

use App\Events\IntranetDataChanged;
use App\Listeners\ProcessIntranetDataChange;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProcessIntranetDataChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_intranet_changes_with_uuid_entity_ids_without_failing(): void
    {
        $user = User::factory()->create();
        $entityId = (string) Str::uuid();

        $event = new IntranetDataChanged(
            entityType: 'student',
            action: 'update',
            data: [
                'id' => $entityId,
                'first_name' => 'Awa',
                'last_name' => 'Konan',
            ],
            ipAddress: '127.0.0.1',
            userAgent: 'PHPUnit',
            actorId: $user->id,
        );

        app(ProcessIntranetDataChange::class)->handle($event);

        $auditLog = AuditLog::query()
            ->where('action', 'intranet_student_update')
            ->latest('created_at')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertSame($user->id, $auditLog->actor_id);
        $this->assertNull($auditLog->entity_id);
        $this->assertSame($entityId, $auditLog->metadata['entity_identifier'] ?? null);
        $this->assertSame($user->id, $auditLog->metadata['event_actor_id'] ?? null);
    }
}
