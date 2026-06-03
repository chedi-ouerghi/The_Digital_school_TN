<?php

namespace Database\Seeders;

use App\Services\CryptoService;
use App\Models\Cryptomoney;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class CryptoSeeder extends Seeder
{
    public function run(): void
    {

        // Map des 10 cryptos: symbol => image_file
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

        // Créer le dossier de destination
        $storagePath = public_path('storage/cryptos');
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        try {
            // ÉTAPE 1: Créer les 10 cryptos avec historique 
            $this->command->info('📝 Creating 10 cryptos with local data...');
            $cryptoService = app(CryptoService::class);
            $results = $cryptoService->ensureTopCryptos();

            // ÉTAPE 2: Copier les images
            $this->command->info('🖼️  Copying images from assests to public/storage/cryptos...');

            foreach ($imageMap as $symbol => $imageFile) {
                try {
                    $crypto = Cryptomoney::where('symbol', $symbol)->first();

                    if ($crypto && !$crypto->image) {
                        $assetsFile = base_path('assests/' . $imageFile);
                        $destFile = $storagePath . '/' . $imageFile;

                        if (File::exists($assetsFile)) {
                            File::copy($assetsFile, $destFile);
                            $crypto->update(['image' => 'cryptos/' . $imageFile]);
                            $this->command->line("  Image copied: {$imageFile}");
                            Log::info("Image copied for {$symbol}");
                        } else {
                            $this->command->warn("  ⚠️  Image file not found: {$assetsFile}");
                            Log::warning("Image file not found for {$symbol}");
                        }
                    }
                } catch (\Exception $e) {
                    $this->command->warn("  ⚠️  Could not copy image for {$symbol}: " . $e->getMessage());
                    Log::warning("Could not copy image for {$symbol}");
                }
            }

            // ÉTAPE 3: Afficher les résultats
            $this->command->info('');
            $this->command->info('📊 SEEDING RESULTS:');
            $this->command->info('Imported: ' . $results['imported']);
            $this->command->info('❌ Failed: ' . $results['failed']);

            if (!empty($results['errors'])) {
                $this->command->error('');
                $this->command->error('⚠️  Errors encountered:');
                foreach ($results['errors'] as $error) {
                    if (is_array($error)) {
                        $this->command->error('  - ' . $error['symbol'] . ': ' . $error['error']);
                    } else {
                        $this->command->error('  - ' . $error);
                    }
                }
            }

            // ÉTAPE 4: Afficher statistiques BD
            $cryptoCount = Cryptomoney::count();
            $historyCount = \App\Models\CryptoHistory::count();

            $this->command->info('');
            $this->command->info('📈 DATABASE STATUS:');
            $this->command->info('   - Cryptos created: ' . $cryptoCount);
            $this->command->info('   - History entries: ' . $historyCount . ' (30 days × cryptos)');
            $this->command->info('');
            $this->command->info('🎉 Crypto seeding completed!');
            $this->command->info('   ✓ Tables: cryptomoney ✓, crypto_history ✓');
            $this->command->info('   ✓ Images: Copied from assests ✓');
            $this->command->info('');

        } catch (\Exception $e) {
            Log::error('CryptoSeeder error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->command->error('❌ Seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }
}

