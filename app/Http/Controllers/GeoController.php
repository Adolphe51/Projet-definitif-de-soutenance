<?php

namespace App\Http\Controllers;

use App\Models\Attack;
use App\Models\BlockedIp;
use App\Services\GeoService;
use Illuminate\Http\JsonResponse;

class GeoController extends Controller
{
    public function attackers()
    {
        $attackers = Attack::select('source_ip', 'country', 'city', 'latitude', 'longitude', 'severity')
            ->distinct('source_ip')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return view('attacks.map', compact('attackers'));
    }

    public function trace(string $ip)
    {
        $attack = Attack::where('source_ip', $ip)->latest()->first();
        $geo = $attack
            ? [
                'country' => $attack->country ?: 'Inconnu',
                'city' => $attack->city ?: 'Inconnue',
                'lat' => $attack->latitude,
                'lon' => $attack->longitude,
                'isp' => $attack->isp ?: 'Inconnu',
            ]
            : GeoService::lookup($ip);
        $isBlocked = BlockedIp::isBlocked($ip) || $attack?->status === 'blocked';

        return view('attacks.trace', compact('ip', 'geo', 'attack', 'isBlocked'));
    }

    public function mapData(): JsonResponse
    {
        return response()->json($this->buildMapData());
    }

    public function apiGeoData(): JsonResponse
    {
        return response()->json($this->buildMapData());
    }

    private function buildMapData(): array
    {
        $attacks = Attack::select([
            'id',
            'source_ip',
            'type',
            'severity',
            'country',
            'city',
            'latitude',
            'longitude',
            'status',
            'created_at',
        ])
            ->orderByDesc('created_at')
            ->limit(200)
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'ip' => $a->source_ip,
                'type' => $a->type,
                'severity' => $a->severity,
                'country' => $a->country,
                'city' => $a->city,
                'lat' => $a->latitude,
                'lon' => $a->longitude,
                'color' => $a->severity_color,
                'status' => BlockedIp::isBlocked($a->source_ip) ? 'blocked' : $a->status,
                'alarm_triggered' => (bool) $a->alarm_triggered,
                'time' => $a->created_at?->diffForHumans(),
            ]);

        // Cible principale (notre serveur)
        $target = ['lat' => 6.3654, 'lon' => 2.4183, 'city' => 'Cotonou', 'country' => 'Bénin'];

        return [
            'attacks' => $attacks,
            'target' => $target,
            'stats' => [
                'total' => $attacks->count(),
                'critical' => $attacks->where('severity', 'critical')->count(),
                'blocked' => $attacks->where('status', 'blocked')->count(),
            ]
        ];
    }
}
