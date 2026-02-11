<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cryptomoney;
use App\Models\CryptoHistory;
use App\Services\CotationService;

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
        $successCount = 0;
        $errorCount = 0;

        foreach ($cryptos as $crypto) {
            if ($this->syncCryptoForToday($crypto, $today, $cotationService)) {
                $successCount++;
            } else {
                $errorCount++;
            }

            usleep(50000); // 0.05 second
        }

        $this->info("✅ Sync completed: {$successCount} successful, {$errorCount} failed");

        return $successCount > 0 ? Command::SUCCESS : Command::FAILURE;
    }

    private function syncCryptoForToday($crypto, $today, $cotationService): bool
    {
        // Check if already synced today
        $alreadySynced = CryptoHistory::where('cryptomoney_id', $crypto->id)
            ->whereDate('recorded_at', $today)
            ->exists();

        if ($alreadySynced) {
            $this->info("  ⏭️ {$crypto->symbol}: Already synced for today");
            return true;
        }

        // Get last known price
        $lastHistory = CryptoHistory::where('cryptomoney_id', $crypto->id)
            ->orderByDesc('recorded_at')
            ->first();

        $basePrice = $lastHistory
            ? (float) $lastHistory->price
            : $crypto->price_eur ?? $cotationService->getInitialPrice($crypto->symbol);

        // Generate today's variation
        $variation = $cotationService->getDailyVariation($crypto->symbol, $basePrice);
        $now = now();

        try {
            // Create history record
            CryptoHistory::create([
                'cryptomoney_id' => $crypto->id,
                'price' => round($variation['newPrice'], 10),
                'volume' => $variation['volume'],
                'recorded_at' => $today, // Historical date (today)
                'created_at' => $now,    // Actual creation time
                'updated_at' => $now,    // Actual update time
            ]);

            // Update crypto current price
            $crypto->update([
                'price_eur' => $variation['newPrice'],
                'change_24h_pct' => $variation['changePercent'] ?? 0,
                'updated_at' => $now,
            ]);

            $this->info("  ✅ {$crypto->symbol}: €{$variation['newPrice']} ({$variation['changePercent']}%)");

            return true;

        } catch (\Throwable $e) {
            $this->error("  💥 {$crypto->symbol}: " . $e->getMessage());
            return false;
        }
    }
}