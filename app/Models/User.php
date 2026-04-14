<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    /**
     * Les colonnes assignables en masse.
     */
    protected $fillable = [
        'uuid',
        'nom',
        'email',
        'password',
        'is_active'
    ];

    /**
     * Les colonnes à cacher pour les arrays/JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Les colonnes à caster en type natif.
     */
    protected $casts = [
        'uuid' => 'string',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {

            if (!$user->uuid) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    public function securitySessions()
    {
        return $this->hasMany(SecuritySession::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeWithRole($query, string $role)
    {
        return $query->whereHas('roles', fn($q) => $q->where('name', $role));
    }

    public function scopeWithAllRelations($query)
    {
        return $query->with([
            'roles',
            'roles.permissions',
        ]);
    }

    public function roles()
    {
        return $this->hasMany(UserRole::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            })
            ->exists();
    }
}
