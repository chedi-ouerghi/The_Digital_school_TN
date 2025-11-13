<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Exécuter la synchronisation tous les jours à minuit
        $schedule->command('crypto:sync-history')->dailyAt('00:00');
    }
}
