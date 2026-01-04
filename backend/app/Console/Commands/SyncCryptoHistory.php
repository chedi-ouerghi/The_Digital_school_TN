<?php
// ============================================================================
// FILE: app/Console/Commands/SyncCryptoHistory.php
// RESPONSABILITÉ: Générer quotidiennement les nouveaux prix
// ============================================================================

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cryptomoney;
use App\Models\CryptoHistory;
use App\Services\CotationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SyncCryptoHistory extends Command
{
    protected $signature = 'crypto:sync-history';
    protected $description = 'Synchroniser les prix quotidiens des cryptomonnaies (LOCAL ONLY)';

    public function handle()
    {
        $this->info('🔄 Starting daily crypto price sync...');

        $cryptos = Cryptomoney::all();

        if ($cryptos->isEmpty()) {
            $this->warn('⚠️  No cryptos found. Run: php artisan db:seed --class=CryptoSeeder');
            return;
        }

        $bar = $this->output->createProgressBar(count($cryptos));
        $cotationService = new CotationService();

        foreach ($cryptos as $crypto) {
            try {
                // ✅ ÉTAPE 1: Récupérer le dernier prix (hier)
                $lastHistory = CryptoHistory::where('cryptomoney_id', $crypto->id)
                    ->orderBy('recorded_at', 'desc')
                    ->first();

                if (!$lastHistory) {
                    $this->line("\n  ⚠️  {$crypto->symbol}: No history found, skipping...");
                    $bar->advance();
                    continue;
                }

                $lastPrice = (float) $lastHistory->price;

                // ✅ ÉTAPE 2: Générer variation avec cotation_generator
                $variation = $cotationService->getDailyVariation($crypto->symbol, $lastPrice);
                $newPrice = $variation['newPrice'];
                $volume = $variation['volume'];

                // ✅ ÉTAPE 3: Sauvegarder en BD avec date d'aujourd'hui
                CryptoHistory::create([
                    'cryptomoney_id' => $crypto->id,
                    'price' => round($newPrice, 10),
                    'volume' => $volume,
                    'recorded_at' => now(),
                ]);

                // ✅ ÉTAPE 4: Mettre à jour le prix ACTUEL et le change_24h_pct
                $changePercent = $variation['changePercent'];
                $crypto->update([
                    'price_eur' => $newPrice,  // ✅ CORRECTION CRITIQUE: Mettre à jour le prix actuel
                    'change_24h_pct' => $changePercent
                ]);

                $this->line("\n  ✅ {$crypto->symbol}: €{$lastPrice} → €{$newPrice} ({$changePercent}%)");
                Log::info("Synced {$crypto->symbol}", [
                    'lastPrice' => $lastPrice,
                    'newPrice' => $newPrice,
                    'change24h' => $changePercent,
                ]);

                $bar->advance();

            } catch (\Exception $e) {
                $this->error("\n  ❌ Error syncing {$crypto->symbol}: {$e->getMessage()}");
                Log::error("Sync error for {$crypto->symbol}", [
                    'error' => $e->getMessage(),
                ]);
                $bar->advance();
            }
        }

        $bar->finish();

        // ✅ ÉTAPE 5: Nettoyer les données > 30 jours
        $this->line("\n\n🧹 Cleaning old data (> 30 days)...");
        $this->cleanOldHistory();

        $this->info('');
        $this->info('✅ Daily sync completed!');
    }

    /**
     * Supprimer les entrées d'historique > 30 jours
     */
    private function cleanOldHistory(): void
    {
        try {
            $cutoffDate = now()->subDays(30);

            $deleted = CryptoHistory::where('recorded_at', '<', $cutoffDate)
                ->delete();

            if ($deleted > 0) {
                $this->line("  ✅ Deleted {$deleted} old history entries");
                Log::info("Cleaned old history", ['deleted' => $deleted]);
            } else {
                $this->line("  ✓ No old entries to delete");
            }

        } catch (\Exception $e) {
            $this->warn("  ⚠️  Could not clean old history: {$e->getMessage()}");
            Log::warning("Error cleaning old history", ['error' => $e->getMessage()]);
        }
    }
}