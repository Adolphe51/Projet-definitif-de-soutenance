<?php

namespace Tests\Feature\CyberGuard;

use App\Models\Attack;
use App\Services\DetectionRuleEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NetworkReconnaissanceDetectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        DetectionRuleEngine::initializeDefaultRules();
    }

    #[Test]
    public function it_detects_http_reconnaissance_from_scanner_user_agent(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.56.23'])
            ->withHeaders([
                'User-Agent' => 'Nmap Scripting Engine',
                'Accept' => '*/*',
            ])
            ->get('/wp-login.php')
            ->assertNotFound();

        $attack = Attack::query()->latest('id')->first();

        $this->assertNotNull($attack);
        $this->assertSame('Port Scan', $attack->type);
        $this->assertSame('http_recon_scan', $attack->rule_id);
        $this->assertSame('192.168.56.23', $attack->source_ip);
        $this->assertSame('HTTP', $attack->protocol);
        $this->assertContains($attack->severity, ['medium', 'high']);
        $this->assertStringContainsString('Reconnaissance HTTP suspecte', (string) $attack->description);
    }

    #[Test]
    public function it_does_not_flag_a_regular_browser_request(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.56.24'])
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0',
                'Accept' => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'fr-FR,fr;q=0.9',
            ])
            ->get('/login')
            ->assertOk();

        $this->assertDatabaseCount('attacks', 0);
    }
}
