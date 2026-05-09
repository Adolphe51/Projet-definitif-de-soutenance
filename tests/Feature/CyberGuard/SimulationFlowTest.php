<?php

namespace Tests\Feature\CyberGuard;

use App\Enums\AppRole;
use App\Models\Attack;
use App\Models\DetectionRule;
use App\Models\SecuritySession;
use App\Models\Simulation;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimulationFlowTest extends TestCase
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

    public function test_simulation_flow_bootstraps_detection_rules_when_missing(): void
    {
        $ip = '127.0.0.1';
        $ua = 'CyberGuard Simulation Test';
        $lang = 'fr';
        $token = 'simulation-admin-token';
        $csrf = 'csrf-simulation';

        $user = User::factory()->create([
            'is_active' => true,
        ]);

        $this->attachRole($user, AppRole::Admin);
        $this->createSecuritySession($user, $token, $ip, $ua, $lang);

        $this->assertSame(0, DetectionRule::count());

        $launchResponse = $this
            ->withHeader('User-Agent', $ua)
            ->withHeader('Accept-Language', $lang)
            ->withHeader('X-CSRF-TOKEN', $csrf)
            ->withCookie('access_token', $token)
            ->withCredentials()
            ->withSession(['_token' => $csrf])
            ->postJson(route('simulations.launch'), [
                '_token' => $csrf,
                'attack_type' => 'SQL Injection',
                'target_ip' => '192.168.1.100',
                'duration' => 30,
                'intensity' => 'medium',
            ]);

        $launchResponse
            ->assertOk()
            ->assertJson(['success' => true]);

        $simulationId = $launchResponse->json('simulation_id');

        $this->assertNotNull($simulationId);
        $this->assertDatabaseHas('simulations', [
            'id' => $simulationId,
            'attack_type' => 'SQL Injection',
            'status' => 'running',
        ]);

        $simulateResponse = $this
            ->withHeader('User-Agent', $ua)
            ->withHeader('Accept-Language', $lang)
            ->withHeader('X-CSRF-TOKEN', $csrf)
            ->withCookie('access_token', $token)
            ->withCredentials()
            ->withSession(['_token' => $csrf])
            ->postJson(route('simulations.api.simulate', ['simulation_id' => $simulationId]), [
                '_token' => $csrf,
            ]);

        $simulateResponse
            ->assertOk()
            ->assertJsonPath('status', 'running')
            ->assertJsonPath('attack.type', 'SQL Injection');

        $this->assertGreaterThanOrEqual(3, DetectionRule::count());
        $this->assertSame(1, Attack::count());
        $this->assertGreaterThan(0, Simulation::findOrFail($simulationId)->packets_sent);
    }
}
