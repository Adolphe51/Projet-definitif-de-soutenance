<?php

namespace App\Enums;

enum AuditResult: string
{
    case Autorise = 'autorise';
    case Refuse = 'refuse';
    case Erreur = 'erreur';
}
