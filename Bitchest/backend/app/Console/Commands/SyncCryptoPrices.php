<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cryptomoney;
use App\Models\CryptoHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;

class SyncCryptoPrices extends Command
{
    protected $signature = 'crypto:sync-prices';
    protected $description = 'Met à jour les prix actuels des cryptomonnaies depuis CoinGecko';

    public function handle()
    {
        $this->info('🔄 Synchronisation des prix actuels...');
        $notificationService = app(NotificationService::class);

        $updated = 0;
        $failed = 0;

        $cryptos = Cryptomoney::whereNotNull('coingecko_id')->get();

        if ($cryptos->isEmpty()) {
            $this->warn('❌ Aucune cryptomonnaie trouvée');
            return 1;
        }

        foreach ($cryptos as $crypto) {
            try {
                $this->line("  {$crypto->symbol}...", 'comment');

                $response = Http::withOptions(['verify' => false])
                    ->timeout(10)
                    ->get("https://api.coingecko.com/api/v3/coins/{$crypto->coingecko_id}");

                if (!$response->ok()) {
                    throw new \Exception("Status {$response->status()}");
                }

                $data = $response->json();
                $newPrice = $data['market_data']['current_price']['eur'] ?? null;

                if ($newPrice === null) {
                    throw new \Exception('Prix EUR non disponible');
                }

                $oldPrice = $crypto->price_eur;

                 // Mise à jour du prix courant de la cryptomonnaie
                $crypto->update([
                    'price_eur' => $newPrice,
                    'market_cap' => $data['market_data']['market_cap']['eur'] ?? null,
                    'volume_24h' => $data['market_data']['total_volume']['eur'] ?? null,
                    'change_24h_pct' => $data['market_data']['price_change_percentage_24h'] ?? null,
                    'updated_at_api' => now(),
                ]);

                // Sauvegarder dans l'historique
                CryptoHistory::create([
                    'cryptomoney_id' => $crypto->id,
                    'price' => $newPrice,
                    'market_cap' => $data['market_data']['market_cap']['eur'] ?? null,
                    'volume' => $data['market_data']['total_volume']['eur'] ?? null,
                    'recorded_at' => now(),
                ]);

                $notificationService->createPriceChange(
                    $crypto,
                    (string) ($oldPrice ?? '0'),
                    (string) $newPrice
                );

                $diff = $newPrice - ($oldPrice ?? 0);
                $trend = $diff >= 0 ? '📈' : '📉';
                $this->info("    ✅ {$crypto->symbol}: {$oldPrice} € → {$newPrice} € {$trend}");
                $updated++;

            } catch (\Exception $e) {
                $this->error("    ❌ {$crypto->symbol}: {$e->getMessage()}");
                Log::error("Erreur sync {$crypto->symbol}", ['error' => $e->getMessage()]);
                $failed++;
            }
        }

        $this->newLine();
        $this->info("✅ {$updated} prix mis à jour");
        if ($failed > 0) {
            $this->warn("❌ {$failed} erreurs");
        }

        return 0;
    }
}