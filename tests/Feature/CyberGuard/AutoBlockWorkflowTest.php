<?php

namespace Tests\Feature\CyberGuard;

use App\Enums\AppRole;
use App\Models\Alert;
use App\Models\Attack;
use App\Models\AuditLog;
use App\Models\BlockedIp;
use App\Models\SecuritySession;
use App\Models\User;
use App\Models\UserRole;
use App\Services\AutoBlockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoBlockWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function createAttack(string $ip, string $type = 'Brute Force', ?string $ruleId = 'brute_force_ssh'): Attack
    {
        return Attack::create([
            'type' => $type,
            'rule_id' => $ruleId,
            'incident_id' => 'INC-' . md5($ip . $type),
            'source_ip' => $ip,
            'target_ip' => '192.168.1.10',
            'target_port' => '22',
            'protocol' => 'TCP',
            'severity' => 'high',
            'status' => 'detected',
            'country' => 'France',
            'city' => 'Paris',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
            'isp' => 'Test ISP',
            'packet_count' => 10,
            'bandwidth_mbps' => 1.2,
            'description' => 'Auto-block test',
            'is_simulation' => false,
            'alarm_triggered' => true,
        ]);
    }

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

    public function test_ip_above_threshold_is_auto_blocked(): void
    {
        config()->set('cyberguard.detection.auto_block.enabled', true);
        config()->set('cyberguard.detection.auto_block.allowlist', []);
        config()->set('cyberguard.detection.auto_block.default_threshold_count', 2);
        config()->set('cyberguard.detection.auto_block.default_window_minutes', 15);
        config()->set('cyberguard.detection.auto_block.default_block_minutes', 30);
        config()->set('cyberguard.detection.auto_block.per_rule', []);
        config()->set('cyberguard.detection.auto_block.per_type', []);

        $service = app(AutoBlockService::class);
        $first = $this->createAttack('198.51.100.50');
        $second = $this->createAttack('198.51.100.50');

        $blocked = $service->evaluateAttack($second, 'test');

        $this->assertNotNull($blocked);
        $this->assertDatabaseHas('blocked_ips', [
            'ip_address' => '198.51.100.50',
        ]);
        $this->assertDatabaseHas('alerts', [
            'attack_id' => $second->id,
            'title' => '🤖 AUTO-BLOCAGE: 198.51.100.50',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'blocked_ip.autoblock',
            'entity_type' => 'BlockedIp',
        ]);
        $this->assertDatabaseHas('attacks', [
            'id' => $first->id,
            'status' => 'blocked',
        ]);
        $this->assertDatabaseHas('attacks', [
            'id' => $second->id,
            'status' => 'blocked',
        ]);
    }

    public function test_allowlisted_ip_is_never_auto_blocked(): void
    {
        config()->set('cyberguard.detection.auto_block.enabled', true);
        config()->set('cyberguard.detection.auto_block.allowlist', ['198.51.100.77']);
        config()->set('cyberguard.detection.auto_block.default_threshold_count', 1);
        config()->set('cyberguard.detection.auto_block.per_rule', []);
        config()->set('cyberguard.detection.auto_block.per_type', []);

        $service = app(AutoBlockService::class);
        $attack = $this->createAttack('198.51.100.77');

        $blocked = $service->evaluateAttack($attack, 'test');

        $this->assertNull($blocked);
        $this->assertDatabaseMissing('blocked_ips', [
            'ip_address' => '198.51.100.77',
        ]);
        $this->assertDatabaseMissing('alerts', [
            'title' => '🤖 AUTO-BLOCAGE: 198.51.100.77',
        ]);
    }

    public function test_admin_can_unblock_with_trace(): void
    {
        $ip = '127.0.0.1';
        $ua = 'CyberGuard Unblock Admin Test';
        $lang = 'fr';
        $token = 'unblock-admin-token';
        $csrf = 'csrf-unblock-admin';

        $user = User::factory()->create([
            'is_active' => true,
        ]);
        $this->attachRole($user, AppRole::Admin);
        $this->createSecuritySession($user, $token, $ip, $ua, $lang);

        $attack = $this->createAttack('198.51.100.88');
        $attack->update(['status' => 'blocked']);
        BlockedIp::blockIp('198.51.100.88', 'Auto-bloqué pour test', $attack->id, 30);

        $this
            ->withHeader('User-Agent', $ua)
            ->withHeader('Accept-Language', $lang)
            ->withCookie('access_token', $token)
            ->withCredentials()
            ->withSession(['_token' => $csrf])
            ->postJson(route('attacks.unblock', $attack->id), [
                '_token' => $csrf,
                'comment' => 'Levée contrôlée du blocage',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('blocked_ips', [
            'ip_address' => '198.51.100.88',
        ]);
        $this->assertDatabaseHas('attacks', [
            'id' => $attack->id,
            'status' => 'investigating',
        ]);
        $this->assertDatabaseHas('alerts', [
            'attack_id' => $attack->id,
            'title' => '🔓 IP débloquée: 198.51.100.88',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'blocked_ip.unblocked',
            'entity_type' => 'BlockedIp',
        ]);
    }

    public function test_blocked_admin_session_can_access_dashboard_but_blocked_mini_site_user_cannot(): void
    {
        $ip = '203.0.113.10';
        $ua = 'CyberGuard Blocked Session Test';
        $lang = 'fr';

        BlockedIp::blockIp($ip, 'Test blocked workstation', null, 30);

        $admin = User::factory()->create(['is_active' => true]);
        $this->attachRole($admin, AppRole::Admin);
        $this->createSecuritySession($admin, 'blocked-admin-token', $ip, $ua, $lang);

        $this
            ->withServerVariables(['REMOTE_ADDR' => $ip])
            ->withHeader('User-Agent', $ua)
            ->withHeader('Accept-Language', $lang)
            ->withCookie('access_token', 'blocked-admin-token')
            ->get(route('dashboard'))
            ->assertOk();

        $miniSiteUser = User::factory()->create(['is_active' => true]);
        $this->createSecuritySession($miniSiteUser, 'blocked-mini-site-token', $ip, $ua, $lang);

        $this
            ->withServerVariables(['REMOTE_ADDR' => $ip])
            ->withHeader('User-Agent', $ua)
            ->withHeader('Accept-Language', $lang)
            ->withCookie('access_token', 'blocked-mini-site-token')
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }
}
