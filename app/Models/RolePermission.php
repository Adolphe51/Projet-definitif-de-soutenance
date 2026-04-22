<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RolePermission extends Pivot
{
    use HasFactory;
    protected $table = 'role_permissions';

    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'role',
        'permission_id'
    ];

    protected $casts = [
        'role' => 'string',
    ];

    // Une permission peut être attribuée à plusieurs rôles
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }
}
