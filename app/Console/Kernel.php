<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Auto-génération d'attaques de démo toutes les minutes (mode démo)
        if (config('cyberguard.detection.demo_mode', true)) {
            $schedule->command('cyberguard:detect --count=3')
                     ->everyMinute()
                     ->withoutOverlapping()
                     ->runInBackground();
        }

        // Auto-blocage des IPs suspectes toutes les 5 minutes
        $schedule->command('cyberguard:autoblock')
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->runInBackground();

        // Simulation d'interactions honeypot toutes les 2 minutes
        if (config('cyberguard.honeypot.enabled', true)) {
            $schedule->command('cyberguard:honeypot simulate --count=2')
                     ->everyTwoMinutes()
                     ->withoutOverlapping()
                     ->runInBackground();
        }

        // Nettoyage des vieilles attaques (garder 30 jours)
        $schedule->command('cyberguard:cleanup --days=30')
                 ->daily()
                 ->at('03:00');

        // Rapport journalier
        $schedule->command('cyberguard:honeypot report')
                 ->dailyAt('08:00')
                 ->emailOutputTo(config('cyberguard.honeypot.alert_email'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
