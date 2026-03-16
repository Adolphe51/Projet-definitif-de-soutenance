<?php

namespace App\Services;

use App\Models\HoneypotTrap;
use App\Models\HoneypotInteraction;
use App\Models\Alert;

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
                ['id' => 1, 'username' => 'john.doe', 'email' => 'john.doe@company.com', 'role' => 'admin'],
                ['id' => 2, 'username' => 'jane.smith', 'email' => 'jane.smith@company.com', 'role' => 'user'],
                ['id' => 3, 'username' => 'bob.wilson', 'email' => 'bob.wilson@company.com', 'role' => 'manager'],
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
            [
                'name' => 'API REST Fictive',
                'type' => 'fake_api',
                'fake_service' => 'HTTPS',
                'port' => 443,
                'path' => '/api/v1',
                'description' => 'Fausse API',
                'lure_content' => json_encode($data['api_keys']),
                'status' => 'active',
            ],
        ];

        foreach ($traps as $trap) {
            HoneypotTrap::firstOrCreate(['name' => $trap['name']], $trap);
        }
    }

    // 🔥 MÉTHODE MANQUANTE — AJOUTÉE
    public static function simulateInteraction(int $trapId): void
    {
        $ip = rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 255);

        $interaction = HoneypotInteraction::create([
            'honeypot_trap_id' => $trapId,
            'ip_address' => $ip,
            'country' => 'Unknown',
            'payload' => 'Suspicious request detected',
            'severity' => rand(1, 5),
            'created_at' => now()->subMinutes(rand(1, 1440)),
        ]);

        // Crée aussi une alerte associée
        Alert::create([
            'title' => 'Intrusion détectée',
            'message' => "Tentative d'accès depuis $ip",
            'severity' => $interaction->severity,
            'status' => 'new',
        ]);
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