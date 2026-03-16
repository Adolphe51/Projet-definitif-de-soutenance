<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Les colonnes assignables en masse.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',       // 'admin' ou 'user'
        'face_image', // URL ou chemin du fichier pour la reconnaissance faciale
    ];

    /**
     * Les colonnes à cacher pour les arrays/JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Les colonnes à caster en type natif.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Vérifie si l'utilisateur est admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}