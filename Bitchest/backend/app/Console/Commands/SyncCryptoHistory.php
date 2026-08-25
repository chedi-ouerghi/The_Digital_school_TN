<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cryptomoney;
use App\Models\CryptoHistory;
use App\Services\CotationService;
use App\Services\NotificationService;

class SyncCryptoHistory extends Command
{
    // ⚠️ IMPORTANT : Doit correspondre à 'crypto:sync-history'
    protected $signature = 'crypto:sync-history';
    protected $description = 'Synchronize cryptocurrency prices for today only';

    public function handle()
    {
        $today = now()->startOfDay();
        $this->info("🔄 Syncing crypto prices for {$today->toDateString()}");

        $cryptos = Cryptomoney::all();

        if ($cryptos->isEmpty()) {
            $this->error('❌ No cryptocurrencies found');
            return Command::FAILURE;
        }

        $cotationService = new CotationService();
        $notificationService = app(NotificationService::class);
        $successCount = 0;
        $errorCount = 0;

        foreach ($cryptos as $crypto) {
            if ($this->syncCryptoForToday($crypto, $today, $cotationService, $notificationService)) {
                $successCount++;
            } else {
                $errorCount++;
            }

            usleep(50000); // 0.05 second
        }

        $this->info("✅ Sync completed: {$successCount} successful, {$errorCount} failed");

        return $successCount > 0 ? Command::SUCCESS : Command::FAILURE;
    }

    private function syncCryptoForToday($crypto, $today, $cotationService, NotificationService $notificationService): bool
    {
        // Idempotent : si déjà synchronisé aujourd'hui, vérifier cohérence prix courant
        $existing = CryptoHistory::where('cryptomoney_id', $crypto->id)
            ->whereDate('recorded_at', $today)
            ->first();

        if ($existing) {
            // S'assurer que le prix courant correspond à l'historique du jour
            if ((string) $crypto->price_eur !== (string) $existing->price) {
                $crypto->update(['price_eur' => $existing->price]);
                $this->info("  ⏭️ {$crypto->symbol}: Already synced, price aligned to €{$existing->price}");
            } else {
                $this->info("  ⏭️ {$crypto->symbol}: Already synced for today");
            }
            return true;
        }

        // Get last known price (veille)
        $lastHistory = CryptoHistory::where('cryptomoney_id', $crypto->id)
            ->whereDate('recorded_at', '<', $today)
            ->orderByDesc('recorded_at')
            ->first();

        $basePrice = $lastHistory
            ? (float) $lastHistory->price
            : (float) ($crypto->price_eur ?? $cotationService->getInitialPrice($crypto->symbol));

        // Generate today's variation
        $variation = $cotationService->getDailyVariation($crypto->symbol, $basePrice);
        $now = now();

        try {
            // Idempotent upsert
            CryptoHistory::updateOrCreate(
                [
                    'cryptomoney_id' => $crypto->id,
                    'recorded_at' => $today->toDateString(),
                ],
                [
                    'price' => round($variation['newPrice'], 10),
                    'volume' => $variation['volume'],
                ]
            );

            // Update crypto current price to match today's history
            $crypto->update([
                'price_eur' => $variation['newPrice'],
                'change_24h_pct' => $variation['changePercent'] ?? 0,
                'updated_at' => $now,
            ]);
            $notificationService->createPriceChange(
                $crypto,
                (string) $basePrice,
                (string) $variation['newPrice']
            );

            $this->info("  ✅ {$crypto->symbol}: €{$variation['newPrice']} ({$variation['changePercent']}%)");

            return true;

        } catch (\Throwable $e) {
            $this->error("  💥 {$crypto->symbol}: " . $e->getMessage());
            return false;
        }
    }
}