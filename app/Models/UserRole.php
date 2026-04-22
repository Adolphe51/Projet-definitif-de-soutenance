<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserRole extends Pivot
{
    use HasFactory;

    protected $table = 'user_roles';

    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'role',
    ];

    protected $casts = [
        'role' => 'string',
    ];

    public function getNameAttribute(): string
    {
        return (string) $this->role;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permissions',
            'role',
            'permission_id',
            'role',
            'id'
        );
    }
}
