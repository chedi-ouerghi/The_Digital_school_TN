<?php
// ============================================================================
// FILE: app/Console/Commands/RegenerateRealisticHistory.php
// RESPONSABILITÉ: Regénérer l'historique réaliste si besoin
// ============================================================================

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cryptomoney;
use App\Services\RealisticPriceHistoryService;
use Illuminate\Support\Facades\Log;

class RegenerateRealisticHistory extends Command
{
    protected $signature = 'crypto:regenerate-history {--symbol= : Symbol to regenerate (ex: BTC)}';
    protected $description = 'Regénérer l\'historique réaliste 30 jours pour une ou toutes les cryptos';

    public function handle()
    {
        $symbol = $this->option('symbol');
        $historyService = app(RealisticPriceHistoryService::class);

        if ($symbol) {
            // ✅ Regénérer pour une crypto spécifique
            $this->command->info("🔄 Regenerating history for {$symbol}...");

            $crypto = Cryptomoney::where('symbol', strtoupper($symbol))->first();

            if (!$crypto) {
                $this->error("❌ Crypto not found: {$symbol}");
                return;
            }

            try {
                $historyService->generateRealisticHistory($crypto, 30);
                $this->info("✅ History regenerated for {$symbol}");
                Log::info("History regenerated for {$symbol}");
            } catch (\Exception $e) {
                $this->error("❌ Error regenerating history: {$e->getMessage()}");
                Log::error("Error regenerating history", ['error' => $e->getMessage()]);
            }

      } else {
    // ✅ Regénérer pour TOUTES les cryptos
    $this->info('🔄 Regenerating history for ALL cryptos...');

    $results = $historyService->generateForAllCryptos();

    $this->info('');
    $this->info('📈 RESULTS:');
    $this->info('✅ Success: ' . $results['success']);
    $this->info('❌ Failed: ' . $results['failed']);

    foreach ($results['details'] as $symbol => $status) {
        $this->line('   ' . $status . ' ' . $symbol);
    }

    $this->info('');
    $this->info('✅ All histories regenerated!');
}

    }
}