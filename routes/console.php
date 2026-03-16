<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/*
|--------------------------------------------------------------------------
| CyberGuard Scheduled Tasks
|--------------------------------------------------------------------------
*/

// Auto-génération d'attaques de démo (toutes les minutes)
Schedule::command('cyberguard:detect --count=3')
    ->everyMinute()
    ->withoutOverlapping();

// Auto-blocage IPs suspectes (toutes les 5 minutes)
Schedule::command('cyberguard:autoblock')
    ->everyFiveMinutes()
    ->withoutOverlapping();

// Simulation honeypot (toutes les 2 minutes)
Schedule::command('cyberguard:honeypot simulate --count=1')
    ->everyTwoMinutes()
    ->withoutOverlapping();

// Nettoyage quotidien à 3h
Schedule::command('cyberguard:cleanup --days=30 --force')
    ->dailyAt('03:00');
