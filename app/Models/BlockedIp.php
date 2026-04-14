<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    // Vérifie si une IP est bloquée
    public static function isBlocked(string $ip): bool
    {
        return static::where('ip_address', $ip)
            ->where(function ($q) {
                $q->whereNull('blocked_until')
                    ->orWhere('blocked_until', '>', now());
            })
            ->exists();
    }

    // Bloque une IP (manual ou via attaque)
    public static function blockIp(string $ip, string $reason = 'Manual block', ?int $attackId = null, ?int $minutes = null): self
    {
        return static::updateOrCreate(
            ['ip_address' => $ip],
            [
                'reason'        => $reason,
                'attack_id'     => $attackId,
                'blocked_until' => $minutes ? now()->addMinutes($minutes) : null,
            ]
        );
    }
}
