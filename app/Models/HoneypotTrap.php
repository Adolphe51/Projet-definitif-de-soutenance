<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HoneypotTrap extends Model
{
    protected $fillable = [
        'name',
        'type',
        'fake_service',
        'port',
        'path',
        'status',
        'description',
        'lure_content',
        'interactions_count',
        'last_triggered_at',
        'config',
    ];

    protected $casts = [
        'config'            => 'array',
        'last_triggered_at' => 'datetime',
    ];

    // Types de pièges disponibles
    public static function trapTypes(): array
    {
        return [
            'fake_login'      => 'Faux Portail de Connexion',
            'fake_admin'      => 'Faux Panel Admin',
            'fake_db'         => 'Fausse Base de Données',
            'fake_api'        => 'Fausse API REST',
            'fake_ssh'        => 'Faux Serveur SSH',
            'fake_ftp'        => 'Faux Serveur FTP',
            'fake_phpmyadmin' => 'Faux phpMyAdmin',
            'fake_wordpress'  => 'Faux WordPress Admin',
            'canary_token'    => 'Token Canary (Piège URL)',
            'fake_document'   => 'Faux Document Sensible',
        ];
    }

    // Relation avec les interactions
    public function interactions()
    {
        return $this->hasMany(HoneypotInteraction::class);
    }
}
