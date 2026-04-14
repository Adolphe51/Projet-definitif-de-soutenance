<?php

namespace App\Enums;

enum AuditImportance: string
{
    case Faible = 'faible';       // Lecture, consultation
    case Moyenne = 'moyenne';     // Modification de données
    case Elevee = 'elevee';       // Authentification, changement de rôle, suppression
    case Critique = 'critique';   // Tentative d'intrusion, accès refusé répété
}
