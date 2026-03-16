<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Simulation extends Model
{
    protected $fillable = [
        'name', 'attack_type', 'target_ip', 'duration_seconds',
        'intensity', 'status', 'packets_sent', 'log',
        'started_at', 'completed_at'
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'completed_at' => 'datetime',
    ];

    public static function attackTypes(): array
    {
        return Attack::attackTypes();
    }
}
