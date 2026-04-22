<?php

namespace App\Services;

use App\Models\Attack;
use App\Models\Alert;

class AttackDetectionService
{
    private const PORT_SCAN = 'Port Scan';

    public static function generateAttack(bool $isSimulation = false): Attack
    {
        $ip = GeoService::generateRandomIp();
        $geo = GeoService::lookup($ip);

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

        return $attack->load('alerts');
    }

    public static function createAlert(Attack $attack): Alert
    {
        return Alert::create([
            'attack_id' => $attack->id,
            'title' => "⚠️ {$attack->severity_icon} Attaque {$attack->type} détectée",
            'message' => "Source: {$attack->source_ip} ({$attack->city}, {$attack->country}) → Cible: {$attack->target_ip}:{$attack->target_port}",
            'severity' => $attack->severity,
            'type' => $attack->is_simulation ? 'simulation' : 'attack',
        ]);
    }

    public function detectAttack(string $type, array $context = []): Attack
    {
        $sourceIp = $context['ip_address'] ?? '127.0.0.1';
        $geo = GeoService::lookup($sourceIp);

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
            'DDoS' => '80,443',
            'SQL Injection' => '3306',
            'XSS' => '80',
            'Brute Force' => '22,3389',
            self::PORT_SCAN => '1-65535',
            'Ransomware' => '445',
            'Phishing' => '25,465',
            'MITM' => '443',
            'DNS Spoofing' => '53',
            default => (string) rand(1, 65535),
        };
    }

    private static function getProtocolForType(string $type): string
    {
        return match ($type) {
            'DDoS', 'DNS Spoofing' => 'UDP',
            self::PORT_SCAN => 'TCP',
            default => 'TCP',
        };
    }

    private static function generateDescription(string $type, string $ip, string $city): string
    {
        return match ($type) {
            'DDoS' => "Flood massif depuis {$ip} ({$city}). Saturation détectée.",
            'SQL Injection' => "Injection SQL détectée depuis {$ip}.",
            'XSS' => "Tentative de XSS depuis {$ip}.",
            'Brute Force' => "Attaque force brute depuis {$ip}.",
            self::PORT_SCAN => "Scan de ports depuis {$ip} ({$city}).",
            'Ransomware' => "Ransomware détecté depuis {$ip}.",
            'Phishing' => "Phishing depuis {$ip} ({$city}).",
            'MITM' => "MITM depuis {$ip}.",
            default => "Activité suspecte détectée depuis {$ip} ({$city})."
        };
    }
}
