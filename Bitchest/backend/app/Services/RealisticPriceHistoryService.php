<?php

namespace App\Services;

use App\Models\Cryptomoney;
use Illuminate\Support\Facades\Log;

/**
 * @deprecated Délégué à CryptoHistoryGenerator (SSOT). Conservé pour compatibilité.
 */
class RealisticPriceHistoryService
{
    public function __construct(protected CryptoHistoryGenerator $generator = new CryptoHistoryGenerator()) {}

    public function generateRealisticHistory(Cryptomoney $crypto, int $days = 30): void
    {
        Log::info('RealisticPriceHistoryService delegating to CryptoHistoryGenerator', ['symbol' => $crypto->symbol]);
        $this->generator->generateFor($crypto, $days);
    }

    public function generateForAllCryptos(): array
    {
        return $this->generator->generateForAll();
    }
}
