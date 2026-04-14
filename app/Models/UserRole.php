<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
