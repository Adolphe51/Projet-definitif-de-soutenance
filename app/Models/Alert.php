<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = [
        'attack_id', 'title', 'message', 'severity', 'type',
        'acknowledged', 'sound_played'
    ];

    protected $casts = [
        'acknowledged' => 'boolean',
        'sound_played' => 'boolean',
    ];

    public function attack()
    {
        return $this->belongsTo(Attack::class);
    }
}
