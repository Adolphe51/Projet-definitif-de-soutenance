<?php

namespace App\Http\Controllers;

use App\Models\Attack;
use App\Models\Alert;
use App\Models\AuditLog;
use App\Models\BlockedIp;
use App\Models\HoneypotInteraction;
use App\Models\HoneypotTrap;
use App\Models\Simulation;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
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
        $recentAlerts = Alert::with('attack')->orderByDesc('created_at')->limit(6)->get();
        $recentAuditTrail = $this->recentAuditTrail();

        return view('dashboard.index', compact(
            'stats',
            'recentAttacks',
            'recentAlerts',
            'recentAuditTrail'
        ));
    }

    /**
     * Retourne les stats au format JSON pour auto-refresh
     */
    public function apiStats(): JsonResponse
    {
        return response()->json($this->getStats());
    }

    /**
     * Calcule les statistiques du dashboard
     */
    private function getStats(): array
    {
        return Cache::remember('dashboard.stats', 5, function () {
            $recentWindowAttacks = Attack::query()
                ->where('created_at', '>=', now()->subDays(7))
                ->get([
                    'id',
                    'type',
                    'severity',
                    'status',
                    'source_ip',
                    'country',
                    'created_at',
                    'updated_at',
                ]);

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

            $topAttackTypes = DB::table('attacks')
                ->selectRaw('type, COUNT(*) as cnt')
                ->groupBy('type')
                ->orderByDesc('cnt')
                ->limit(5)
                ->get()
                ->map(fn($row) => [
                    'label' => $row->type,
                    'count' => (int) $row->cnt,
                ])
                ->values()
                ->all();

            $topSourceIps = DB::table('attacks')
                ->selectRaw('source_ip, COUNT(*) as cnt')
                ->groupBy('source_ip')
                ->orderByDesc('cnt')
                ->limit(5)
                ->get()
                ->map(fn($row) => [
                    'label' => $row->source_ip,
                    'count' => (int) $row->cnt,
                ])
                ->values()
                ->all();

            $topCountries = DB::table('attacks')
                ->selectRaw('country, COUNT(*) as cnt')
                ->whereNotNull('country')
                ->groupBy('country')
                ->orderByDesc('cnt')
                ->limit(5)
                ->get()
                ->map(fn($row) => [
                    'label' => $row->country ?: 'Inconnu',
                    'count' => (int) $row->cnt,
                ])
                ->values()
                ->all();

            $resolvedDurations = Attack::query()
                ->where('status', 'resolved')
                ->whereNotNull('created_at')
                ->whereNotNull('updated_at')
                ->get(['created_at', 'updated_at']);

            $meanResolutionMinutes = round((float) $resolvedDurations->avg(function (Attack $attack) {
                return max(0, $attack->created_at->diffInMinutes($attack->updated_at));
            }), 1);

            $blockRatePercent = ((int) $attackTotals->total) > 0
                ? round((((int) $attackTotals->blocked) / ((int) $attackTotals->total)) * 100, 1)
                : 0.0;

            $trends24h = $this->buildHourlyTrend($recentWindowAttacks);
            $trends7d = $this->buildDailyTrend($recentWindowAttacks);

            return [
                'total_attacks' => (int) $attackTotals->total,
                'critical' => (int) $attackTotals->critical,
                'blocked' => (int) $attackTotals->blocked,
                'active' => (int) $attackTotals->active,
                'unread_alerts' => Alert::where('acknowledged', false)->count(),
                'simulations_run' => Simulation::count(),
                'manual_simulation_attacks' => Attack::where('is_simulation', true)->count(),
                'countries_count' => $countriesCount,
                'top_attack_type' => $topAttackType,
                'attacks_per_hour' => (int) $attackTotals->attacks_per_hour,
                'high_risk_ips' => Attack::where('severity', 'critical')->distinct('source_ip')->count('source_ip'),
                'blocked_ips_count' => $blockedIpsCount,
                'block_rate_percent' => $blockRatePercent,
                'mean_resolution_minutes' => $meanResolutionMinutes,
                'resolved_incidents' => $resolvedDurations->count(),
                'active_honeypots' => HoneypotTrap::where('status', 'active')->count(),
                'recent_honeypots' => HoneypotInteraction::orderByDesc('created_at')->limit(5)->get(),
                'auth_audit_events' => AuditLog::whereIn('action', ['login_success', 'login_failed', 'otp_verified', 'otp_failed'])
                    ->where('created_at', '>=', now()->subDay())
                    ->count(),
                'intranet_audit_events' => AuditLog::where('action', 'like', 'intranet_%')
                    ->where('created_at', '>=', now()->subDay())
                    ->count(),
                'trends_24h' => $trends24h,
                'trends_7d' => $trends7d,
                'top_attack_types' => $topAttackTypes,
                'top_source_ips' => $topSourceIps,
                'top_countries' => $topCountries,
                'attack_chart' => [
                    'labels' => array_column($topAttackTypes, 'label'),
                    'values' => array_column($topAttackTypes, 'count'),
                ],
            ];
        });
    }

    private function recentAuditTrail()
    {
        return AuditLog::with('actor:id,nom,email')
            ->where(function ($query) {
                $query->whereIn('action', ['login_success', 'login_failed', 'otp_verified', 'otp_failed'])
                    ->orWhere('action', 'like', 'intranet_%')
                    ->orWhere('action', 'like', 'attack.%')
                    ->orWhere('action', 'like', 'blocked_ip.%');
            })
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();
    }

    private function buildHourlyTrend($attacks): array
    {
        $buckets = [];

        for ($offset = 23; $offset >= 0; $offset--) {
            $hour = now()->copy()->subHours($offset)->startOfHour();
            $key = $hour->format('Y-m-d H:00:00');
            $buckets[$key] = [
                'label' => $hour->format('H\h'),
                'count' => 0,
            ];
        }

        foreach ($attacks as $attack) {
            $bucketKey = $attack->created_at?->copy()->startOfHour()->format('Y-m-d H:00:00');

            if ($bucketKey && array_key_exists($bucketKey, $buckets)) {
                $buckets[$bucketKey]['count']++;
            }
        }

        return [
            'labels' => array_column($buckets, 'label'),
            'values' => array_column($buckets, 'count'),
        ];
    }

    private function buildDailyTrend($attacks): array
    {
        $buckets = [];

        for ($offset = 6; $offset >= 0; $offset--) {
            $day = now()->copy()->subDays($offset)->startOfDay();
            $key = $day->format('Y-m-d');
            $buckets[$key] = [
                'label' => $day->locale('fr')->translatedFormat('D'),
                'count' => 0,
            ];
        }

        foreach ($attacks as $attack) {
            $bucketKey = $attack->created_at?->copy()->startOfDay()->format('Y-m-d');

            if ($bucketKey && array_key_exists($bucketKey, $buckets)) {
                $buckets[$bucketKey]['count']++;
            }
        }

        return [
            'labels' => array_column($buckets, 'label'),
            'values' => array_column($buckets, 'count'),
        ];
    }
}
