<?php

namespace App\Services;

use App\Models\Attack;
use App\Models\Alert;

class AttackDetectionService
{
    public static function generateAttack(bool $isSimulation = false): Attack
    {
        $ip  = GeoService::generateRandomIp();
        $geo = GeoService::lookup($ip);

        $types     = Attack::attackTypes();
        $type      = $types[array_rand($types)];
        $severities = ['low', 'medium', 'high', 'critical'];
        $weights    = [20, 35, 30, 15]; // probabilités
        $severity   = self::weightedRandom($severities, $weights);

        $attack = Attack::create([
            'type'          => $type,
            'source_ip'     => $ip,
            'target_ip'     => '10.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 254),
            'target_port'   => self::getPortForType($type),
            'protocol'      => self::getProtocolForType($type),
            'severity'      => $severity,
            'status'        => 'detected',
            'country'       => $geo['country'],
            'city'          => $geo['city'],
            'latitude'      => $geo['lat'],
            'longitude'     => $geo['lon'],
            'isp'           => $geo['isp'],
            'packet_count'  => rand(100, 100000),
            'bandwidth_mbps' => round(rand(1, 10000) / 10, 2),
            'description'   => self::generateDescription($type, $ip, $geo['city']),
            'is_simulation' => $isSimulation,
            'alarm_triggered' => in_array($severity, ['high', 'critical']),
        ]);

        // Créer une alerte automatique
        self::createAlert($attack);

        return $attack;
    }

    public static function createAlert(Attack $attack): Alert
    {
        return Alert::create([
            'attack_id' => $attack->id,
            'title'     => "⚠️ {$attack->severity_icon} Attaque {$attack->type} détectée",
            'message'   => "Source: {$attack->source_ip} ({$attack->city}, {$attack->country}) → Cible: {$attack->target_ip}:{$attack->target_port}",
            'severity'  => $attack->severity,
            'type'      => $attack->is_simulation ? 'simulation' : 'attack',
        ]);
    }

    private static function weightedRandom(array $items, array $weights): string
    {
        $total  = array_sum($weights);
        $rand   = rand(1, $total);
        $cumulative = 0;
        foreach ($items as $i => $item) {
            $cumulative += $weights[$i];
            if ($rand <= $cumulative) return $item;
        }
        return $items[0];
    }

    private static function getPortForType(string $type): string
    {
        return match($type) {
            'DDoS'          => '80,443',
            'SQL Injection' => '3306',
            'XSS'           => '80',
            'Brute Force'   => '22,3389',
            'Port Scan'     => '1-65535',
            'Ransomware'    => '445',
            'Phishing'      => '25,465',
            'MITM'          => '443',
            'DNS Spoofing'  => '53',
            default         => (string)rand(1, 65535),
        };
    }

    private static function getProtocolForType(string $type): string
    {
        return match($type) {
            'DDoS'       => 'UDP',
            'DNS Spoofing' => 'UDP',
            'Port Scan'  => 'TCP',
            default      => 'TCP',
        };
    }

    private static function generateDescription(string $type, string $ip, string $city): string
    {
        $descriptions = [
            'DDoS'          => "Flood massif depuis {$ip} ({$city}). Saturation de la bande passante détectée.",
            'SQL Injection' => "Tentative d'injection SQL depuis {$ip}. Payload malveillant détecté dans les paramètres.",
            'XSS'           => "Script cross-site injecté depuis {$ip}. Tentative de vol de cookies.",
            'Brute Force'   => "Attaque par force brute depuis {$ip}. {$city}: +500 tentatives en 60s.",
            'Port Scan'     => "Scan de ports agressif depuis {$ip} ({$city}). Reconnaissance du réseau.",
            'Ransomware'    => "Comportement ransomware détecté depuis {$ip}. Chiffrement de fichiers tenté.",
            'Phishing'      => "Email de phishing tracé depuis {$ip} ({$city}). Usurpation d'identité détectée.",
            'MITM'          => "Attaque Man-in-the-Middle depuis {$ip}. Interception de trafic SSL.",
        ];
        return $descriptions[$type] ?? "Activité suspecte détectée depuis {$ip} ({$city}).";
    }
}
