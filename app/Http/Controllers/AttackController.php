<?php

namespace App\Http\Controllers;

use App\Models\Attack;
use App\Models\Alert;
use App\Services\AttackDetectionService;
use App\Services\GeoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AttackController extends Controller
{
    public function index()
    {
        $attacks = Attack::orderByDesc('created_at')->paginate(20);
        $types = Attack::attackTypes();
        return view('attacks.index', compact('attacks', 'types'));
    }

    public function live()
    {
        return view('attacks.live');
    }

    public function apiLive(): JsonResponse
    {
        // Génère une nouvelle attaque aléatoire si le timing s'y prête
        $newAttack = null;
        if (rand(1, 100) <= 40) {
            $newAttack = AttackDetectionService::generateAttack(false);
        }

        $attacks = Attack::select([
            'id',
            'type',
            'source_ip',
            'target_ip',
            'severity',
            'status',
            'country',
            'city',
            'latitude',
            'longitude',
            'packet_count',
            'bandwidth_mbps',
            'description',
            'alarm_triggered',
            'is_simulation',
            'created_at',
        ])->orderByDesc('created_at')->limit(20)->get()->map(function ($a) {
            return [
                'id' => $a->id,
                'type' => $a->type,
                'source_ip' => $a->source_ip,
                'target_ip' => $a->target_ip,
                'severity' => $a->severity,
                'status' => $a->status,
                'country' => $a->country,
                'city' => $a->city,
                'latitude' => $a->latitude,
                'longitude' => $a->longitude,
                'packet_count' => $a->packet_count,
                'bandwidth_mbps' => $a->bandwidth_mbps,
                'description' => $a->description,
                'alarm' => $a->alarm_triggered,
                'is_simulation' => $a->is_simulation,
                'color' => $a->severity_color,
                'icon' => $a->type_icon,
                'time' => $a->created_at->diffForHumans(),
                'timestamp' => $a->created_at->toISOString(),
            ];
        });

        $totals = Attack::selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN severity = "critical" THEN 1 ELSE 0 END) as critical')
            ->first();

        return response()->json([
            'attacks' => $attacks,
            'new_attack' => $newAttack ? [
                'id' => $newAttack->id,
                'type' => $newAttack->type,
                'severity' => $newAttack->severity,
                'ip' => $newAttack->source_ip,
                'country' => $newAttack->country,
                'alarm' => $newAttack->alarm_triggered,
            ] : null,
            'total' => $totals->total,
            'critical' => $totals->critical,
        ]);
    }

    public function detect(Request $request): JsonResponse
    {
        $attack = AttackDetectionService::generateAttack(false);
        return response()->json(['success' => true, 'attack' => $attack]);
    }

    public function show(int $id)
    {
        $attack = Attack::findOrFail($id);
        $geo = GeoService::lookup($attack->source_ip);
        return view('attacks.show', compact('attack', 'geo'));
    }

    public function destroy(int $id): JsonResponse
    {
        Attack::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function block(int $id): JsonResponse
    {
        $attack = Attack::findOrFail($id);
        $attack->update(['status' => 'blocked']);

        Alert::create([
            'attack_id' => $attack->id,
            'title' => "🛡️ IP Bloquée: {$attack->source_ip}",
            'message' => "L'adresse IP {$attack->source_ip} ({$attack->country}) a été bloquée avec succès.",
            'severity' => 'low',
            'type' => 'system',
        ]);

        return response()->json(['success' => true, 'message' => "IP {$attack->source_ip} bloquée."]);
    }
}
