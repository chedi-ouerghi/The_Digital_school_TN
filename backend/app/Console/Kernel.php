<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Les commandes Artisan fournies par votre application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\SyncCryptoHistory::class,
    ];

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        Log::info('Loading commands from: ' . __DIR__ . '/Commands');
        $this->load(__DIR__.'/Commands');
        
        // Vérifier si la commande est chargée
        $allCommands = $this->getArtisan()->all();
        Log::info('Available commands: ' . implode(', ', array_keys($allCommands)));
    }

    protected function schedule(Schedule $schedule)
    {
        // Exécuter la synchronisation tous les jours à minuit
        $schedule->command('crypto:sync-history')->dailyAt('00:00');
    }
}