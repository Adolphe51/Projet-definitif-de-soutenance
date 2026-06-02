<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index()
    {
        $alerts = Alert::with(['attack.rule'])->orderByDesc('created_at')->paginate(30);
        $summary = [
            'unread' => Alert::where('acknowledged', false)->count(),
            'critical' => Alert::where('severity', 'critical')->count(),
            'high' => Alert::where('severity', 'high')->count(),
            'total' => Alert::count(),
            'latestAt' => Alert::latest('created_at')->value('created_at'),
            'authAudit24h' => AuditLog::whereIn('action', ['login_success', 'login_failed', 'otp_verified', 'otp_failed'])
                ->where('created_at', '>=', now()->subDay())
                ->count(),
            'intranetAudit24h' => AuditLog::where('action', 'like', 'intranet_%')
                ->where('created_at', '>=', now()->subDay())
                ->count(),
            'manualSimulations' => Alert::where('type', 'simulation')->count(),
            'attackAlerts' => Alert::where('type', 'attack')->count(),
        ];

        $recentAuditTrail = AuditLog::with('actor:id,nom,email')
            ->where(function ($query) {
                $query->whereIn('action', ['login_success', 'login_failed', 'otp_verified', 'otp_failed'])
                    ->orWhere('action', 'like', 'intranet_%')
                    ->orWhere('action', 'like', 'attack.%')
                    ->orWhere('action', 'like', 'blocked_ip.%');
            })
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        return view('alerts.index', compact('alerts', 'summary', 'recentAuditTrail'));
    }

    public function unread(Request $request): JsonResponse
    {
        $afterId = max((int) $request->query('after_id', 0), 0);

        $baseQuery = Alert::query()->where('acknowledged', false);
        $count = (clone $baseQuery)->count();

        $alertsQuery = Alert::with(['attack.rule'])
            ->where('acknowledged', false)
            ->limit(10);

        if ($afterId > 0) {
            $alertsQuery
                ->where('id', '>', $afterId)
                ->orderBy('id');
        } else {
            $alertsQuery->orderByDesc('created_at');
        }

        $alerts = $alertsQuery->get();

        return response()->json([
            'alerts' => $alerts->map(fn (Alert $alert) => $this->formatAlertPayload($alert))->values(),
            'count' => $count,
        ]);
    }

    public function acknowledge(int $id): JsonResponse
    {
        Alert::findOrFail($id)->update(['acknowledged' => true]);
        Alert::clearUnreadCountCache();
        return response()->json(['success' => true]);
    }

    public function clearAll(): JsonResponse
    {
        Alert::where('acknowledged', false)->update(['acknowledged' => true]);
        Alert::clearUnreadCountCache();
        return response()->json(['success' => true]);
    }

    public function apiCount(): JsonResponse
    {
        $counts = Alert::selectRaw('SUM(CASE WHEN acknowledged = false THEN 1 ELSE 0 END) as count')
            ->selectRaw('SUM(CASE WHEN acknowledged = false AND severity = "critical" THEN 1 ELSE 0 END) as critical')
            ->first();

        return response()->json([
            'count' => (int) $counts->count,
            'critical' => (int) $counts->critical,
        ]);
    }

    public function stream(Request $request): JsonResponse
    {
        return response()->json([
            'available' => false,
            'message' => 'Flux SSE desactive pour privilegier la stabilite en environnement local.',
        ]);
    }

    private function formatAlertPayload(Alert $alert): array
    {
        $attack = $alert->attack;

        $originLabel = match (true) {
            $alert->type === 'simulation' => 'Simulation manuelle',
            $alert->type === 'honeypot' => 'Honeypot secondaire',
            $alert->type === 'attack' && $attack?->is_simulation => 'Attaque simulée',
            $alert->type === 'attack' => 'Détection sécurité',
            $alert->type === 'system' => 'Traitement SOC',
            default => 'Événement sécurité',
        };

        return [
            'id' => $alert->id,
            'attack_id' => $alert->attack_id,
            'title' => $alert->title,
            'message' => $alert->message,
            'severity' => $alert->severity,
            'type' => $alert->type,
            'origin_label' => $originLabel,
            'acknowledged' => (bool) $alert->acknowledged,
            'created_at' => $alert->created_at?->toISOString(),
            'created_at_human' => $alert->created_at?->diffForHumans(),
            'created_at_label' => $alert->created_at?->format('d/m/Y H:i'),
            'attack_url' => $alert->attack_id ? route('attacks.show', $alert->attack_id) : null,
            'attack' => $attack ? [
                'id' => $attack->id,
                'type' => $attack->type,
                'severity' => $attack->severity,
                'source_ip' => $attack->source_ip,
                'target_ip' => $attack->target_ip,
                'is_simulation' => (bool) $attack->is_simulation,
                'rule_name' => $attack->rule?->name,
            ] : null,
        ];
    }
}
