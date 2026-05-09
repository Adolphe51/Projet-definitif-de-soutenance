<?php

namespace Tests\Feature\CyberGuard;

use App\Models\Attack;
use App\Models\DetectionRule;
use Tests\TestCase;
use App\Services\AttackDetectionService;
use App\Services\DetectionRuleEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class DetectionRuleEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialiser les règles par défaut
        DetectionRuleEngine::initializeDefaultRules();
    }

    #[Test]
    public function it_can_generate_attack_by_rule()
    {
        $rule = DetectionRule::where('rule_id', 'brute_force_ssh')->first();
        $this->assertNotNull($rule, 'La règle brute_force_ssh devrait exister');

        $context = [
            'source_ip' => '192.168.1.100',
            'is_simulation' => true,
        ];

        $attack = AttackDetectionService::generateAttackByRule('brute_force_ssh', $context);

        $this->assertInstanceOf(Attack::class, $attack);
        $this->assertEquals('Brute Force', $attack->type);
        $this->assertEquals('192.168.1.100', $attack->source_ip);
        $this->assertEquals(22, $attack->target_port); // Le contexte ne spécifie pas le port, donc il devrait être null ou utiliser la valeur par défaut
        $this->assertEquals('TCP', $attack->protocol);
        $this->assertEquals($rule->rule_id, $attack->rule_id);
        $this->assertTrue($attack->is_simulation);
    }

    #[Test]
    public function it_generates_reproducible_attacks_with_same_context()
    {
        $context = [
            'source_ip' => '10.0.0.1',
            'is_simulation' => true,
        ];

        // Générer deux attaques avec le même contexte et la même règle
        $attack1 = AttackDetectionService::generateAttackByRule('sql_injection', $context);
        $attack2 = AttackDetectionService::generateAttackByRule('sql_injection', $context);

        // Les attaques devraient avoir les mêmes caractéristiques déterministes
        $this->assertEquals($attack1->type, $attack2->type);
        $this->assertEquals($attack1->source_ip, $attack2->source_ip);
        $this->assertEquals($attack1->target_ip, $attack2->target_ip);
        $this->assertEquals($attack1->target_port, $attack2->target_port);
        $this->assertEquals($attack1->protocol, $attack2->protocol);
        $this->assertEquals($attack1->severity, $attack2->severity);
        $this->assertEquals($attack1->rule_id, $attack2->rule_id);
    }

    #[Test]
    public function it_can_evaluate_events_and_match_rules()
    {
        $event = [
            'type' => 'Brute Force',
            'source_ip' => '192.168.1.1',
            'target_port' => 22,
            'packet_count' => 25,
        ];

        $rule = DetectionRuleEngine::evaluateEvent($event);

        $this->assertNotNull($rule);
        $this->assertEquals('brute_force_ssh', $rule->rule_id);
    }

    #[Test]
    public function it_handles_unknown_rule_gracefully()
    {
        $attack = AttackDetectionService::generateAttackByRule('unknown_rule', []);

        $this->assertNull($attack);
    }

    #[Test]
    public function it_falls_back_to_legacy_when_no_rules_match()
    {
        // Désactiver toutes les règles
        DetectionRule::query()->update(['enabled' => false]);

        $service = new AttackDetectionService();
        $attack = $service->detectAttack('Unknown Attack', [
            'ip_address' => '127.0.0.1',
            'severity' => 'high',
        ]);

        $this->assertInstanceOf(Attack::class, $attack);
        $this->assertEquals('Unknown Attack', $attack->type);
        $this->assertEquals('127.0.0.1', $attack->source_ip);
        $this->assertEquals('high', $attack->severity);
    }

    #[Test]
    public function it_generates_random_attacks_in_demo_mode()
    {
        // Générer plusieurs attaques pour vérifier la variété
        $attacks = [];
        for ($i = 0; $i < 10; $i++) {
            $attacks[] = AttackDetectionService::generateAttack(true);
        }

        // Vérifier qu'il y a de la variété dans les types d'attaques
        $types = array_unique(array_column($attacks, 'type'));
        $this->assertGreaterThan(1, count($types), 'Les attaques devraient avoir différents types');

        // Toutes devraient être des simulations
        $simulations = array_filter($attacks, fn($a) => $a->is_simulation);
        $this->assertCount(10, $simulations);
    }

    #[Test]
    public function it_creates_alerts_for_generated_attacks()
    {
        $attack = AttackDetectionService::generateAttackByRule('sql_injection', [
            'source_ip' => '10.0.0.1',
            'is_simulation' => false,
        ]);

        $this->assertNotNull($attack->alert);
        $this->assertEquals($attack->id, $attack->alert->attack_id);
        $this->assertStringContainsString('SQL Injection', $attack->alert->title);
        $this->assertEquals('attack', $attack->alert->type);
    }
}
