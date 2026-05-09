<?php

namespace App\Services;

use App\Models\DetectionRule;
use App\Models\Attack;
use App\Models\Alert;
use App\Services\AutoBlockService;
use Illuminate\Support\Str;

/**
 * Moteur de détection basé sur les règles
 *
 * Responsabilités:
 * - Définir les règles de détection reproductibles
 * - Évaluer un événement contre les règles actives
 * - Générer une attaque avec rule_id et incident_id
 */
class DetectionRuleEngine
{
    /**
     * Registre des règles
     */
    protected static array $rulesCache = [];

    /**
     * S'assurer que les règles minimales existent encore en base.
     */
    protected static function ensureDefaultRulesAvailable(?string $requiredRuleId = null): void
    {
        $query = DetectionRule::query()->whereIn(
            'rule_id',
            config('cyberguard.detection.focused_rule_ids', [])
        );

        $missingRequiredRule = $requiredRuleId !== null
            && !(clone $query)->where('rule_id', $requiredRuleId)->exists();

        if ($missingRequiredRule || !$query->exists()) {
            self::initializeDefaultRules();
        }
    }

    /**
     * Initialiser les règles par défaut en base de données
     */
    public static function initializeDefaultRules(): void
    {
        $defaults = [
            [
                'rule_id' => 'brute_force_ssh',
                'name' => 'SSH Brute Force',
                'description' => 'Détecte des tentatives répétées de connexion SSH échouées',
                'attack_type' => 'Brute Force',
                'default_severity' => 'high',
                'detection_params' => ['threshold' => 5, 'window_minutes' => 10, 'port' => 22],
                'enabled' => true,
                'priority' => 1,
                'category' => 'auth',
            ],
            [
                'rule_id' => 'sql_injection',
                'name' => 'SQL Injection Attempt',
                'description' => 'Détecte une tentative d\'injection SQL',
                'attack_type' => 'SQL Injection',
                'default_severity' => 'high',
                'detection_params' => ['keywords' => ['union', 'select', 'drop', 'insert', 'delete']],
                'enabled' => true,
                'priority' => 2,
                'category' => 'application',
            ],
            [
                'rule_id' => 'xss_attempt',
                'name' => 'XSS Attempt',
                'description' => 'Détecte une tentative de cross-site scripting',
                'attack_type' => 'XSS',
                'default_severity' => 'medium',
                'detection_params' => ['keywords' => ['<script', 'javascript:', 'onerror', 'onclick']],
                'enabled' => true,
                'priority' => 3,
                'category' => 'application',
            ],
        ];

        $supportedRuleIds = config('cyberguard.detection.focused_rule_ids', [
            'brute_force_ssh',
            'sql_injection',
            'xss_attempt',
        ]);

        $defaults = array_values(array_filter(
            $defaults,
            fn(array $rule) => in_array($rule['rule_id'], $supportedRuleIds, true)
        ));

        foreach ($defaults as $rule) {
            DetectionRule::updateOrCreate(
                ['rule_id' => $rule['rule_id']],
                $rule
            );
        }

        // Vider le cache
        static::$rulesCache = [];
    }

    /**
     * Obtenir toutes les règles actives
     */
    public static function getActiveRules(): array
    {
        self::ensureDefaultRulesAvailable();

        if (empty(static::$rulesCache)) {
            static::$rulesCache = DetectionRule::query()
                ->where('enabled', true)
                ->whereIn('rule_id', config('cyberguard.detection.focused_rule_ids', []))
                ->orderBy('priority')
                ->get()
                ->toArray();
        }
        return static::$rulesCache;
    }

    /**
     * Obtenir une règle par son ID
     */
    public static function getRule(string $ruleId): ?DetectionRule
    {
        self::ensureDefaultRulesAvailable($ruleId);

        return DetectionRule::byRuleId($ruleId);
    }

    /**
     * Générer une attaque basée sur une règle
     *
     * Assure que: même contexte -> même type/sévérité (reproductibilité)
     */
    public static function detectByRule(
        string $ruleId,
        array $context = []
    ): ?Attack {
        $rule = self::getRule($ruleId);

        if (!$rule || !$rule->enabled) {
            return null;
        }

        $sourceIp = $context['source_ip'] ?? GeoService::generateRandomIp();
        $geo = GeoService::lookup($sourceIp, !($context['is_simulation'] ?? false));

        // Générer un incident_id basé sur source_ip + rule_id pour grouper les attaques corrélées
        $incidentId = self::generateIncidentId($sourceIp, $ruleId);

        $attack = Attack::create([
            'type' => $rule->attack_type,
            'rule_id' => $rule->rule_id,
            'incident_id' => $incidentId,
            'source_ip' => $sourceIp,
            'target_ip' => $context['target_ip'] ?? '192.168.1.1',
            'target_port' => $context['target_port'] ?? null,
            'protocol' => $context['protocol'] ?? 'TCP',
            'severity' => $context['severity'] ?? $rule->default_severity,
            'status' => $context['status'] ?? 'detected',
            'country' => $geo['country'] ?? 'Unknown',
            'city' => $geo['city'] ?? null,
            'latitude' => $geo['lat'] ?? null,
            'longitude' => $geo['lon'] ?? null,
            'isp' => $geo['isp'] ?? null,
            'packet_count' => $context['packet_count'] ?? 0,
            'bandwidth_mbps' => $context['bandwidth_mbps'] ?? 0,
            'payload' => $context['payload'] ?? null,
            'description' => $context['description'] ?? "Détection: {$rule->name} depuis {$sourceIp}",
            'is_simulation' => $context['is_simulation'] ?? false,
            'alarm_triggered' => in_array($context['severity'] ?? $rule->default_severity, ['high', 'critical']),
        ]);

        // Créer l'alerte associée
        Alert::create([
            'attack_id' => $attack->id,
            'title' => "⚠️ {$attack->severity_icon} {$rule->name}",
            'message' => "Source: {$sourceIp} ({$geo['city']}, {$geo['country']})",
            'severity' => $attack->severity,
            'type' => $attack->is_simulation ? 'simulation' : 'attack',
        ]);

        app(AutoBlockService::class)->evaluateAttack($attack, 'detection');

        return $attack;
    }

    /**
     * Générer un ID d'incident déterministe
     * Assure que le même source_ip + rule_id génère le même incident_id
     */
    public static function generateIncidentId(string $sourceIp, string $ruleId): string
    {
        // Décider si on crée un nouvel incident basé sur la date
        // Pour reproductibilité: same day = same incident
        $dateKey = now()->format('Y-m-d');
        return 'INC-' . Str::slug("{$ruleId}-{$sourceIp}-{$dateKey}", '-');
    }

    /**
     * Évaluer un événement brut contre les règles
     * Retourne la première règle qui match
     */
    public static function evaluateEvent(array $event): ?DetectionRule
    {
        $rules = self::getActiveRules();

        foreach ($rules as $ruleArray) {
            $rule = new DetectionRule($ruleArray);

            // Vérifier si la règle match cet événement
            if (self::ruleMatches($rule, $event)) {
                return $rule;
            }
        }

        return null;
    }

    /**
     * Vérifier si une règle correspond à un événement
     */
    protected static function ruleMatches(DetectionRule $rule, array $event): bool
    {
        // Implémentation simplifiée
        // En production: comparaison plus élaborée avec detection_params

        // Si l'événement mentionne le type d'attaque
        if (isset($event['type']) && $event['type'] === $rule->attack_type) {
            return true;
        }

        // Si l'événement contient des indices du type
        if (isset($event['payload'])) {
            $payload = strtolower($event['payload']);
            $params = $rule->detection_params ?? [];

            if (isset($params['keywords'])) {
                foreach ($params['keywords'] as $keyword) {
                    if (strpos($payload, strtolower($keyword)) !== false) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Effacer le cache des règles (après insertion/mise à jour)
     */
    public static function clearRulesCache(): void
    {
        static::$rulesCache = [];
    }
}
