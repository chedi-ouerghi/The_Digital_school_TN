<?php

namespace Database\Seeders;

use App\Models\Cryptomoney;
use App\Services\CryptoHistoryGenerator;
use Illuminate\Database\Seeder;

/**
 * Génère l'historique 40 jours pour toutes les cryptos existantes.
 * Idempotent via updateOrCreate + UNIQUE(cryptomoney_id, recorded_at).
 * Délègue au SSOT CryptoHistoryGenerator.
 */
class CryptoHistorySeeder extends Seeder
{
    public function run(): void
    {
        $days = (int) config('bitchest.history_days', 40);
        $generator = app(CryptoHistoryGenerator::class);

        $this->command?->info("📊 Generating {$days} days history for all cryptos...");

        foreach (Cryptomoney::all() as $crypto) {
            $generator->generateFor($crypto, $days);
            $this->command?->line("  ✓ {$crypto->symbol}: {$days} days ensured");
        }

        $this->command?->info('✅ CryptoHistory seeding completed (idempotent)');
    }
}
