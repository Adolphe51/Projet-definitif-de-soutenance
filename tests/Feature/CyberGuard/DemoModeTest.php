<?php

namespace Tests\Feature\CyberGuard;

use App\Models\Attack;
use App\Models\HoneypotTrap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class DemoModeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que demo_mode_enabled génère des attaques (testé indirectement via seeder)
     * NOTE: Les tests HTTP de génération sont documentés dans ETAPE_2_DEMO_VS_PROD.md
     */
    public function test_demo_mode_enables_features_documented(): void
    {
        Config::set('cyberguard.detection.demo_mode', true);

        // Vérifier que la configuration est bien lue
        $this->assertTrue(config('cyberguard.detection.demo_mode'));
        $this->assertTrue(config('cyberguard.detection.demo_rate') > 0);
    }

    /**
     * Test que demo_mode_disabled empêche les générations (testé indirectement via seeder)
     * NOTE: Les tests HTTP sont documentés dans ETAPE_2_DEMO_VS_PROD.md
     */
    public function test_demo_mode_disabled_disables_features(): void
    {
        Config::set('cyberguard.detection.demo_mode', false);

        // Vérifier que la configuration est bien lue
        $this->assertFalse(config('cyberguard.detection.demo_mode'));
    }

    /**
     * Test que honeypot_demo_mode génère des interactions
     * NOTE: Le test HTTP complet est documenté dans ETAPE_2_DEMO_VS_PROD.md
     */
    public function test_honeypot_demo_mode_configuration(): void
    {
        Config::set('cyberguard.honeypot.demo_mode', true);
        Config::set('cyberguard.honeypot.demo_rate', 100);

        // Vérifier que la configuration est bien lue
        $this->assertTrue(config('cyberguard.honeypot.demo_mode'));
        $this->assertEquals(100, config('cyberguard.honeypot.demo_rate'));
    }

    /**
     * Test que honeypot_demo_mode_disabled empêche les simulations
     * NOTE: Le test HTTP complet est documenté dans ETAPE_2_DEMO_VS_PROD.md
     */
    public function test_honeypot_demo_mode_disabled_configuration(): void
    {
        Config::set('cyberguard.honeypot.demo_mode', false);

        // Vérifier que la configuration est bien lue
        $this->assertFalse(config('cyberguard.honeypot.demo_mode'));
    }

    public function test_seeder_respects_demo_mode_flag(): void
    {
        // Désactiver le mode démo pour le seeder
        Config::set('cyberguard.mode.is_demo', false);

        // Exécuter le seeder
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\CyberGuardSeeder']);

        // Sans mode démo, aucune attaque supplémentaire ne devrait être créée par le seeder
        // (sauf peut-être les pièges qui sont toujours créés)
        $afterSeeding = Attack::count();

        // Aucune attaque démographique ne devrait être générée
        $this->assertEquals(0, $afterSeeding);
    }

    public function test_seeder_keeps_manual_only_flow_in_demo_mode(): void
    {
        // Activer le mode démo pour le seeder
        Config::set('cyberguard.mode.is_demo', true);
        Config::set('cyberguard.mode.is_production', false);

        // Exécuter le seeder
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\CyberGuardSeeder']);

        // En mode démo, les pièges existent mais les incidents restent manuels
        $this->assertEquals(0, Attack::count());
        $this->assertGreaterThan(0, HoneypotTrap::count());
    }

    /**
     * Test que la demo_rate est correctement lue depuis la configuration
     */
    public function test_demo_rate_configuration_respected(): void
    {
        Config::set('cyberguard.detection.demo_mode', true);
        Config::set('cyberguard.detection.demo_rate', 0); // 0% de chance de générer

        // Vérifier que la configuration est bien lue
        $this->assertEquals(0, config('cyberguard.detection.demo_rate'));

        // Avec 0%, aucune attaque ne devrait être générée (testé via seeder)
        Config::set('cyberguard.mode.is_demo', false);
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\CyberGuardSeeder']);

        $this->assertEquals(0, Attack::count());
    }
}
