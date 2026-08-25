<?php

namespace Database\Seeders;

use App\Models\Cryptomoney;
use App\Models\CryptoHistory;
use App\Services\CryptoService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CryptoSeeder extends Seeder
{
    public function run(): void
    {
        $imageMap = [
            'BTC' => 'bitcoin.png',
            'ETH' => 'ethereum.png',
            'XRP' => 'ripple.png',
            'BCH' => 'bitcoin-cash.png',
            'ADA' => 'cardano.png',
            'LTC' => 'litecoin.png',
            'XEM' => 'nem.png',
            'XLM' => 'stellar.png',
            'IOTA' => 'iota.png',
            'DASH' => 'dash.png',
        ];

        $storagePath = public_path('storage/cryptos');
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        $this->command->info('📝 Creating 10 cryptos with local data...');
        $cryptoService = app(CryptoService::class);
        $results = $cryptoService->ensureTopCryptos();

        $this->command->info('🖼️  Copying images from assests to public/storage/cryptos...');
        foreach ($imageMap as $symbol => $imageFile) {
            try {
                $crypto = Cryptomoney::where('symbol', $symbol)->first();
                if ($crypto) {
                    $assetsFile = base_path('assests/' . $imageFile);
                    $destFile = $storagePath . '/' . $imageFile;
                    // Toujours copier si fichier source existe, même si image déjà renseignée (idempotent)
                    if (File::exists($assetsFile)) {
                        File::copy($assetsFile, $destFile);
                        if (!$crypto->image || $crypto->image !== 'cryptos/' . $imageFile) {
                            $crypto->update(['image' => 'cryptos/' . $imageFile]);
                        }
                        $this->command->line("  Image ensured: {$imageFile}");
                    } else {
                        $this->command->warn("  ⚠️  Image file not found: {$assetsFile}");
                    }
                }
            } catch (\Throwable $e) {
                $this->command->warn("  ⚠️  Could not copy image for {$symbol}: " . $e->getMessage());
            }
        }

        $this->command->info('');
        $this->command->info('📊 SEEDING RESULTS:');
        $this->command->info('Imported: ' . $results['imported']);
        $this->command->info('Failed: ' . $results['failed']);

        if (!empty($results['errors'])) {
            $this->command->error('Errors:');
            foreach ($results['errors'] as $error) {
                $this->command->error('  - ' . ($error['symbol'] ?? '?') . ': ' . ($error['error'] ?? json_encode($error)));
            }
        }

        // Validation 40 jours
        $days = (int) config('bitchest.history_days', 40);
        $cryptoCount = Cryptomoney::count();
        $historyCount = CryptoHistory::count();
        $expectedHistory = $cryptoCount * $days;

        // Duplicates check
        $duplicateRows = \Illuminate\Support\Facades\DB::table('crypto_history')
            ->select('cryptomoney_id', 'recorded_at', \Illuminate\Support\Facades\DB::raw('COUNT(*) as c'))
            ->groupBy('cryptomoney_id', 'recorded_at')
            ->having('c', '>', 1)
            ->count();

        $negativePrices = CryptoHistory::where('price', '<=', 0)->count();

        // Mismatches : dernier historique != prix courant
        $mismatches = 0;
        foreach (Cryptomoney::all() as $crypto) {
            $latest = CryptoHistory::where('cryptomoney_id', $crypto->id)->orderByDesc('recorded_at')->first();
            if (!$latest) {
                $mismatches++;
                continue;
            }
            if (bccomp((string) $latest->price, (string) $crypto->price_eur, 8) !== 0) {
                $mismatches++;
            }
        }

        // Missing dates per crypto
        $missingDates = 0;
        $today = now()->startOfDay();
        $start = $today->copy()->subDays($days - 1);
        foreach (Cryptomoney::all() as $crypto) {
            $dates = CryptoHistory::where('cryptomoney_id', $crypto->id)->pluck('recorded_at')->map(fn($d) => \Carbon\Carbon::parse($d)->toDateString())->toArray();
            for ($i = 0; $i < $days; $i++) {
                $expected = $start->copy()->addDays($i)->toDateString();
                if (!in_array($expected, $dates, true)) {
                    $missingDates++;
                }
            }
        }

        $this->command->info('');
        $this->command->info('📈 DATABASE STATUS:');
        $this->command->info("   - Cryptos: {$cryptoCount} (expected 10)");
        $this->command->info("   - History entries: {$historyCount} (expected {$expectedHistory} = {$cryptoCount} × {$days})");
        $this->command->info("   - Duplicate history rows: {$duplicateRows}");
        $this->command->info("   - Negative prices: {$negativePrices}");
        $this->command->info("   - Missing dates: {$missingDates}");
        $this->command->info("   - Current/latest price mismatches: {$mismatches}");
        $this->command->info('');

        if ($cryptoCount === 10 && $historyCount === $expectedHistory && $duplicateRows === 0 && $negativePrices === 0 && $missingDates === 0 && $mismatches === 0) {
            $this->command->info('✅ Crypto database initialization');
            $this->command->info('------------------------------');
            $this->command->info("Cryptocurrencies: {$cryptoCount}");
            $this->command->info("History records: {$historyCount}");
            $this->command->info("History days per crypto: {$days}");
            $this->command->info("Duplicate history rows: {$duplicateRows}");
            $this->command->info("Invalid prices: {$negativePrices}");
            $this->command->info("Missing dates: {$missingDates}");
            $this->command->info("Current/latest price mismatches: {$mismatches}");
            $this->command->info('');
            $this->command->info('Database initialization completed successfully.');
        } else {
            $this->command->warn('⚠️  Validation issues detected - see counts above');
        }
    }
}
