<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\AttackDetectionService;
use App\Models\Simulation;

class CyberGuardSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🛡️  Seeding CyberGuard database...');

        // 1. Pièges honeypot
        $this->command->info('🍯 Déploiement des pièges honeypot...');
        \App\Services\HoneypotService::createDefaultTraps();

        // 2. Attaques réelles
        $this->command->info('💀 Génération des attaques de démo...');
        for ($i = 0; $i < 60; $i++) {
            AttackDetectionService::generateAttack(false);
        }

        // 3. Attaques simulées
        for ($i = 0; $i < 15; $i++) {
            AttackDetectionService::generateAttack(true);
        }

        // 4. Simulations
        $types = ['DDoS', 'SQL Injection', 'Brute Force', 'Port Scan', 'XSS', 'Ransomware'];
        foreach ($types as $type) {
            Simulation::create([
                'name'             => "Demo-{$type}-" . date('Ymd'),
                'attack_type'      => $type,
                'target_ip'        => '192.168.1.' . rand(1, 254),
                'duration_seconds' => rand(30, 120),
                'intensity'        => ['low', 'medium', 'high'][rand(0, 2)],
                'status'           => 'completed',
                'packets_sent'     => rand(1000, 500000),
                'started_at'       => now()->subMinutes(rand(30, 240)),
                'completed_at'     => now()->subMinutes(rand(1, 29)),
            ]);
        }

        // 5. Interactions honeypot
        $traps = \App\Models\HoneypotTrap::all();
        foreach ($traps as $trap) {
            for ($i = 0; $i < rand(2, 8); $i++) {
                \App\Services\HoneypotService::simulateInteraction($trap->id);
            }
        }

        // 6. IPs bloquées
        foreach (['185.220.101.10', '103.21.244.15', '45.142.212.100'] as $ip) {
            \App\Models\BlockedIp::blockIp($ip, 'Bloqué lors de la démo initiale');
        }

        // 7. Alerte de bienvenue
        \App\Models\Alert::create([
            'title'   => '🛡️ CyberGuard Opérationnel',
            'message' => 'Système initialisé. ' . \App\Models\Attack::count() . ' attaques chargées.',
            'severity'=> 'low',
            'type'    => 'system',
        ]);

        $this->command->info('✅ Seeding terminé! Attaques: ' . \App\Models\Attack::count());
    }
}
