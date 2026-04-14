<?php

namespace App\Http\Controllers;

use App\Models\Attack;
use App\Models\Alert;
use App\Models\Simulation;
use App\Models\BlockedIp;
use App\Models\HoneypotTrap;
use App\Models\HoneypotInteraction;
use App\Services\AttackDetectionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord
     */
    public function index()
    {
        $stats = $this->getStats();

        $recentAttacks = Attack::orderByDesc('created_at')->limit(10)->get();
        $recentAlerts  = Alert::orderByDesc('created_at')->limit(5)->get();
        $recentInteractions = HoneypotInteraction::orderByDesc('created_at')->limit(5)->get();

        return view('dashboard.index', compact(
            'stats',
            'recentAttacks',
            'recentAlerts',
            'recentInteractions'
        ));
    }

    /**
     * Retourne les stats au format JSON pour auto-refresh
     */
    public function apiStats(): JsonResponse
    {
        // Génération aléatoire d'attaque pour le live (~30%)
        if (rand(1, 100) <= 30) {
            AttackDetectionService::generateAttack(false);
        }

        return response()->json($this->getStats());
    }

    /**
     * Calcule les statistiques du dashboard
     */
    private function getStats(): array
    {
        // Attaques
        $totalAttacks = Attack::where('is_simulation', false)->count();
        $critical     = Attack::where('severity', 'critical')->where('status', '!=', 'blocked')->count();
        $blocked      = Attack::where('status', 'blocked')->count();
        $active       = Attack::where('status', 'detected')->where('is_simulation', false)->count();

        // Alertes
        $unreadAlerts = Alert::where('acknowledged', false)->count();

        // Simulations
        $simulationsRun = Simulation::count();

        // Attaques par pays et type
        $countriesCount = Attack::distinct('country')->count('country');
        $topAttackType  = Attack::selectRaw('type, COUNT(*) as cnt')
            ->groupBy('type')
            ->orderByDesc('cnt')
            ->first()?->type ?? 'N/A';
        $attacksPerHour = Attack::where('created_at', '>=', now()->subHour())->count();

        // IP bloquées
        $blockedIpsCount = BlockedIp::where(function ($q) {
            $q->whereNull('blocked_until')
                ->orWhere('blocked_until', '>', now());
        })->count();

        $highRiskIpsCount = Attack::where('severity', 'critical')
            ->distinct('source_ip')
            ->count('source_ip');

        // Honeypots
        $activeHoneypots = HoneypotTrap::where('status', 'active')->count();
        $recentHoneypotInteractions = HoneypotInteraction::orderByDesc('created_at')->limit(5)->get();

        return [
            'total_attacks'       => $totalAttacks,
            'critical'            => $critical,
            'blocked'             => $blocked,
            'active'              => $active,
            'unread_alerts'       => $unreadAlerts,
            'simulations_run'     => $simulationsRun,
            'countries_count'     => $countriesCount,
            'top_attack_type'     => $topAttackType,
            'attacks_per_hour'    => $attacksPerHour,
            'high_risk_ips'       => $highRiskIpsCount,
            'blocked_ips_count'   => $blockedIpsCount,
            'active_honeypots'    => $activeHoneypots,
            'recent_honeypots'    => $recentHoneypotInteractions,
        ];
    }
}
