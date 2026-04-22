<?php

namespace App\Http\Controllers;

use App\Models\Attack;
use App\Models\Alert;
use App\Models\BlockedIp;
use App\Models\HoneypotInteraction;
use App\Models\HoneypotTrap;
use App\Models\Simulation;
use App\Services\AttackDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Affiche le tableau de bord
     */
    public function index()
    {
        $stats = $this->getStats();

        $recentAttacks = Attack::orderByDesc('created_at')->limit(10)->get();
        $recentAlerts = Alert::orderByDesc('created_at')->limit(5)->get();
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
            Cache::forget('dashboard.stats');
        }

        return response()->json($this->getStats());
    }

    /**
     * Calcule les statistiques du dashboard
     */
    private function getStats(): array
    {
        return Cache::remember('dashboard.stats', 5, function () {
            $attackTotals = DB::table('attacks')
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN severity = "critical" AND status != "blocked" THEN 1 ELSE 0 END) as critical')
                ->selectRaw('SUM(CASE WHEN status = "blocked" THEN 1 ELSE 0 END) as blocked')
                ->selectRaw('SUM(CASE WHEN status = "detected" AND is_simulation = 0 THEN 1 ELSE 0 END) as active')
                ->selectRaw('SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as attacks_per_hour', [now()->subHour()])
                ->first();

            $countriesCount = DB::table('attacks')->distinct('country')->count('country');
            $topAttackType = DB::table('attacks')
                ->selectRaw('type, COUNT(*) as cnt')
                ->groupBy('type')
                ->orderByDesc('cnt')
                ->first()?->type ?? 'N/A';

            $blockedIpsCount = BlockedIp::where(function ($q) {
                $q->whereNull('blocked_until')
                    ->orWhere('blocked_until', '>', now());
            })->count();

            return [
                'total_attacks' => (int) $attackTotals->total,
                'critical' => (int) $attackTotals->critical,
                'blocked' => (int) $attackTotals->blocked,
                'active' => (int) $attackTotals->active,
                'unread_alerts' => Alert::where('acknowledged', false)->count(),
                'simulations_run' => Simulation::count(),
                'countries_count' => $countriesCount,
                'top_attack_type' => $topAttackType,
                'attacks_per_hour' => (int) $attackTotals->attacks_per_hour,
                'high_risk_ips' => Attack::where('severity', 'critical')->distinct('source_ip')->count('source_ip'),
                'blocked_ips_count' => $blockedIpsCount,
                'active_honeypots' => HoneypotTrap::where('status', 'active')->count(),
                'recent_honeypots' => HoneypotInteraction::orderByDesc('created_at')->limit(5)->get(),
            ];
        });
    }
}
