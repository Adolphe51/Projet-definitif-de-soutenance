<?php

namespace App\Services;

use App\Models\Attack;
use App\Models\Alert;
use App\Services\DetectionRuleEngine;
use App\Services\AutoBlockService;

class AttackDetectionService
{
    public static function supportedAttackTypes(): array
    {
        return Attack::attackTypes();
    }

    public static function ruleIdForAttackType(string $attackType): ?string
    {
        $normalized = strtolower(str_replace(['-', ' '], '_', trim($attackType)));

        return match ($normalized) {
            'brute_force', 'brute_force_ssh' => 'brute_force_ssh',
            'sql_injection' => 'sql_injection',
            'xss', 'xss_attempt' => 'xss_attempt',
            default => null,
        };
    }

    /**
     * Générer une attaque aléatoire (mode démo)
     * NOTE: Cette méthode est conservée pour compatibilité mais utilise maintenant les règles
     */
    public static function generateAttack(bool $isSimulation = false): Attack
    {
        // Sélectionner une règle aléatoire parmi les actives
        $rules = DetectionRuleEngine::getActiveRules();
        if (empty($rules)) {
            // Fallback si aucune règle n'est définie
            return self::generateLegacyAttack($isSimulation);
        }

        $rule = $rules[array_rand($rules)];
        $ruleId = $rule['rule_id'];

        // Générer le contexte basé sur la règle
        $context = self::generateContextForRule($ruleId, $isSimulation);

        return DetectionRuleEngine::detectByRule($ruleId, $context);
    }

    /**
     * Générer une attaque basée sur une règle spécifique
     */
    public static function generateAttackByRule(string $ruleId, array $context = []): ?Attack
    {
        // Fusionner avec le contexte spécifique à la règle
        $ruleContext = self::generateContextForRule($ruleId, $context['is_simulation'] ?? false, $context);
        $context = array_merge($ruleContext, $context);

        return DetectionRuleEngine::detectByRule($ruleId, $context);
    }

    /**
     * Détecter une attaque réelle basée sur un événement
     */
    public function detectAttack(string $type, array $context = []): Attack
    {
        $context['is_simulation'] = false;

        if ($ruleId = self::ruleIdForAttackType($type)) {
            return self::generateAttackByRule($ruleId, $context) ?? $this->detectLegacyAttack($type, $context);
        }

        // Essayer d'abord de trouver une règle qui correspond
        $event = array_merge($context, ['type' => $type]);
        $rule = DetectionRuleEngine::evaluateEvent($event);

        if ($rule) {
            // Utiliser la règle pour une détection déterministe
            $context['is_simulation'] = false;
            return DetectionRuleEngine::detectByRule($rule->rule_id, $context);
        }

        // Fallback vers la méthode legacy si aucune règle ne match
        return self::detectLegacyAttack($type, $context);
    }

    /**
     * Générer le contexte approprié pour une règle donnée
     */
    private static function generateContextForRule(string $ruleId, bool $isSimulation = false, array $providedContext = []): array
    {
        $ip = $providedContext['source_ip'] ?? GeoService::generateRandomIp();

        $baseContext = [
            'source_ip' => $ip,
            'target_ip' => '192.168.1.1',
            'is_simulation' => $isSimulation,
        ];

        // Contexte spécifique selon la règle
        switch ($ruleId) {
            case 'brute_force_ssh':
                return array_merge($baseContext, [
                    'target_port' => 22,
                    'protocol' => 'TCP',
                    'packet_count' => rand(10, 50),
                    'description' => "Tentatives répétées de connexion détectées depuis {$ip}",
                ]);

            case 'sql_injection':
                return array_merge($baseContext, [
                    'target_port' => 80,
                    'protocol' => 'HTTP',
                    'packet_count' => rand(5, 25),
                    'payload' => "' UNION SELECT * FROM users--",
                    'description' => "Tentative d'injection SQL depuis {$ip}",
                ]);

            case 'xss_attempt':
                return array_merge($baseContext, [
                    'target_port' => 80,
                    'protocol' => 'HTTP',
                    'packet_count' => rand(3, 12),
                    'payload' => '<script>alert("XSS")</script>',
                    'description' => "Tentative XSS depuis {$ip}",
                ]);

            default:
                // Contexte générique
                return array_merge($baseContext, [
                    'target_port' => rand(1, 65535),
                    'protocol' => 'TCP',
                    'packet_count' => rand(100, 10000),
                    'bandwidth_mbps' => rand(1, 100),
                ]);
        }
    }

    /**
     * Méthode legacy conservée pour compatibilité
     * À supprimer dans une future version
     */
    private static function generateLegacyAttack(bool $isSimulation = false): Attack
    {
        $ip = GeoService::generateRandomIp();
        $geo = GeoService::lookup($ip, !$isSimulation);

        $type = Attack::attackTypes()[array_rand(Attack::attackTypes())];
        $severity = self::weightedRandom(
            ['low', 'medium', 'high', 'critical'],
            [20, 35, 30, 15]
        );

        $attack = Attack::create([
            'type' => $type,
            'source_ip' => $ip,
            'target_ip' => '10.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 254),
            'target_port' => self::getPortForType($type),
            'protocol' => self::getProtocolForType($type),
            'severity' => $severity,
            'status' => 'detected',
            'country' => $geo['country'],
            'city' => $geo['city'],
            'latitude' => $geo['lat'],
            'longitude' => $geo['lon'],
            'isp' => $geo['isp'],
            'packet_count' => rand(100, 100000),
            'bandwidth_mbps' => round(rand(1, 10000) / 10, 2),
            'description' => self::generateDescription($type, $ip, $geo['city']),
            'is_simulation' => $isSimulation,
            'alarm_triggered' => in_array($severity, ['high', 'critical']),
        ]);

        self::createAlert($attack);
        app(AutoBlockService::class)->evaluateAttack($attack, 'legacy-demo');

        return $attack;
    }

    /**
     * Méthode legacy pour detectAttack
     */
    private function detectLegacyAttack(string $type, array $context = []): Attack
    {
        $sourceIp = $context['ip_address'] ?? '127.0.0.1';
        $geo = GeoService::lookup($sourceIp, true);

        $attack = Attack::create([
            'type' => $type,
            'source_ip' => $sourceIp,
            'target_ip' => $context['target_ip'] ?? '192.168.1.1',
            'target_port' => $context['target_port'] ?? null,
            'protocol' => $context['protocol'] ?? 'TCP',
            'severity' => $context['severity'] ?? 'medium',
            'status' => $context['status'] ?? 'detected',
            'country' => $geo['country'] ?? null,
            'city' => $geo['city'] ?? null,
            'latitude' => $geo['lat'] ?? null,
            'longitude' => $geo['lon'] ?? null,
            'isp' => $geo['isp'] ?? null,
            'packet_count' => $context['packet_count'] ?? 0,
            'bandwidth_mbps' => $context['bandwidth_mbps'] ?? 0,
            'description' => $context['description'] ?? "Détection automatique de {$type} depuis {$sourceIp}",
            'is_simulation' => false,
            'alarm_triggered' => in_array($context['severity'] ?? 'medium', ['high', 'critical']),
        ]);

        self::createAlert($attack);
        app(AutoBlockService::class)->evaluateAttack($attack, 'legacy-detect');

        return $attack;
    }

    private static function weightedRandom(array $items, array $weights): string
    {
        $total = array_sum($weights);
        $rand = rand(1, $total);
        $cumulative = 0;
        foreach ($items as $i => $item) {
            $cumulative += $weights[$i];
            if ($rand <= $cumulative) {
                return $item;
            }
        }
        return $items[0];
    }

    private static function getPortForType(string $type): string
    {
        return match ($type) {
            'SQL Injection' => '3306',
            'XSS' => '80',
            'Brute Force' => '22,3389',
            default => (string) rand(1, 65535),
        };
    }

    private static function getProtocolForType(string $type): string
    {
        return 'TCP';
    }

    private static function generateDescription(string $type, string $ip, string $city): string
    {
        return match ($type) {
            'SQL Injection' => "Injection SQL détectée depuis {$ip}.",
            'XSS' => "Tentative de XSS depuis {$ip}.",
            'Brute Force' => "Attaque force brute depuis {$ip}.",
            default => "Activité suspecte détectée depuis {$ip} ({$city})."
        };
    }

    /**
     * Créer une alerte associée à une attaque
     */
    private static function createAlert(Attack $attack): void
    {
        Alert::create([
            'attack_id' => $attack->id,
            'title' => "⚠️ {$attack->severity_icon} {$attack->type}",
            'message' => "Source: {$attack->source_ip} ({$attack->city}, {$attack->country})",
            'severity' => $attack->severity,
            'type' => $attack->is_simulation ? 'simulation' : 'attack',
        ]);
    }
}
