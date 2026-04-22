<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecuritySession extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'security_sessions';

    protected $fillable = [
        'user_id',
        'access_token_hash',
        'refresh_token_hash',
        'ip_address',
        'user_agent',
        'device_fingerprint',
        'expires_at',
        'last_activity_at',
        'is_revoked',
    ];

    protected $appends = ['ip_address'];

    public function getIpAddressAttribute(): ?string
    {
        return $this->attributes['ip_address'] ?? null;
    }

    public function setIpAddressAttribute(?string $value): void
    {
        $this->attributes['ip_address'] = $value;
    }

    protected $casts = [
        'expires_at'       => 'datetime',
        'last_activity_at' => 'datetime',
        'is_revoked'       => 'boolean',
    ];

    /**
     * Relation : une session appartient à un utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Vérifie si la session est active
     */
    public function isActive(): bool
    {
        if (!$this->last_activity_at) {
            return false;
        }

        return !$this->is_revoked
            && $this->expires_at->isFuture()
            && $this->last_activity_at->diffInMinutes(now()) < config('session.lifetime', 120);
    }

    public function revoke(): void
    {
        $this->update(['is_revoked' => true]);
    }
}
