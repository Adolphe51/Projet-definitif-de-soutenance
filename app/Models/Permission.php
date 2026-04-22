<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permission extends Model
{
    use HasFactory;
    protected $table = 'permissions';

    protected $fillable = [
        'nom',
        'description',
        'ressourceType'
    ];

    public function getNameAttribute(): string
    {
        return (string) $this->attributes['nom'];
    }

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['nom'] = $value;
    }

    // Une permission peut être associée à plusieurs rôles
    public function rolePermissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }
}
