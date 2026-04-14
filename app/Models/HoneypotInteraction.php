<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HoneypotInteraction extends Model
{
    protected $fillable = [
        'honeypot_trap_id',
        'source_ip',
        'country',
        'city',
        'latitude',
        'longitude',
        'isp',
        'method',
        'path',
        'user_agent',
        'payload',
        'credentials_attempted',
        'session_duration',
        'actions_taken',
        'risk_score',
    ];

    protected $casts = [
        'actions_taken'         => 'array',
        'credentials_attempted' => 'array',
        'latitude'              => 'float',
        'longitude'             => 'float',
    ];

    public function trap()
    {
        return $this->belongsTo(HoneypotTrap::class);
    }
}
