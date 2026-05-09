<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Simulation;

class CyberGuardSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🛡️  Seeding CyberGuard database...');

        // Vérifier le mode démo avant de générer les données fictives
        $demoMode = config('cyberguard.mode.is_demo', false);
        $isProduction = config('cyberguard.mode.is_production', false);

        // 1. Pièges honeypot (toujours créés)
        $this->command->info('🍯 Déploiement des pièges honeypot...');
        \App\Services\HoneypotService::createDefaultTraps();

        // 2. Données d'incident et simulations
        if ($demoMode && !$isProduction) {
            $this->command->info('🧪 Mode DÉMO: aucune attaque ni simulation auto-générée. Utilisez le laboratoire ou un scénario réseau réel pour alimenter CyberGuard.');
        } else {
            $this->command->warn('⚠️  Génération automatique d\'attaques désactivée.');
        }

        // 3. Historique de simulations laissé vide par défaut
        Simulation::query()->whereIn('status', ['pending', 'running', 'completed', 'stopped'])->delete();

        // 4. IPs bloquées
        $this->command->info('🔒 Configuration des IPs bloquées...');
        foreach (['185.220.101.10', '103.21.244.15', '45.142.212.100'] as $ip) {
            \App\Models\BlockedIp::blockIp($ip, 'Bloqué lors du seeding initial');
        }

        // 5. Alerte de bienvenue
        $mode = $demoMode && !$isProduction ? '(MODE DÉMO)' : '(MODE PROD)';
        \App\Models\Alert::create([
            'title'   => "🛡️ CyberGuard Opérationnel {$mode}",
            'message' => 'Système initialisé. Aucun incident fictif n\'a été généré automatiquement.',
            'severity'=> 'low',
            'type'    => 'system',
        ]);

        $this->command->info('✅ Seeding terminé! Attaques en BD: ' . \App\Models\Attack::count());
        $this->command->line('ℹ️  Les simulations et attaques doivent désormais être déclenchées volontairement pendant la démonstration.');
    }
}
