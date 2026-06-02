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
        $attackers = Attack::select('source_ip', 'country', 'city', 'latitude', 'longitude', 'severity', 'source_scope', 'source_channel', 'source_label', 'is_geolocatable')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->filter(fn (Attack $attack) => $attack->isGeolocatable())
            ->unique('source_ip')
            ->values();

        return view('attacks.map', compact('attackers'));
    }

    public function trace(string $ip)
    {
        $attack = Attack::where('source_ip', $ip)->latest()->first();
        $sourceScope = $attack?->resolveSourceScope() ?? (\App\Services\AttackDetectionService::isPrivateOrReservedIp($ip) ? 'internal' : 'external');
        $sourceChannel = $attack?->resolveSourceChannel() ?? ($sourceScope === 'internal' ? 'intranet' : 'network');
        $sourceLabel = $attack?->resolveSourceLabel() ?? ($sourceScope === 'internal' ? 'Application metier' : 'Trafic reseau');
        $isGeolocatable = $attack?->isGeolocatable() ?? false;
        $geo = $attack
            ? [
                'country' => $attack->country ?: 'Inconnu',
                'city' => $attack->city ?: 'Inconnue',
                'lat' => $attack->latitude,
                'lon' => $attack->longitude,
                'isp' => $attack->isp ?: 'Inconnu',
            ]
            : GeoService::lookup($ip);

        if ($sourceScope === 'internal') {
            $geo = [
                'country' => 'Reseau local',
                'city' => 'Segment interne',
                'lat' => null,
                'lon' => null,
                'isp' => "Adresse privee ({$ip})",
            ];
        }

        $isBlocked = BlockedIp::isBlocked($ip) || $attack?->status === 'blocked';
        $recentActivities = Attack::where('source_ip', $ip)->latest()->limit(5)->get();
        $traceHops = $sourceScope === 'internal'
            ? ['poste.local', 'switch-lan-01', 'gateway-intranet', $ip]
            : ['pare-feu-local', 'sortie-reseau', preg_replace('/\.\d+$/', '.1', $ip), $ip];

        return view('attacks.trace', compact(
            'ip',
            'geo',
            'attack',
            'isBlocked',
            'sourceScope',
            'sourceChannel',
            'sourceLabel',
            'isGeolocatable',
            'recentActivities',
            'traceHops'
        ));
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
        $recentAttacks = Attack::select([
            'id',
            'source_ip',
            'type',
            'severity',
            'source_scope',
            'source_channel',
            'source_label',
            'is_geolocatable',
            'country',
            'city',
            'latitude',
            'longitude',
            'status',
            'created_at',
        ])
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        $internalAttacks = $recentAttacks
            ->filter(fn (Attack $attack) => $attack->resolveSourceScope() === 'internal')
            ->values();

        $attacks = $recentAttacks
            ->filter(fn (Attack $attack) => $attack->isGeolocatable())
            ->map(fn($a) => [
                'id' => $a->id,
                'ip' => $a->source_ip,
                'type' => $a->type,
                'severity' => $a->severity,
                'source_scope' => $a->resolveSourceScope(),
                'source_channel' => $a->resolveSourceChannel(),
                'source_label' => $a->resolveSourceLabel(),
                'country' => $a->country,
                'city' => $a->city,
                'lat' => $a->latitude,
                'lon' => $a->longitude,
                'color' => $a->severity_color,
                'status' => BlockedIp::isBlocked($a->source_ip) ? 'blocked' : $a->status,
                'alarm_triggered' => (bool) $a->alarm_triggered,
                'time' => $a->created_at?->diffForHumans(),
            ])
            ->values();

        $internalSummary = $internalAttacks
            ->groupBy(fn (Attack $attack) => $attack->resolveSourceLabel() . '|' . $attack->source_ip)
            ->map(function ($group) {
                /** @var Attack $latest */
                $latest = $group->sortByDesc('created_at')->first();
                $severityOrder = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];
                $highestSeverity = $group->sortByDesc(fn (Attack $attack) => $severityOrder[$attack->severity] ?? 0)->first()?->severity ?? 'low';

                return [
                    'ip' => $latest->source_ip,
                    'label' => $latest->resolveSourceLabel(),
                    'channel' => $latest->resolveSourceChannel(),
                    'count' => $group->count(),
                    'severity' => $highestSeverity,
                    'time' => $latest->created_at?->diffForHumans(),
                ];
            })
            ->sortByDesc('count')
            ->take(10)
            ->values();

        // Cible principale (notre serveur)
        $target = ['lat' => 6.3654, 'lon' => 2.4183, 'city' => 'Cotonou', 'country' => 'Bénin'];

        return [
            'attacks' => $attacks,
            'target' => $target,
            'stats' => [
                'total' => $attacks->count(),
                'critical' => $attacks->where('severity', 'critical')->count(),
                'blocked' => $attacks->where('status', 'blocked')->count(),
                'internal' => $internalAttacks->count(),
            ],
            'internal_summary' => $internalSummary,
        ];
    }
}
