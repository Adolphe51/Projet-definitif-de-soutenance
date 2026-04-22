<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AuthCode extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'code_hash',
        'expires_at',
        'email',
        'attempts',
        'used_at',
        'ip_address'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function markAsUsed(): void
    {
        $this->update([
            'used_at' => now()
        ]);
    }

    /**
     * Vérifie si le nombre maximal de tentatives a été dépassé.
     * 🔐 CORRECTION : Limite à 3 tentatives (au lieu de 5)
     */
    public function hasExceededAttempts(?int $max = null): bool
    {
        $max ??= (int) config('cyberguard.auth.otp.max_attempts', 3);

        return $this->attempts >= $max;
    }

    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }
}
