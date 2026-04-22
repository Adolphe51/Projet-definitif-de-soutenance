<?php

use App\Http\Middleware\Auth\AuditRequest as AuthAuditRequest;
use App\Http\Middleware\Auth\CheckIpAuthorized as AuthCheckIpAuthorized;
use App\Http\Middleware\Auth\SecuritySessionMiddleware as AuthSecuritySessionMiddleware;
use App\Http\Middleware\Auth\EnhancedCsrfProtection;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 🔐 CORRECTION : Ordre des middlewares respecté
        // L'ordre est critique pour la sécurité :
        // 1. csrf - Protection CSRF en premier
        // 2. honeypot - Détection des pièges
        // 3. blocked.ip - Vérification IP bloquées
        // 4. session.security - Authentification par session
        // 5. ip.authorized - Vérification IP autorisée
        // 6. audit - Journalisation en dernier (après authentification)
    
        $middleware->alias([
            'csrf' => EnhancedCsrfProtection::class,
            'honeypot' => \App\Http\Middleware\HoneypotMiddleware::class,
            'blocked.ip' => \App\Http\Middleware\CheckBlockedIp::class,
            'session.security' => AuthSecuritySessionMiddleware::class,
            'ip.authorized' => AuthCheckIpAuthorized::class,
            'audit' => AuthAuditRequest::class,
            'throttle' => \App\Http\Middleware\RateLimitMiddleware::class,
        ]);

        // 🔐 Groupes de middleware dans le bon ordre pour les routes protégées
        $middleware->group('secure', [
            'csrf',
            'honeypot',
            'blocked.ip',
            'session.security',
            'ip.authorized',
            'audit',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
