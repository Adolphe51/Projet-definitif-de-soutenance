<?php

namespace App\Enums;

enum AppRole: string
{
    case Admin = 'admin';
    case Analyst = 'analyst';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur',
            self::Analyst => 'Analyste Sécurité'
        };
    }
}
