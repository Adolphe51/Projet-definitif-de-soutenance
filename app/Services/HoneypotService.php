<?php

namespace App\Services;

use App\Models\HoneypotTrap;
use App\Models\HoneypotInteraction;
use App\Models\Alert;
use App\Services\GeoService;

class HoneypotService
{
    // Identifiants appâts
    private static array $lureCredentials = [
        ['username' => 'admin', 'password' => 'admin123'],
        ['username' => 'root', 'password' => 'root'],
        ['username' => 'administrator', 'password' => 'password'],
        ['username' => 'admin', 'password' => '123456'],
        ['username' => 'sa', 'password' => 'sa'],
        ['username' => 'test', 'password' => 'test'],
    ];

    // Données fictives réalistes
    private static function fakeDatasets(): array
    {
        return [
            'users_db' => [
                ['id' => 1, 'username' => 'john.doe', 'email' => 'john.doe@company.com', 'role' => 'admin', 'last_login' => '2026-05-02 08:42'],
                ['id' => 2, 'username' => 'jane.smith', 'email' => 'jane.smith@company.com', 'role' => 'user', 'last_login' => '2026-05-01 17:15'],
                ['id' => 3, 'username' => 'bob.wilson', 'email' => 'bob.wilson@company.com', 'role' => 'manager', 'last_login' => '2026-04-30 11:03'],
            ],
            'api_keys' => [
                ['key' => 'sk_live_' . str_repeat('0', 32), 'service' => 'payment_gateway'],
                ['key' => 'tok_' . str_repeat('f', 24), 'service' => 'internal_api'],
            ],
            'config' => [
                'db_host' => '10.0.0.5',
                'db_name' => 'production_db',
                'db_user' => 'prod_user',
                'db_pass' => '[HONEYPOT]',
            ],
        ];
    }

    // Création des pièges
    public static function createDefaultTraps(): void
    {
        $data = self::fakeDatasets();

        $traps = [
            [
                'name' => 'Portail Admin Fictif',
                'type' => 'fake_admin',
                'fake_service' => 'HTTP',
                'port' => 8080,
                'path' => '/admin',
                'description' => 'Panneau admin vulnérable',
                'lure_content' => json_encode($data['users_db']),
                'status' => 'active',
            ],
            [
                'name' => 'phpMyAdmin Piège',
                'type' => 'fake_phpmyadmin',
                'fake_service' => 'HTTP',
                'port' => 8081,
                'path' => '/phpmyadmin',
                'description' => 'Faux phpMyAdmin',
                'lure_content' => json_encode($data['config']),
                'status' => 'active',
            ],
        ];

        foreach ($traps as $trap) {
            HoneypotTrap::firstOrCreate(['name' => $trap['name']], $trap);
        }
    }

    // 🔥 MÉTHODE MANQUANTE — AJOUTÉE
    public static function simulateInteraction(int $trapId): HoneypotInteraction
    {
        $trap = HoneypotTrap::findOrFail($trapId);
        $ip = GeoService::generateRandomIp();
        $geo = GeoService::lookupSimulated($ip);

        $interaction = HoneypotInteraction::create([
            'honeypot_trap_id' => $trapId,
            'source_ip' => $ip,
            'country' => $geo['country'] ?? 'Unknown',
            'city' => $geo['city'] ?? null,
            'latitude' => $geo['lat'] ?? null,
            'longitude' => $geo['lon'] ?? null,
            'isp' => $geo['isp'] ?? null,
            'method' => 'GET',
            'path' => $trap->path ?? '/',
            'user_agent' => 'CyberGuard Simulator',
            'payload' => 'Suspicious request detected (simulated)',
            'credentials_attempted' => null,
            'session_duration' => rand(1, 20),
            'actions_taken' => null,
            'risk_score' => rand(40, 90),
        ]);

        $trap->increment('interactions_count');
        $trap->update([
            'last_triggered_at' => now(),
            'status' => 'triggered',
        ]);

        // Crée aussi une alerte associée
        Alert::create([
            'title' => "🍯 Honeypot interaction (simulée) — {$trap->name}",
            'message' => "Tentative d'accès simulée depuis {$ip} ({$geo['city']}, {$geo['country']}) → {$trap->path}",
            'severity' => 'high',
            'type' => 'honeypot',
        ]);

        return $interaction;
    }

    public static function getLureCredentials(): array
    {
        return self::$lureCredentials;
    }

    public static function getFakeDataset(string $key): array
    {
        return self::fakeDatasets()[$key] ?? [];
    }
}
