<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;
    protected $table = 'permissions';

    protected $fillable = [
        'nom',
        'description',
        'ressourceType'
    ];

    // Une permission peut être associée à plusieurs rôles
    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class);
    }
}
