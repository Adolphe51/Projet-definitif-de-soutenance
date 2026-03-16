<?php

namespace App\Console\Commands;

use App\Models\Attack;
use App\Models\Alert;
use App\Models\HoneypotInteraction;
use Illuminate\Console\Command;

class CleanupCommand extends Command
{
    protected $signature   = 'cyberguard:cleanup {--days=30 : Supprimer les données plus vieilles que N jours}';
    protected $description = 'Nettoie les vieilles données de la base CyberGuard';

    public function handle(): int
    {
        $days  = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $this->info("🧹 Nettoyage des données antérieures au {$cutoff->format('d/m/Y')}...");

        $attacks      = Attack::where('created_at', '<', $cutoff)->count();
        $alerts       = Alert::where('created_at', '<', $cutoff)->count();
        $interactions = HoneypotInteraction::where('created_at', '<', $cutoff)->count();

        if ($this->confirm("Supprimer {$attacks} attaques, {$alerts} alertes, {$interactions} interactions honeypot?")) {
            Attack::where('created_at', '<', $cutoff)->delete();
            Alert::where('created_at', '<', $cutoff)->where('acknowledged', true)->delete();
            HoneypotInteraction::where('created_at', '<', $cutoff)->delete();

            $this->info("✅ Nettoyage terminé.");
            $this->line("  - Attaques supprimées  : {$attacks}");
            $this->line("  - Alertes supprimées   : {$alerts}");
            $this->line("  - Interactions supp.   : {$interactions}");
        }

        return Command::SUCCESS;
    }
}
