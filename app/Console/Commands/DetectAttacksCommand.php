<?php

namespace App\Console\Commands;

use App\Services\AttackDetectionService;
use App\Services\HoneypotService;
use App\Models\Attack;
use App\Models\HoneypotTrap;
use Illuminate\Console\Command;

class DetectAttacksCommand extends Command
{
    protected $signature   = 'cyberguard:detect
                                {--count=1 : Nombre d\'attaques à générer}
                                {--type= : Type d\'attaque spécifique}
                                {--severity= : Sévérité forcée}
                                {--simulation : Marquer comme simulation}';

    protected $description = 'Génère des attaques simulées pour la détection en temps réel';

    public function handle(): int
    {
        $count      = (int) $this->option('count');
        $simulation = (bool) $this->option('simulation');

        $this->info("🛡️  CyberGuard — Génération de {$count} attaque(s)...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        for ($i = 0; $i < $count; $i++) {
            $attack = AttackDetectionService::generateAttack($simulation);
            $bar->advance();

            $this->newLine();
            $this->line(sprintf(
                '  [%s] %s %s ← <fg=cyan>%s</> (%s, %s)',
                strtoupper($attack->severity),
                $attack->type_icon,
                $attack->type,
                $attack->source_ip,
                $attack->city,
                $attack->country
            ));

            if ($attack->alarm_triggered) {
                $this->warn("  🔊 Alarme déclenchée pour cette attaque!");
            }

            usleep(100000); // 100ms entre chaque
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✅ {$count} attaque(s) générée(s) avec succès.");
        $this->info("📊 Total attaques en base: " . Attack::count());

        return Command::SUCCESS;
    }
}
