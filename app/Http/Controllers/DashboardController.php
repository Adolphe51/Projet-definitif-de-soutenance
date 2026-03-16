<?php

namespace App\Http\Controllers;

// Importation de la classe de base pour corriger l'erreur "Class not found"
use App\Http\Controllers\Controller;

use App\Models\Attack;
use App\Models\Alert;
use App\Models\Simulation;
use App\Services\AttackDetectionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = $this->getStats();
        $recentAttacks = Attack::orderByDesc('created_at')->limit(10)->get();
        $recentAlerts  = Alert::orderByDesc('created_at')->limit(5)->get();
        $criticalCount = Attack::where('severity', 'critical')->where('status', '!=', 'blocked')->count();

        return view('dashboard.index', compact('stats', 'recentAttacks', 'recentAlerts', 'criticalCount'));
    }

    public function stats(): JsonResponse
    {
        return response()->json($this->getStats());
    }

    public function apiStats(): JsonResponse
    {
        // Génère automatiquement une attaque aléatoire ~30% du temps pour simuler le "live"
        if (rand(1, 100) <= 30) {
            AttackDetectionService::generateAttack(false);
        }
        return response()->json($this->getStats());
    }

    private function getStats(): array
    {
        return [
            'total_attacks'    => Attack::where('is_simulation', false)->count(),
            'blocked'          => Attack::where('status', 'blocked')->count(),
            'critical'         => Attack::where('severity', 'critical')->count(),
            'active'           => Attack::where('status', 'detected')->where('is_simulation', false)->count(),
            'unread_alerts'    => Alert::where('acknowledged', false)->count(),
            'simulations_run'  => Simulation::count(),
            'countries_count'  => Attack::distinct('country')->count('country'),
            'top_attack_type'  => Attack::selectRaw('type, COUNT(*) as cnt')->groupBy('type')->orderByDesc('cnt')->first()?->type ?? 'N/A',
            'attacks_per_hour' => Attack::where('created_at', '>=', now()->subHour())->count(),
            'high_risk_ips'    => Attack::where('severity', 'critical')->distinct('source_ip')->count('source_ip'),
        ];
    }
}