<?php

namespace Tests\Feature\CyberGuard;

use App\Models\Attack;
use App\Services\DetectionRuleEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WebLogIngestionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        DetectionRuleEngine::initializeDefaultRules();
    }

    #[Test]
    public function it_ingests_web_logs_and_detects_realistic_http_tests(): void
    {
        $logPath = storage_path('framework/testing-access.log');

        file_put_contents($logPath, implode(PHP_EOL, [
            '192.168.1.50 - - [19/May/2026:10:00:00 +0000] "GET /wp-login.php HTTP/1.1" 404 162 "-" "Nikto/2.5.0"',
            '192.168.1.51 - - [19/May/2026:10:01:00 +0000] "GET /intranet/messages?search=1%27%20UNION%20SELECT%20NULL HTTP/1.1" 200 512 "-" "sqlmap/1.8.2"',
            '192.168.1.52 - - [19/May/2026:10:02:00 +0000] "GET /login HTTP/1.1" 200 1024 "-" "Mozilla/5.0"',
        ]) . PHP_EOL);

        $this->artisan('cyberguard:collect-web-logs', [
            '--file' => $logPath,
            '--reset-offset' => true,
        ])->assertSuccessful();

        $this->assertDatabaseHas('attacks', [
            'source_ip' => '192.168.1.50',
            'type' => 'Port Scan',
            'rule_id' => 'http_recon_scan',
        ]);

        $this->assertDatabaseHas('attacks', [
            'source_ip' => '192.168.1.51',
            'type' => 'SQL Injection',
            'rule_id' => 'sql_injection',
        ]);

        $this->assertDatabaseMissing('attacks', [
            'source_ip' => '192.168.1.52',
        ]);

        $this->assertSame(2, Attack::count());
    }

    #[Test]
    public function it_tracks_offsets_to_avoid_duplicate_ingestion(): void
    {
        $logPath = storage_path('framework/testing-access-offset.log');

        file_put_contents($logPath, '192.168.1.60 - - [19/May/2026:11:00:00 +0000] "GET /.env HTTP/1.1" 404 162 "-" "ffuf/2.1.0"' . PHP_EOL);

        $this->artisan('cyberguard:collect-web-logs', [
            '--file' => $logPath,
            '--reset-offset' => true,
        ])->assertSuccessful();

        $this->assertSame(1, Attack::count());

        $this->artisan('cyberguard:collect-web-logs', [
            '--file' => $logPath,
        ])->assertSuccessful();

        $this->assertSame(1, Attack::count());
    }
}
