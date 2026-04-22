<?php

namespace Tests\Feature\Auth;

use App\Enums\AppRole;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_role_and_permission_helpers_match_the_current_schema(): void
    {
        $user = User::factory()->create([
            'nom' => 'Analyste Demo',
        ]);

        $permission = Permission::create([
            'nom' => 'alerts.list',
            'description' => 'Consulter les alertes',
            'ressourceType' => 'Alertes',
        ]);

        UserRole::create([
            'user_id' => $user->id,
            'role' => AppRole::Analyst->value,
        ]);

        RolePermission::create([
            'role' => AppRole::Analyst->value,
            'permission_id' => $permission->id,
        ]);

        $user->load('roles.permissions');

        $this->assertSame('Analyste Demo', $user->name);
        $this->assertTrue($user->hasRole(AppRole::Analyst->value));
        $this->assertTrue($user->hasPermission('alerts.list'));
        $this->assertSame('alerts.list', $user->roles->first()->permissions->first()->name);
    }
}
