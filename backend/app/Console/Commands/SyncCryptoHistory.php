<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cryptomoney;
use App\Models\CryptoHistory;
use App\Services\CryptoService;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class SyncCryptoHistory extends Command
{
    protected $signature = 'crypto:sync-history';
    protected $description = 'Synchroniser l\'historique des prix des cryptomonnaies';

    public function handle(CryptoService $cryptoService)
    {
        $this->info('Début de la synchronisation de l\'historique...');

        $cryptos = Cryptomoney::whereNotNull('coingecko_id')->get();
        $bar = $this->output->createProgressBar(count($cryptos));

        foreach ($cryptos as $crypto) {
            try {
                $history = $cryptoService->getMarketChart($crypto->coingecko_id, 30);
                
                foreach ($history as [$timestamp, $price]) {
                    $date = Carbon::createFromTimestampMs($timestamp);
                    
                    CryptoHistory::updateOrCreate(
                        [
                            'cryptomoney_id' => $crypto->id,
                            'recorded_at' => $date,
                        ],
                        [
                            'price' => $price,
                            'market_cap' => $crypto->market_cap,
                            'volume' => $crypto->volume_24h,
                        ]
                    );
                }
                
                $bar->advance();
            } catch (\Exception $e) {
                $this->error("Erreur pour {$crypto->symbole}: {$e->getMessage()}");
            }
        }

        $bar->finish();
        $this->info("\nSynchronisation terminée!");

        // Notifier tous les clients (mise à jour des cours)
        try {
            $clients = User::where('role', 'CLIENT')->get();
            foreach ($clients as $client) {
                Notification::create([
                    'user_id' => $client->id,
                    'title' => 'Mise à jour des cours',
                    'message' => 'Les cours des cryptomonnaies ont été mis à jour automatiquement.',
                    'type' => Notification::TYPE_PRICE_UPDATE,
                ]);
            }
        } catch (\Exception $e) {
            $this->error('Erreur lors de la création des notifications de mise à jour: ' . $e->getMessage());
        }
    }
}
