<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HoneypotTrap extends Model
{
    protected $fillable = [
        'name', 'type', 'fake_service', 'port', 'path', 'status',
        'description', 'lure_content', 'interactions_count',
        'last_triggered_at', 'config',
    ];

    protected $casts = [
        'config'            => 'array',
        'last_triggered_at' => 'datetime',
    ];

    public static function trapTypes(): array
    {
        return [
            'fake_login'    => 'Faux Portail de Connexion',
            'fake_admin'    => 'Faux Panel Admin',
            'fake_db'       => 'Fausse Base de Données',
            'fake_api'      => 'Fausse API REST',
            'fake_ssh'      => 'Faux Serveur SSH',
            'fake_ftp'      => 'Faux Serveur FTP',
            'fake_phpmyadmin' => 'Faux phpMyAdmin',
            'fake_wordpress' => 'Faux WordPress Admin',
            'canary_token'  => 'Token Canary (Piège URL)',
            'fake_document' => 'Faux Document Sensible',
        ];
    }
}

class HoneypotInteraction extends Model
{
    protected $fillable = [
        'honeypot_trap_id', 'source_ip', 'country', 'city',
        'latitude', 'longitude', 'isp', 'method', 'path',
        'user_agent', 'payload', 'credentials_attempted',
        'session_duration', 'actions_taken', 'risk_score',
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
