<?php

namespace App\Console\Commands;

use App\Services\HoneypotService;
use App\Models\HoneypotTrap;
use App\Models\HoneypotInteraction;
use Illuminate\Console\Command;

class HoneypotCommand extends Command
{
    protected $signature   = 'cyberguard:honeypot
                                {action=status : status|init|simulate|reset|report}
                                {--trap= : ID ou nom du piège ciblé}
                                {--count=1 : Nombre d\'interactions à simuler}';

    protected $description = 'Gère l\'environnement honeypot (init, simulate, report, reset)';

    public function handle(): int
    {
        return match($this->argument('action')) {
            'status'   => $this->showStatus(),
            'init'     => $this->initTraps(),
            'simulate' => $this->simulateInteractions(),
            'reset'    => $this->resetHoneypot(),
            'report'   => $this->generateReport(),
            default    => $this->showHelp(),
        };
    }

    private function showStatus(): int
    {
        $this->info('🍯 CyberGuard Honeypot — Statut');
        $this->newLine();

        $traps = HoneypotTrap::all();
        if ($traps->isEmpty()) {
            $this->warn('Aucun piège configuré. Lancez: php artisan cyberguard:honeypot init');
            return Command::SUCCESS;
        }

        $headers = ['ID', 'Nom', 'Type', 'Port', 'Path', 'Statut', 'Interactions', 'Dernière activité'];
        $rows    = $traps->map(fn($t) => [
            $t->id,
            $t->name,
            $t->type,
            $t->port ?? '—',
            $t->path ?? '—',
            match($t->status) {
                'active'    => '<fg=green>● ACTIF</>',
                'triggered' => '<fg=red>⚡ DÉCLENCHÉ</>',
                default     => '<fg=gray>○ INACTIF</>',
            },
            $t->interactions_count,
            $t->last_triggered_at?->diffForHumans() ?? 'Jamais',
        ])->toArray();

        $this->table($headers, $rows);
        $this->info('Total interactions: ' . HoneypotInteraction::count());
        $this->info('Credentials capturés: ' . HoneypotInteraction::whereNotNull('credentials_attempted')->count());

        return Command::SUCCESS;
    }

    private function initTraps(): int
    {
        $this->info('🚀 Initialisation des pièges honeypot...');
        HoneypotService::createDefaultTraps();
        $this->info('✅ ' . HoneypotTrap::count() . ' pièges déployés avec succès!');
        $this->call('cyberguard:honeypot', ['action' => 'status']);
        return Command::SUCCESS;
    }

    private function simulateInteractions(): int
    {
        $count   = (int) $this->option('count');
        $trapId  = $this->option('trap');
        $traps   = $trapId ? HoneypotTrap::where('id', $trapId)->get() : HoneypotTrap::where('status', 'active')->get();

        if ($traps->isEmpty()) {
            $this->warn('Aucun piège actif. Lancez: php artisan cyberguard:honeypot init');
            return Command::FAILURE;
        }

        $this->info("🎣 Simulation de {$count} interaction(s)...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        for ($i = 0; $i < $count; $i++) {
            $trap        = $traps->random();
            $interaction = HoneypotService::simulateInteraction($trap->id);
            $bar->advance();
            $this->newLine();

            $creds = $interaction->credentials_attempted;
            $this->line(sprintf(
                '  🍯 %s ← <fg=cyan>%s</> (%s) | Risk: <fg=%s>%d/100</>%s',
                $trap->name,
                $interaction->source_ip,
                $interaction->country,
                $interaction->risk_score >= 80 ? 'red' : ($interaction->risk_score >= 60 ? 'yellow' : 'green'),
                $interaction->risk_score,
                $creds ? " | Creds: {$creds['username']}:{$creds['password']}" : ''
            ));
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✅ {$count} interaction(s) simulée(s).");
        return Command::SUCCESS;
    }

    private function resetHoneypot(): int
    {
        if (!$this->confirm('⚠️  Supprimer TOUTES les interactions honeypot?', false)) {
            return Command::SUCCESS;
        }
        HoneypotInteraction::truncate();
        HoneypotTrap::query()->update(['interactions_count' => 0, 'status' => 'active', 'last_triggered_at' => null]);
        $this->info('✅ Honeypot réinitialisé.');
        return Command::SUCCESS;
    }

    private function generateReport(): int
    {
        $this->info('📊 Rapport Honeypot CyberGuard');
        $this->info(str_repeat('─', 60));

        $total   = HoneypotInteraction::count();
        $unique  = HoneypotInteraction::distinct('source_ip')->count('source_ip');
        $creds   = HoneypotInteraction::whereNotNull('credentials_attempted')->count();
        $highRsk = HoneypotInteraction::where('risk_score', '>=', 80)->count();

        $this->line("Total interactions  : {$total}");
        $this->line("IPs uniques         : {$unique}");
        $this->line("Credentials capturés: {$creds}");
        $this->line("Interactions risque↑: {$highRsk}");
        $this->newLine();

        // Top pays
        $topCountries = HoneypotInteraction::selectRaw('country, COUNT(*) as cnt')
            ->groupBy('country')->orderByDesc('cnt')->limit(5)->get();

        $this->info('Top 5 Pays Sources:');
        foreach ($topCountries as $c) {
            $this->line("  {$c->country}: {$c->cnt}");
        }
        $this->newLine();

        // Top credentials
        $topCreds = HoneypotInteraction::whereNotNull('credentials_attempted')
            ->get()
            ->groupBy(fn($i) => ($i->credentials_attempted['username'] ?? '?') . ':' . ($i->credentials_attempted['password'] ?? '?'))
            ->sortByDesc->count()
            ->take(5);

        $this->info('Top 5 Credentials Testés:');
        foreach ($topCreds as $cred => $items) {
            $this->line("  {$cred} ({$items->count()}x)");
        }

        return Command::SUCCESS;
    }

    private function showHelp(): int
    {
        $this->info('Actions disponibles: status | init | simulate | reset | report');
        return Command::SUCCESS;
    }
}
