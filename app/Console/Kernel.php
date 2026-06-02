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
        // Auto-blocage des IPs suspectes toutes les 5 minutes (toujours actif)
        $schedule->command('cyberguard:autoblock')
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->runInBackground();

        if (config('cyberguard.detection.log_ingestion.enabled') && config('cyberguard.detection.log_ingestion.access_log_path')) {
            $schedule->command('cyberguard:collect-web-logs')
                ->everyMinute()
                ->withoutOverlapping();
        }

        // Nettoyage des vieilles attaques (toujours actif - maintenance)
        $schedule->command('cyberguard:cleanup --days=30')
                 ->daily()
                 ->at('03:00');

        // Rapport journalier (avec email si configuré)
        if (config('cyberguard.honeypot.alert_email')) {
            $schedule->command('cyberguard:honeypot report')
                     ->dailyAt('08:00')
                     ->emailOutputTo(config('cyberguard.honeypot.alert_email'));
        }
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
