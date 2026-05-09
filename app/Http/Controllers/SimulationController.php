<?php

namespace App\Http\Controllers;

use App\Models\Attack;
use App\Models\Simulation;
use App\Services\AttackDetectionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SimulationController extends Controller
{
    public function index()
    {
        $simulations = Simulation::orderByDesc('created_at')->get();
        $types = AttackDetectionService::supportedAttackTypes();
        return view('simulations.index', compact('simulations', 'types'));
    }

    public function launch(Request $request): JsonResponse
    {
        $types = AttackDetectionService::supportedAttackTypes();

        $request->validate([
            'attack_type' => 'required|string|in:' . implode(',', $types),
            'target_ip'   => 'required|ip',
            'duration'    => 'required|integer|min:5|max:120',
            'intensity'   => 'required|in:low,medium,high',
        ]);

        $simulation = Simulation::create([
            'name'             => "Sim-" . $request->attack_type . "-" . now()->format('Ymd_His'),
            'attack_type'      => $request->attack_type,
            'target_ip'        => $request->target_ip,
            'duration_seconds' => $request->duration,
            'intensity'        => $request->intensity,
            'status'           => 'running',
            'started_at'       => now(),
            'log'              => "Simulation démarrée à " . now()->toDateTimeString(),
        ]);

        return response()->json([
            'success'       => true,
            'simulation_id' => $simulation->id,
            'message'       => "Simulation {$request->attack_type} lancée.",
        ]);
    }

    public function status(int $id): JsonResponse
    {
        $sim = Simulation::findOrFail($id);
        return response()->json(['simulation' => $sim]);
    }

    public function history()
    {
        return Simulation::orderByDesc('created_at')->limit(50)->get();
    }

    public function stop(int $id): JsonResponse
    {
        $sim = Simulation::findOrFail($id);
        $sim->update(['status' => 'stopped', 'completed_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function apiSimulate(Request $request): JsonResponse
    {
        $simId = $request->get('simulation_id');

        if ($simId) {
            $sim = Simulation::find($simId);
            if ($sim && $sim->status === 'running') {
                $elapsed = now()->diffInSeconds($sim->started_at);
                if ($elapsed >= $sim->duration_seconds) {
                    $sim->update(['status' => 'completed', 'completed_at' => now()]);
                    return response()->json(['status' => 'completed']);
                }
            }
        }

        $sim = $simId ? Simulation::find($simId) : null;
        $ruleId = $sim ? AttackDetectionService::ruleIdForAttackType($sim->attack_type) : null;

        if (!$ruleId) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Type de simulation non supporté dans le périmètre actuel.',
            ], 422);
        }

        $attack = AttackDetectionService::generateAttackByRule($ruleId, [
            'is_simulation' => true,
            'target_ip' => $sim->target_ip,
        ]);

        if (!$attack) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Impossible de générer l’attaque demandée.',
            ], 500);
        }

        if ($sim) {
            $packetsMap = ['low' => 100, 'medium' => 500, 'high' => 2000];
            $intensity = $sim->intensity ?? 'medium';
            $sim->increment('packets_sent', $packetsMap[$intensity] ?? 500);
        }

        return response()->json([
            'status' => 'running',
            'attack' => [
                'id'        => $attack->id,
                'type'      => $attack->type,
                'source_ip' => $attack->source_ip,
                'country'   => $attack->country,
                'city'      => $attack->city,
                'severity'  => $attack->severity,
                'packets'   => $attack->packet_count,
                'bandwidth' => $attack->bandwidth_mbps,
            ]
        ]);
    }

    public function apiFeed(): JsonResponse
    {
        $attacks = Attack::where('is_simulation', true)
            ->orderByDesc('created_at')
            ->limit(15)
            ->get()
            ->map(fn($a) => [
                'id'       => $a->id,
                'type'     => $a->type,
                'ip'       => $a->source_ip,
                'country'  => $a->country,
                'severity' => $a->severity,
                'packets'  => $a->packet_count,
                'time'     => $a->created_at->diffForHumans(),
            ]);

        return response()->json(['feed' => $attacks]);
    }
}
