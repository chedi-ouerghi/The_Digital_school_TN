<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }

    protected function schedule(Schedule $schedule)
    {
        // Exécuter la synchronisation tous les jours à minuit
        $schedule->command('crypto:sync-history')->dailyAt('00:00');
    }
}
