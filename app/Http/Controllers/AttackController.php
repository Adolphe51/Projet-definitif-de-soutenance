<?php

namespace App\Http\Controllers;

use App\Enums\AuditResult;
use App\Models\Attack;
use App\Models\Alert;
use App\Models\AttackComment;
use App\Models\BlockedIp;
use App\Services\AttackDetectionService;
use App\Services\AutoBlockService;
use App\Services\Audit\AuditServiceWrapper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttackController extends Controller
{
    public function index()
    {
        $attacks = Attack::select([
            'id',
            'type',
            'source_ip',
            'target_ip',
            'target_port',
            'severity',
            'status',
            'country',
            'city',
            'isp',
            'packet_count',
            'bandwidth_mbps',
            'is_simulation',
            'created_at',
        ])
            ->orderByDesc('created_at')
            ->paginate(20);

        $severityCounts = Attack::select('severity', DB::raw('COUNT(*) as cnt'))
            ->groupBy('severity')
            ->pluck('cnt', 'severity')
            ->all();

        $types = Attack::attackTypes();
        return view('attacks.index', compact('attacks', 'types', 'severityCounts'));
    }

    public function live()
    {
        return view('attacks.live');
    }

    public function apiLive(): JsonResponse
    {
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
                'source_scope' => $a->resolveSourceScope(),
                'source_channel' => $a->resolveSourceChannel(),
                'source_label' => $a->resolveSourceLabel(),
                'is_geolocatable' => $a->isGeolocatable(),
                'color' => $a->severity_color,
                'icon' => $a->type_icon,
                'time' => $a->created_at->diffForHumans(),
                'timestamp' => $a->created_at->toISOString(),
            ];
        });

        $totals = Cache::remember('attacks.live.totals', now()->addSeconds(2), function () {
            return Attack::selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN severity = "critical" THEN 1 ELSE 0 END) as critical')
                ->first();
        });

        return response()->json([
            'attacks' => $attacks,
            'new_attack' => null,
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
        $with = ['rule'];

        if ($this->attackCommentsEnabled()) {
            $with[] = 'comments.user';
        }

        $attack = Attack::with($with)->findOrFail($id);

        if (!$this->attackCommentsEnabled()) {
            $attack->setRelation('comments', collect());
        }

        $relatedAttacks = $attack->incident_id ? $attack->correlatedAttacks()->limit(12)->get() : collect();

        return view('attacks.show', compact('attack', 'relatedAttacks'));
    }

    public function updateStatus(int $id, Request $request): JsonResponse
    {
        $attack = Attack::findOrFail($id);

        $data = $request->validate([
            'status' => ['required', 'in:investigating,blocked,false_positive,resolved'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $result = $this->applyStatusChange($attack, $data['status'], $data['comment'] ?? null);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'status' => $attack->status,
            'already' => $result['already'],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        Attack::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function block(int $id): JsonResponse
    {
        $attack = Attack::findOrFail($id);

        if (BlockedIp::isBlocked($attack->source_ip)) {
            $this->synchronizeBlockedStatus($attack);
            $this->acknowledgeRelatedAlerts($attack);

            return response()->json([
                'success' => true,
                'already' => true,
                'message' => "IP {$attack->source_ip} est déjà bloquée.",
            ]);
        }

        $this->applyStatusChange($attack, 'blocked', 'Blocage manuel initié depuis la page d’attaque.');

        return response()->json(['success' => true, 'message' => "IP {$attack->source_ip} bloquée."]);
    }

    public function unblock(int $id, Request $request, AutoBlockService $autoBlockService): JsonResponse
    {
        $attack = Attack::findOrFail($id);
        $data = $request->validate([
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $unblocked = $autoBlockService->unblockIp(
            $attack->source_ip,
            $attack,
            auth()->user(),
            $data['comment'] ?? null
        );

        if (!$unblocked) {
            $this->acknowledgeRelatedAlerts($attack);

            return response()->json([
                'success' => true,
                'already' => true,
                'message' => "IP {$attack->source_ip} est déjà débloquée.",
                'status' => $attack->status,
            ]);
        }

        $this->acknowledgeRelatedAlerts($attack);

        return response()->json([
            'success' => true,
            'message' => "IP {$attack->source_ip} débloquée.",
            'status' => 'investigating',
        ]);
    }

    public function triggerAlarm(int $id): JsonResponse
    {
        $attack = Attack::findOrFail($id);

        if ($attack->alarm_triggered) {
            return response()->json([
                'success' => true,
                'already' => true,
                'message' => "L'alarme pour {$attack->source_ip} est déjà active.",
            ]);
        }

        $attack->forceFill(['alarm_triggered' => true])->save();

        Alert::create([
            'attack_id' => $attack->id,
            'title' => "🔔 Alarme déclenchée: {$attack->source_ip}",
            'message' => "Activation manuelle de l’alarme pour l’incident #{$attack->id}.",
            'severity' => in_array($attack->severity, ['critical', 'high'], true) ? $attack->severity : 'medium',
            'type' => 'system',
            'acknowledged' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Alarme déclenchée pour {$attack->source_ip}.",
        ]);
    }

    private function applyStatusChange(Attack $attack, string $status, ?string $comment = null): array
    {
        $oldStatus = $attack->status;
        $comment = $comment !== null ? trim($comment) : null;

        if ($oldStatus === $status) {
            if ($comment !== null && $comment !== '' && $this->attackCommentsEnabled()) {
                AttackComment::create([
                    'attack_id' => $attack->id,
                    'user_id' => auth()->id(),
                    'status' => $status,
                    'comment' => $comment,
                ]);
            }

            $this->acknowledgeRelatedAlerts($attack);

            return [
                'already' => true,
                'message' => $comment ? 'Commentaire enregistré sans changement de statut.' : "Le statut est déjà {$status}.",
            ];
        }

        $attack->status = $status;
        $attack->save();

        if ($this->attackCommentsEnabled()) {
            AttackComment::create([
                'attack_id' => $attack->id,
                'user_id' => auth()->id(),
                'status' => $status,
                'comment' => $comment,
            ]);
        }

        if ($status === 'blocked') {
            if (!BlockedIp::isBlocked($attack->source_ip)) {
                BlockedIp::blockIp(
                    $attack->source_ip,
                    "Blocage SOC pour Attack #{$attack->id}",
                    $attack->id
                );
            }

            $this->synchronizeBlockedStatus($attack);
        }

        $this->acknowledgeRelatedAlerts($attack);

        Alert::create([
            'attack_id' => $attack->id,
            'title' => match ($status) {
                'blocked' => "🛡️ IP bloquée: {$attack->source_ip}",
                'investigating' => "🔎 Attaque en investigation",
                'false_positive' => "✅ Attaque déclarée faux positif",
                'resolved' => "✅ Incident résolu",
                default => "⚠️ Statut d'attaque mis à jour",
            },
            'message' => $comment ?? "Statut changé de {$oldStatus} à {$status}.",
            'severity' => in_array($status, ['investigating', 'resolved', 'false_positive']) ? 'low' : 'medium',
            'type' => 'system',
            'acknowledged' => true,
        ]);

        AuditServiceWrapper::logElevee(
            'attack.status.changed',
            'Attack',
            "Attack #{$attack->id}",
            AuditResult::Autorise,
            [
                'user' => auth()->user(),
                'oldValues' => ['status' => $oldStatus],
                'newValues' => ['status' => $status],
                'entityId' => $attack->id,
                'metadata' => ['comment' => $comment, 'source_ip' => $attack->source_ip],
            ]
        );

        return [
            'already' => false,
            'message' => "Statut mis à jour en {$status}.",
        ];
    }

    private function acknowledgeRelatedAlerts(Attack $attack): void
    {
        Alert::where('attack_id', $attack->id)
            ->where('acknowledged', false)
            ->update(['acknowledged' => true]);

        Alert::clearUnreadCountCache();
    }

    private function synchronizeBlockedStatus(Attack $attack): void
    {
        Attack::where('source_ip', $attack->source_ip)
            ->where('status', '!=', 'blocked')
            ->update(['status' => 'blocked']);
    }

    private function attackCommentsEnabled(): bool
    {
        return Schema::hasTable('attack_comments');
    }
}
