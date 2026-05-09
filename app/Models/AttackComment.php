<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttackComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'attack_id',
        'user_id',
        'status',
        'comment',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    public function attack()
    {
        return $this->belongsTo(Attack::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
