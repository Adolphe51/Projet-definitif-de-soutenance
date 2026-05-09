<?php

namespace Tests\Feature\Auth;

use App\Enums\AppRole;
use App\Models\Attack;
use App\Models\SecuritySession;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacAccessTest extends TestCase
{
    use RefreshDatabase;

    private function attachRole(User $user, AppRole $role): void
    {
        UserRole::create([
            'user_id' => $user->id,
            'role' => $role->value,
        ]);
    }

    private function createSecuritySession(User $user, string $token, string $ip, string $ua, string $lang): void
    {
        SecuritySession::create([
            'user_id' => $user->id,
            'access_token_hash' => hash('sha256', $token),
            'refresh_token_hash' => hash('sha256', 'refresh-' . $token),
            'ip_address' => $ip,
            'user_agent' => $ua,
            'device_fingerprint' => hash('sha256', implode('|', [$ip, $ua, $lang])),
            'expires_at' => now()->addHour(),
            'last_activity_at' => now(),
            'is_revoked' => false,
        ]);
    }

    public function test_non_admin_user_can_access_mini_site_but_not_admin_module(): void
    {
        $ip = '127.0.0.1';
        $ua = 'CyberGuard RBAC Test';
        $lang = 'fr';
        $token = 'rbac-mini-site-token';
        $csrf = 'csrf-mini-site';

        $user = User::factory()->create([
            'is_active' => true,
        ]);
        $this->createSecuritySession($user, $token, $ip, $ua, $lang);

        $attack = Attack::create([
            'type' => 'XSS',
            'source_ip' => '198.51.100.10',
            'target_ip' => '192.168.1.10',
            'target_port' => '80',
            'protocol' => 'TCP',
            'severity' => 'high',
            'status' => 'detected',
            'country' => 'France',
            'city' => 'Paris',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
            'isp' => 'Test ISP',
            'packet_count' => 123,
            'bandwidth_mbps' => 4.2,
            'description' => 'Test attack',
            'is_simulation' => false,
            'alarm_triggered' => true,
        ]);

        $this
            ->withHeader('User-Agent', $ua)
            ->withHeader('Accept-Language', $lang)
            ->withCookie('access_token', $token)
            ->get(route('intranet.index'))
            ->assertOk();

        $this
            ->withHeader('User-Agent', $ua)
            ->withHeader('Accept-Language', $lang)
            ->withCookie('access_token', $token)
            ->get(route('attacks.index'))
            ->assertStatus(403);

        $this
            ->withHeader('User-Agent', $ua)
            ->withHeader('Accept-Language', $lang)
            ->withCookie('access_token', $token)
            ->get(route('attacks.show', $attack->id))
            ->assertStatus(403);

        $this
            ->withHeader('User-Agent', $ua)
            ->withHeader('Accept-Language', $lang)
            ->withCookie('access_token', $token)
            ->withCredentials()
            ->withSession(['_token' => $csrf])
            ->postJson(route('attacks.block', $attack->id), ['_token' => $csrf])
            ->assertStatus(403);

        $this
            ->withHeader('User-Agent', $ua)
            ->withHeader('Accept-Language', $lang)
            ->withCookie('access_token', $token)
            ->withCredentials()
            ->withSession(['_token' => $csrf])
            ->deleteJson(route('attacks.destroy', $attack->id), ['_token' => $csrf])
            ->assertStatus(403);
    }

    public function test_admin_can_access_mini_site_and_admin_module(): void
    {
        $ip = '127.0.0.1';
        $ua = 'CyberGuard RBAC Admin Full Access Test';
        $lang = 'fr';
        $token = 'rbac-admin-full-token';

        $user = User::factory()->create([
            'is_active' => true,
        ]);
        $this->attachRole($user, AppRole::Admin);
        $this->createSecuritySession($user, $token, $ip, $ua, $lang);

        $this
            ->withHeader('User-Agent', $ua)
            ->withHeader('Accept-Language', $lang)
            ->withCookie('access_token', $token)
            ->get(route('dashboard'))
            ->assertOk();

        $this
            ->withHeader('User-Agent', $ua)
            ->withHeader('Accept-Language', $lang)
            ->withCookie('access_token', $token)
            ->get(route('intranet.index'))
            ->assertOk();
    }

    public function test_admin_can_block_and_delete_attacks(): void
    {
        $ip = '127.0.0.1';
        $ua = 'CyberGuard RBAC Admin Test';
        $lang = 'fr';
        $token = 'rbac-admin-token';
        $csrf = 'csrf-admin';

        $user = User::factory()->create([
            'is_active' => true,
        ]);
        $this->attachRole($user, AppRole::Admin);
        $this->createSecuritySession($user, $token, $ip, $ua, $lang);

        $attack = Attack::create([
            'type' => 'Brute Force',
            'source_ip' => '198.51.100.11',
            'target_ip' => '192.168.1.11',
            'target_port' => '22',
            'protocol' => 'TCP',
            'severity' => 'medium',
            'status' => 'detected',
            'country' => 'France',
            'city' => 'Paris',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
            'isp' => 'Test ISP',
            'packet_count' => 1,
            'bandwidth_mbps' => 0.1,
            'description' => 'Test attack',
            'is_simulation' => false,
            'alarm_triggered' => false,
        ]);

        $this
            ->withHeader('User-Agent', $ua)
            ->withHeader('Accept-Language', $lang)
            ->withCookie('access_token', $token)
            ->withCredentials()
            ->withSession(['_token' => $csrf])
            ->postJson(route('attacks.block', $attack->id), ['_token' => $csrf])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('attacks', [
            'id' => $attack->id,
            'status' => 'blocked',
        ]);

        $this
            ->withHeader('User-Agent', $ua)
            ->withHeader('Accept-Language', $lang)
            ->withCookie('access_token', $token)
            ->withCredentials()
            ->withSession(['_token' => $csrf])
            ->deleteJson(route('attacks.destroy', $attack->id), ['_token' => $csrf])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('attacks', [
            'id' => $attack->id,
        ]);
    }
}
