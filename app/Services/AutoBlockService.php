<?php

namespace App\Services;

use App\Enums\AuditResult;
use App\Models\Alert;
use App\Models\Attack;
use App\Models\AttackComment;
use App\Models\BlockedIp;
use App\Models\SecuritySession;
use App\Models\User;
use App\Services\Audit\AuditServiceWrapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AutoBlockService
{
    public function isEnabled(): bool
    {
        return (bool) config('cyberguard.detection.auto_block.enabled', true);
    }

    public function isAllowlisted(string $ip): bool
    {
        $allowlist = config('cyberguard.detection.auto_block.allowlist', ['127.0.0.1', '::1']);

        return in_array($ip, $allowlist, true);
    }

    public function evaluateAttack(Attack $attack, string $source = 'detection', array $overrides = []): ?BlockedIp
    {
        if (!$this->isEnabled()) {
            return null;
        }

        if ($attack->is_simulation && !config('cyberguard.detection.auto_block.apply_to_simulations', false)) {
            return null;
        }

        if ($this->isAllowlisted($attack->source_ip) || BlockedIp::isBlocked($attack->source_ip)) {
            return null;
        }

        $policy = $this->resolvePolicyForAttack($attack, $overrides);
        $threshold = max(1, (int) ($policy['threshold_count'] ?? 1));
        $count = $this->countMatchingAttacks($attack, (int) ($policy['window_minutes'] ?? 10));

        if ($count < $threshold) {
            return null;
        }

        return $this->autoBlockAttack($attack, $policy, $count, $source);
    }

    public function autoBlockHoneypotIp(string $ip, int $riskScore, string $path, ?int $trapId = null): ?BlockedIp
    {
        if (!$this->isEnabled() || $this->isAllowlisted($ip) || BlockedIp::isBlocked($ip)) {
            return null;
        }

        $policy = config('cyberguard.detection.auto_block.honeypot', []);
        $threshold = (int) ($policy['risk_score_threshold'] ?? 95);

        if ($riskScore < $threshold) {
            return null;
        }

        $minutes = ($policy['permanent'] ?? false) ? null : (int) ($policy['block_minutes'] ?? config('cyberguard.detection.auto_block.default_block_minutes', 60));
        $reason = "Auto-bloqué par le honeypot (score {$riskScore}/100) sur {$path}";

        $blocked = BlockedIp::blockIp($ip, $reason, null, $minutes);

        Alert::create([
            'title' => "🍯 AUTO-BLOCAGE HONEYPOT: {$ip}",
            'message' => $reason,
            'severity' => $riskScore >= 99 ? 'critical' : 'high',
            'type' => 'honeypot',
        ]);

        AuditServiceWrapper::logCritique(
            'blocked_ip.autoblock.honeypot',
            'BlockedIp',
            "IP {$ip}",
            AuditResult::Autorise,
            [
                'entityId' => $blocked->id,
                'newValues' => [
                    'ip_address' => $ip,
                    'blocked_until' => optional($blocked->blocked_until)?->toISOString(),
                ],
                'metadata' => [
                    'reason' => $reason,
                    'path' => $path,
                    'risk_score' => $riskScore,
                    'honeypot_trap_id' => $trapId,
                ],
            ]
        );

        return $blocked;
    }

    public function unblockIp(string $ip, ?Attack $attack = null, ?User $actor = null, ?string $comment = null): bool
    {
        $existing = BlockedIp::findActive($ip);

        if (!$existing) {
            return false;
        }

        $oldValues = [
            'ip_address' => $existing->ip_address,
            'reason' => $existing->reason,
            'attack_id' => $existing->attack_id,
            'blocked_until' => optional($existing->blocked_until)?->toISOString(),
        ];

        BlockedIp::unblockIp($ip);

        Attack::where('source_ip', $ip)
            ->where('status', 'blocked')
            ->update(['status' => 'investigating']);

        if ($attack && Schema::hasTable('attack_comments')) {
            AttackComment::create([
                'attack_id' => $attack->id,
                'user_id' => $actor?->id,
                'status' => 'investigating',
                'comment' => $comment ?: "Déblocage manuel de l'IP {$ip}.",
            ]);
        }

        Alert::create([
            'attack_id' => $attack?->id,
            'title' => "🔓 IP débloquée: {$ip}",
            'message' => $comment ?: "Déblocage manuel tracé pour {$ip}.",
            'severity' => 'low',
            'type' => 'system',
            'acknowledged' => true,
        ]);

        AuditServiceWrapper::logElevee(
            'blocked_ip.unblocked',
            'BlockedIp',
            "IP {$ip}",
            AuditResult::Autorise,
            [
                'user' => $actor,
                'entityId' => $existing->id,
                'oldValues' => $oldValues,
                'newValues' => [
                    'ip_address' => $ip,
                    'status' => 'unblocked',
                ],
                'metadata' => [
                    'attack_id' => $attack?->id,
                    'comment' => $comment,
                ],
            ]
        );

        return true;
    }

    public function canBypassBlockedIpForAdmin(Request $request): bool
    {
        $path = ltrim($request->path(), '/');
        $allowedPrefixes = config('cyberguard.detection.auto_block.admin_route_prefixes', []);

        $matchesProtectedPath = collect($allowedPrefixes)->contains(
            fn(string $prefix) => str_starts_with($path, ltrim($prefix, '/'))
        );

        if (!$matchesProtectedPath) {
            return false;
        }

        $ip = $request->ip() ?? '127.0.0.1';
        $trustedAdminIps = config('cyberguard.detection.auto_block.trusted_admin_ips', ['127.0.0.1', '::1']);

        if (in_array($ip, $trustedAdminIps, true)) {
            return true;
        }

        $token = $request->bearerToken() ?? $request->cookie('access_token');
        if (!$token) {
            return false;
        }

        $session = SecuritySession::where('access_token_hash', hash('sha256', $token))
            ->with('user.roles')
            ->first();

        if (!$session || $session->is_revoked || $session->expires_at < now()) {
            return false;
        }

        $fingerprint = hash('sha256', implode('|', [
            $request->ip(),
            $request->userAgent(),
            $request->header('Accept-Language'),
        ]));

        return $session->device_fingerprint === $fingerprint
            && $session->user?->is_active
            && $session->user->hasRole('admin');
    }

    private function autoBlockAttack(Attack $attack, array $policy, int $count, string $source): BlockedIp
    {
        $blockMinutes = ($policy['permanent'] ?? false) ? null : (int) ($policy['block_minutes'] ?? config('cyberguard.detection.auto_block.default_block_minutes', 60));
        $windowMinutes = (int) ($policy['window_minutes'] ?? config('cyberguard.detection.auto_block.default_window_minutes', 10));
        $label = $blockMinutes === null ? 'permanent' : "temporaire ({$blockMinutes} min)";
        $reason = "Auto-bloqué ({$attack->type}) : {$count} événements en {$windowMinutes} min, blocage {$label}.";

        $blocked = BlockedIp::blockIp($attack->source_ip, $reason, $attack->id, $blockMinutes);

        Attack::where('source_ip', $attack->source_ip)
            ->whereNotIn('status', ['blocked', 'false_positive', 'resolved'])
            ->update(['status' => 'blocked']);

        Alert::create([
            'attack_id' => $attack->id,
            'title' => "🤖 AUTO-BLOCAGE: {$attack->source_ip}",
            'message' => $reason,
            'severity' => $blockMinutes === null ? 'critical' : 'high',
            'type' => 'system',
        ]);

        AuditServiceWrapper::logCritique(
            'blocked_ip.autoblock',
            'BlockedIp',
            "IP {$attack->source_ip}",
            AuditResult::Autorise,
            [
                'entityId' => $blocked->id,
                'newValues' => [
                    'ip_address' => $attack->source_ip,
                    'attack_id' => $attack->id,
                    'blocked_until' => optional($blocked->blocked_until)?->toISOString(),
                ],
                'metadata' => [
                    'attack_type' => $attack->type,
                    'rule_id' => $attack->rule_id,
                    'severity' => $attack->severity,
                    'count' => $count,
                    'window_minutes' => $windowMinutes,
                    'source' => $source,
                    'allowlisted' => false,
                ],
            ]
        );

        return $blocked;
    }

    private function countMatchingAttacks(Attack $attack, int $windowMinutes): int
    {
        return Attack::query()
            ->where('source_ip', $attack->source_ip)
            ->where('created_at', '>=', now()->subMinutes($windowMinutes))
            ->where('status', '!=', 'false_positive')
            ->when(
                $attack->rule_id,
                fn($query) => $query->where('rule_id', $attack->rule_id),
                fn($query) => $query->where('type', $attack->type)
            )
            ->count();
    }

    private function resolvePolicyForAttack(Attack $attack, array $overrides = []): array
    {
        $defaults = [
            'threshold_count' => (int) config('cyberguard.detection.auto_block.default_threshold_count', 5),
            'window_minutes' => (int) config('cyberguard.detection.auto_block.default_window_minutes', 10),
            'block_minutes' => (int) config('cyberguard.detection.auto_block.default_block_minutes', 60),
            'permanent' => in_array($attack->severity, config('cyberguard.detection.auto_block.permanent_severities', ['critical']), true),
        ];

        $typePolicy = config("cyberguard.detection.auto_block.per_type.{$attack->type}", []);
        $rulePolicy = $attack->rule_id
            ? config("cyberguard.detection.auto_block.per_rule.{$attack->rule_id}", [])
            : [];

        return array_merge($defaults, $typePolicy, $rulePolicy, $overrides);
    }
}
