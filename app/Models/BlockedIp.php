<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BlockedIp extends Model
{
    protected $fillable = [
        'ip_address',
        'reason',
        'attack_id',
        'blocked_until',
    ];

    protected $casts = [
        'blocked_until' => 'datetime',
    ];

    // Relation vers l'attaque
    public function attack()
    {
        return $this->belongsTo(Attack::class);
    }

    private static function cacheKey(string $ip): string
    {
        return "blocked_ip:permanent_or_until:{$ip}";
    }

    private static function cacheNotBlocked(string $ip): void
    {
        // Short negative cache to avoid hammering DB on normal traffic.
        Cache::put(self::cacheKey($ip), 0, now()->addSeconds(30));
    }

    private static function cacheBlockedUntil(string $ip, ?\Illuminate\Support\Carbon $blockedUntil): void
    {
        $key = self::cacheKey($ip);

        if ($blockedUntil === null) {
            Cache::forever($key, 'permanent');
            return;
        }

        $ttlSeconds = max(1, now()->diffInSeconds($blockedUntil, false));
        Cache::put($key, $blockedUntil->timestamp, now()->addSeconds($ttlSeconds));
    }

    public static function clearCache(string $ip): void
    {
        Cache::forget(self::cacheKey($ip));
    }

    public static function findActive(string $ip): ?self
    {
        $blocked = static::where('ip_address', $ip)->first();

        if (!$blocked) {
            return null;
        }

        if ($blocked->blocked_until === null || $blocked->blocked_until->isFuture()) {
            return $blocked;
        }

        return null;
    }

    // Vérifie si une IP est bloquée
    public static function isBlocked(string $ip): bool
    {
        $cached = Cache::get(self::cacheKey($ip));

        if ($cached === 'permanent') {
            return true;
        }

        if (is_numeric($cached)) {
            $ts = (int) $cached;
            // 0 means "not blocked" (negative cache).
            if ($ts === 0) {
                return false;
            }

            // Otherwise it's a unix timestamp.
            return $ts > now()->timestamp;
        }

        $row = static::where('ip_address', $ip)->first(['blocked_until']);
        if (!$row) {
            self::cacheNotBlocked($ip);
            return false;
        }

        if ($row->blocked_until === null) {
            self::cacheBlockedUntil($ip, null);
            return true;
        }

        if ($row->blocked_until->isFuture()) {
            self::cacheBlockedUntil($ip, $row->blocked_until);
            return true;
        }

        // Expired block: keep a short negative cache.
        self::cacheNotBlocked($ip);
        return false;
    }

    // Bloque une IP (manual ou via attaque)
    public static function blockIp(string $ip, string $reason = 'Manual block', ?int $attackId = null, ?int $minutes = null): self
    {
        $blocked = static::updateOrCreate(
            ['ip_address' => $ip],
            [
                'reason'        => $reason,
                'attack_id'     => $attackId,
                'blocked_until' => $minutes ? now()->addMinutes($minutes) : null,
            ]
        );

        // Keep the runtime cache in sync for fast middleware checks.
        self::cacheBlockedUntil($ip, $blocked->blocked_until);

        return $blocked;
    }

    public static function unblockIp(string $ip): ?self
    {
        $blocked = static::where('ip_address', $ip)->first();

        if (!$blocked) {
            self::clearCache($ip);
            return null;
        }

        $blocked->delete();
        self::clearCache($ip);

        return $blocked;
    }
}
