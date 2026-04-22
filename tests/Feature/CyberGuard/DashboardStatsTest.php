<?php

namespace Tests\Feature\CyberGuard;

use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_stats_returns_expected_json_structure(): void
    {
        Cache::flush();

        DB::table('attacks')->insert([
            [
                'type' => 'XSS',
                'source_ip' => '198.51.100.1',
                'target_ip' => '192.168.1.1',
                'target_port' => '80',
                'protocol' => 'TCP',
                'severity' => 'critical',
                'status' => 'detected',
                'country' => 'France',
                'city' => 'Paris',
                'latitude' => 48.8566,
                'longitude' => 2.3522,
                'isp' => 'Test ISP',
                'packet_count' => 100,
                'bandwidth_mbps' => 10.5,
                'payload' => 'payload',
                'description' => 'Test attack',
                'is_simulation' => false,
                'alarm_triggered' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        DB::table('alerts')->insert([
            'attack_id' => 1,
            'title' => 'Test alert',
            'message' => 'Alert triggered',
            'severity' => 'high',
            'type' => 'attack',
            'acknowledged' => false,
            'sound_played' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('simulations')->insert([
            'name' => 'Sim test',
            'attack_type' => 'DDoS',
            'target_ip' => '192.168.1.1',
            'duration_seconds' => 20,
            'intensity' => 'medium',
            'status' => 'pending',
            'packets_sent' => 0,
            'log' => null,
            'started_at' => now(),
            'completed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('blocked_ips')->insert([
            'ip_address' => '198.51.100.1',
            'reason' => 'Test block',
            'blocked_until' => now()->addHour(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('honeypot_traps')->insert([
            'name' => 'Trap 1',
            'type' => 'web',
            'fake_service' => 'apache',
            'port' => 8080,
            'path' => '/wp-admin',
            'status' => 'active',
            'description' => 'Trap description',
            'lure_content' => 'fake login',
            'interactions_count' => 0,
            'last_triggered_at' => now(),
            'config' => json_encode(['mode' => 'stealth']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('honeypot_interactions')->insert([
            'honeypot_trap_id' => 1,
            'source_ip' => '198.51.100.2',
            'country' => 'France',
            'city' => 'Paris',
            'latitude' => 48.85,
            'longitude' => 2.35,
            'isp' => 'Test ISP',
            'method' => 'GET',
            'path' => '/wp-admin',
            'user_agent' => 'Mozilla/5.0',
            'payload' => null,
            'credentials_attempted' => json_encode([]),
            'session_duration' => 1,
            'actions_taken' => json_encode([]),
            'risk_score' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $controller = new DashboardController();
        $response = $this->app->call([$controller, 'apiStats']);

        $response->assertStatus(200);
        $json = $response->getData(true);

        $this->assertArrayHasKey('total_attacks', $json);
        $this->assertArrayHasKey('critical', $json);
        $this->assertArrayHasKey('blocked_ips_count', $json);
        $this->assertArrayHasKey('attacks_per_hour', $json);
        $this->assertArrayHasKey('active_honeypots', $json);
        $this->assertArrayHasKey('recent_honeypots', $json);
    }
}
