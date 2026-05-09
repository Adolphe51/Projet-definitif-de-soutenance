<?php

namespace App\Console\Commands;

use App\Models\Attack;
use App\Models\BlockedIp;
use App\Services\AutoBlockService;
use Illuminate\Console\Command;

class AutoBlockCommand extends Command
{
    protected $signature   = 'cyberguard:autoblock
                                {--threshold=5 : Nombre d\'attaques avant blocage}
                                {--window=10 : Fenêtre de temps en minutes}
                                {--dry-run : Simuler sans bloquer réellement}';

    protected $description = 'Détecte et bloque automatiquement les IPs suspectes';

    public function __construct(
        private readonly AutoBlockService $autoBlockService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $threshold = (int) $this->option('threshold');
        $window    = (int) $this->option('window');
        $dryRun    = (bool) $this->option('dry-run');

        $this->info("🔍 Analyse des IPs suspectes (seuil: {$threshold} attaques en {$window} min)...");

        $suspiciousIps = Attack::selectRaw('source_ip, COALESCE(rule_id, type) as signature, COUNT(*) as cnt, MAX(severity) as max_severity, MAX(created_at) as last_seen')
            ->where('created_at', '>=', now()->subMinutes($window))
            ->where('status', '!=', 'blocked')
            ->where('is_simulation', false)
            ->groupBy('source_ip')
            ->groupBy('signature')
            ->havingRaw('COUNT(*) >= ?', [$threshold])
            ->orderByDesc('cnt')
            ->get();

        if ($suspiciousIps->isEmpty()) {
            $this->info('✅ Aucune IP suspecte détectée.');
            return Command::SUCCESS;
        }

        $this->warn("⚠️  {$suspiciousIps->count()} IP(s) suspecte(s) détectée(s):");

        $headers = ['IP', 'Attaques', 'Sévérité Max', 'Dernière vue', 'Action'];
        $rows    = [];
        $blockedCount = 0;

        foreach ($suspiciousIps as $suspect) {
            $alreadyBlocked = BlockedIp::isBlocked($suspect->source_ip);
            $allowlisted = $this->autoBlockService->isAllowlisted($suspect->source_ip);
            $action = $alreadyBlocked
                ? 'Déjà bloquée'
                : ($allowlisted ? 'Allowlist' : ($dryRun ? '[DRY-RUN] Bloquerait' : 'BLOQUÉE'));

            if (!$alreadyBlocked && !$allowlisted) {
                $latestAttack = Attack::query()
                    ->where('source_ip', $suspect->source_ip)
                    ->where(function ($query) use ($suspect) {
                        $query->where('rule_id', $suspect->signature)
                            ->orWhere('type', $suspect->signature);
                    })
                    ->orderByDesc('created_at')
                    ->first();

                if ($latestAttack && !$dryRun) {
                    $blocked = $this->autoBlockService->evaluateAttack($latestAttack, 'scheduler', [
                        'threshold_count' => $threshold,
                        'window_minutes' => $window,
                    ]);

                    if ($blocked) {
                        $blockedCount++;
                    }
                }
            }

            $rows[] = [
                $suspect->source_ip,
                $suspect->cnt,
                strtoupper($suspect->max_severity),
                \Carbon\Carbon::parse($suspect->last_seen)->diffForHumans(),
                $action,
            ];
        }

        $this->table($headers, $rows);

        if (!$dryRun) {
            $this->info("✅ {$blockedCount} IP(s) bloquée(s).");
        }

        return Command::SUCCESS;
    }
}
