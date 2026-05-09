<?php

namespace App\Console\Commands;

use App\Models\Attack;
use App\Models\Alert;
use App\Models\HoneypotInteraction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupCommand extends Command
{
    protected $signature   = 'cyberguard:cleanup
                            {--days=30 : Supprimer les données plus vieilles que N jours}
                            {--focused-demo : Garder seulement un petit jeu de donnees coherent avec les 3 scenarios retenus}
                            {--keep-attacks-per-type=4 : Nombre d attaques a conserver par type supporte}
                            {--keep-simulations-per-type=1 : Nombre de simulations a conserver par type supporte}
                            {--force : Executer sans confirmation interactive}';
    protected $description = 'Nettoie les vieilles données de la base CyberGuard';

    public function handle(): int
    {
        if ((bool) $this->option('focused-demo')) {
            return $this->cleanupFocusedDemo();
        }

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

    private function cleanupFocusedDemo(): int
    {
        $supportedTypes = collect(config('cyberguard.detection.monitored_types', []))
            ->filter()
            ->values();
        $keepAttacksPerType = max(0, (int) $this->option('keep-attacks-per-type'));
        $keepSimulationsPerType = max(0, (int) $this->option('keep-simulations-per-type'));

        $allAttacks = Attack::query()
            ->select(['id', 'type', 'created_at'])
            ->orderByDesc('created_at')
            ->get();

        $allSimulations = \App\Models\Simulation::query()
            ->select(['id', 'attack_type', 'created_at'])
            ->orderByDesc('created_at')
            ->get();

        $attackIdsToKeep = collect();
        foreach ($supportedTypes as $type) {
            $attackIdsToKeep = $attackIdsToKeep->merge(
                $allAttacks->where('type', $type)->take($keepAttacksPerType)->pluck('id')
            );
        }

        $simulationIdsToKeep = collect();
        foreach ($supportedTypes as $type) {
            $simulationIdsToKeep = $simulationIdsToKeep->merge(
                $allSimulations->where('attack_type', $type)->take($keepSimulationsPerType)->pluck('id')
            );
        }

        $attackIdsToDelete = $allAttacks
            ->reject(fn (Attack $attack) => $attackIdsToKeep->contains($attack->id))
            ->pluck('id')
            ->values();

        $simulationIdsToDelete = $allSimulations
            ->reject(fn ($simulation) => $simulationIdsToKeep->contains($simulation->id))
            ->pluck('id')
            ->values();

        $alertsToDelete = $attackIdsToDelete->isEmpty()
            ? 0
            : Alert::whereIn('attack_id', $attackIdsToDelete)->count();

        $this->info('🧹 Nettoyage ciblé du jeu de démonstration...');
        $this->line('Types conservés : ' . $supportedTypes->implode(', '));
        $this->line("Attaques à conserver par type : {$keepAttacksPerType}");
        $this->line("Simulations à conserver par type : {$keepSimulationsPerType}");
        $this->line('Attaques supprimées : ' . $attackIdsToDelete->count());
        $this->line('Simulations supprimées : ' . $simulationIdsToDelete->count());
        $this->line('Alertes impactées : ' . $alertsToDelete);

        if (!(bool) $this->option('force') && !$this->confirm('Confirmer ce nettoyage ciblé ?')) {
            $this->warn('Nettoyage annulé.');
            return Command::SUCCESS;
        }

        DB::transaction(function () use ($attackIdsToDelete, $simulationIdsToDelete) {
            if ($simulationIdsToDelete->isNotEmpty()) {
                \App\Models\Simulation::whereIn('id', $simulationIdsToDelete)->delete();
            }

            if ($attackIdsToDelete->isNotEmpty()) {
                Attack::whereIn('id', $attackIdsToDelete)->delete();
            }
        });

        $this->info('✅ Jeu de démonstration recentré.');
        $this->line('Attaques restantes : ' . Attack::count());
        $this->line('Simulations restantes : ' . \App\Models\Simulation::count());

        return Command::SUCCESS;
    }
}
