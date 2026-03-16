<?php

namespace App\Console\Commands;

use App\Models\Attack;
use App\Models\BlockedIp;
use App\Models\Alert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoBlockCommand extends Command
{
    protected $signature   = 'cyberguard:autoblock
                                {--threshold=5 : Nombre d\'attaques avant blocage}
                                {--window=10 : Fenêtre de temps en minutes}
                                {--dry-run : Simuler sans bloquer réellement}';

    protected $description = 'Détecte et bloque automatiquement les IPs suspectes';

    public function handle(): int
    {
        $threshold = (int) $this->option('threshold');
        $window    = (int) $this->option('window');
        $dryRun    = (bool) $this->option('dry-run');

        $this->info("🔍 Analyse des IPs suspectes (seuil: {$threshold} attaques en {$window} min)...");

        $suspiciousIps = Attack::selectRaw('source_ip, COUNT(*) as cnt, MAX(severity) as max_severity, MAX(created_at) as last_seen')
            ->where('created_at', '>=', now()->subMinutes($window))
            ->where('status', '!=', 'blocked')
            ->groupBy('source_ip')
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

        foreach ($suspiciousIps as $suspect) {
            $alreadyBlocked = BlockedIp::isBlocked($suspect->source_ip);
            $action         = $alreadyBlocked ? 'Déjà bloquée' : ($dryRun ? '[DRY-RUN] Bloquerait' : 'BLOQUÉE');

            if (!$alreadyBlocked && !$dryRun) {
                BlockedIp::blockIp(
                    $suspect->source_ip,
                    "Auto-bloqué: {$suspect->cnt} attaques en {$window} min"
                );

                Attack::where('source_ip', $suspect->source_ip)->update(['status' => 'blocked']);

                Alert::create([
                    'title'    => "🤖 AUTO-BLOCAGE: {$suspect->source_ip}",
                    'message'  => "{$suspect->cnt} attaques détectées en {$window} min — IP bloquée automatiquement.",
                    'severity' => 'high',
                    'type'     => 'system',
                ]);
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
            $this->info("✅ " . $suspiciousIps->where(fn($s) => !BlockedIp::isBlocked($s->source_ip))->count() . " IP(s) bloquée(s).");
        }

        return Command::SUCCESS;
    }
}
