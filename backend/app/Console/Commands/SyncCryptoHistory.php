<?php
// ============================================================================
// FILE: app/Console/Commands/SyncCryptoHistory.php
// RESPONSABILITÉ: Synchroniser UNE SEULE FOIS par jour
// ============================================================================

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cryptomoney;
use App\Models\CryptoHistory;
use App\Services\CotationService;
use Illuminate\Support\Facades\Log;

class SyncCryptoHistory extends Command
{
    protected $signature = 'crypto:sync-history';
    protected $description = 'Synchroniser les prix quotidiens des cryptomonnaies (ONE DAY ONLY)';

public function handle()
{
    $today = now()->startOfDay();

    $this->info("🔄 Daily crypto sync with auto backfill for {$today->toDateString()}");

    $cryptos = Cryptomoney::all();

    if ($cryptos->isEmpty()) {
        $this->error('❌ No cryptomoney found');
        return Command::FAILURE;
    }

    $cotationService = new CotationService();

    try {
        foreach ($cryptos as $crypto) {

            $this->info("📈 Processing {$crypto->symbol}");

            /**
             * Dernier historique connu
             */
            $lastHistory = CryptoHistory::where('cryptomoney_id', $crypto->id)
                ->orderByDesc('recorded_at')
                ->first();

            // Date de départ
            $startDate = $lastHistory
                ? $lastHistory->recorded_at->copy()->startOfDay()->addDay()
                : $today->copy()->subDay();

            // Prix de départ
            $basePrice = $lastHistory
                ? (float) $lastHistory->price
                : $cotationService->getInitialPrice($crypto->symbol);

            /**
             * Boucle sur TOUS les jours manquants
             */
            for ($date = $startDate; $date->lte($today); $date->addDay()) {

                // Sécurité anti-duplication
                $alreadyExists = CryptoHistory::where('cryptomoney_id', $crypto->id)
                    ->whereDate('recorded_at', $date)
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                $variation = $cotationService->getDailyVariation(
                    $crypto->symbol,
                    $basePrice
                );

                CryptoHistory::create([
                    'cryptomoney_id' => $crypto->id,
                    'price' => round($variation['newPrice'], 10),
                    'volume' => $variation['volume'],
                    'recorded_at' => $date->copy(),
                ]);

                // Le prix du jour devient la base du lendemain
                $basePrice = $variation['newPrice'];

                $this->info("  ➕ {$crypto->symbol} synced for {$date->toDateString()}");
            }

            /**
             * Mise à jour du prix courant (dernier jour uniquement)
             */
            $crypto->update([
                'price_eur' => $basePrice,
                'change_24h_pct' => $variation['changePercent'] ?? 0,
            ]);
        }

        $this->cleanOldHistory();

        $this->info('✅ Crypto sync with backfill completed successfully');
        return Command::SUCCESS;

    } catch (\Throwable $e) {

        $this->error('💥 Crypto sync FAILED');
        Log::critical('Crypto sync failed', [
            'error' => $e->getMessage(),
        ]);

        return Command::FAILURE;
    }
}


    /**
     * Supprimer les entrées > 365 jours
     */
    private function cleanOldHistory(): void
    {
        try {
            $cutoffDate = now()->subDays(365);

            CryptoHistory::where('recorded_at', '<', $cutoffDate)->delete();

        } catch (\Throwable $e) {
            Log::warning('Error cleaning crypto history', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
