<?php

namespace App\Enums;

enum AppRole: string
{
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrateur',
        };
    }
}
