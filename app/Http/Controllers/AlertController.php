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

    public function unread(): JsonResponse
    {
        $alerts = Alert::where('acknowledged', false)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
        return response()->json(['alerts' => $alerts, 'count' => $alerts->count()]);
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

    public function stream(Request $request)
    {
        // SSE endpoint pour les alertes temps réel
        return response()->stream(function () {
            $lastId = 0;
            $iterations = 0;
            while ($iterations < 30) {
                $alerts = Alert::where('id', '>', $lastId)
                    ->orderBy('id')
                    ->limit(5)
                    ->get();

                foreach ($alerts as $alert) {
                    echo "data: " . json_encode($alert) . "\n\n";
                    $lastId = $alert->id;
                }

                ob_flush();
                flush();
                sleep(2);
                $iterations++;
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
