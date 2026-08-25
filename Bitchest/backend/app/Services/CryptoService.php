<?php

namespace App\Services;

use App\Models\Cryptomoney;
use App\Models\CryptoHistory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CryptoService
{
    public function __construct(
        protected CryptoHistoryGenerator $historyGenerator
    ) {}

    /**
     * Initialise les 10 cryptos officielles + historique 40j.
     * Idempotent : updateOrCreate + updateOrCreate pour history.
     *
     * @return array {imported, failed, errors}
     */
    public function ensureTopCryptos(): array
    {
        Log::info('Initializing 10 cryptos');

        $results = ['imported' => 0, 'failed' => 0, 'errors' => []];
        $cryptos = config('bitchest.cryptos', []);
        $days = (int) config('bitchest.history_days', 40);

        foreach ($cryptos as $symbol => $data) {
            try {
                $this->createOrUpdate($symbol, $data, $days);
                $results['imported']++;
            } catch (\Throwable $e) {
                $results['failed']++;
                $results['errors'][] = ['symbol' => $symbol, 'error' => $e->getMessage()];
                Log::error("Failed to create {$symbol}", ['error' => $e->getMessage()]);
            }
        }

        Log::info('Top cryptos initialization complete', $results);
        return $results;
    }

    private function createOrUpdate(string $symbol, array $data, ?int $days = null): string
    {
        $this->validateCryptoData($symbol, $data);
        $days = $days ?? (int) config('bitchest.history_days', 40);

        $symbol = strtoupper($symbol);
        $cryptoData = [
            'name' => $data['name'],
            'symbol' => $symbol,
            'category' => $data['category'] ?? 'Layer 1',
            'website' => $data['website'] ?? null,
            'price_eur' => round((float) $data['price_eur'], 10),
            'market_cap' => isset($data['market_cap']) ? round((float) $data['market_cap'], 2) : null,
            'change_24h_pct' => 0.00,
        ];

        $crypto = Cryptomoney::updateOrCreate(['symbol' => $symbol], $cryptoData);

        // Délégation SSOT : toute la logique historique est dans le generator
        $this->historyGenerator->generateFor($crypto, $days);

        return $crypto->id;
    }

    /**
     * Récupère l'historique marché pour un graphique.
     * Retourne [timestamp_ms, price, volume, change_24h_pct]
     */
    public function getMarketChart(string $cryptoId, int $days = 40): array
    {
        $cacheKey = 'crypto_history:' . $cryptoId . ':' . $days;
        $ttl = 60 * 60 * 6;

        return Cache::remember($cacheKey, $ttl, function () use ($cryptoId, $days) {
            $crypto = Cryptomoney::findOrFail($cryptoId);

            $history = CryptoHistory::where('cryptomoney_id', $cryptoId)
                ->where('recorded_at', '>=', now()->subDays($days - 1)->toDateString())
                ->orderBy('recorded_at', 'asc')
                ->get();

            if ($history->isEmpty()) {
                Log::warning('No history in DB for chart, returning empty', ['crypto_id' => $cryptoId]);
                return [];
            }

            $prices = [];
            foreach ($history as $idx => $record) {
                $change24h = 0.00;
                if ($idx > 0) {
                    $prev = (float) $history[$idx - 1]->price;
                    $curr = (float) $record->price;
                    if ($prev > 0) {
                        $change24h = round((($curr - $prev) / $prev) * 100, 2);
                    }
                }
                $timestamp = \Carbon\Carbon::parse($record->recorded_at)->startOfDay()->getTimestamp() * 1000;
                $prices[] = [
                    $timestamp,
                    (float) $record->price,
                    (float) ($record->volume ?? 0),
                    $change24h,
                ];
            }

            return $prices;
        });
    }

    public function listCryptos(int $perPage = 10)
    {
        return Cryptomoney::paginate($perPage);
    }

    public function getCryptoById(string $id): Cryptomoney
    {
        return Cryptomoney::findOrFail($id);
    }

    private function validateCryptoData(string $symbol, array $data): void
    {
        if (empty($symbol)) {
            throw new \InvalidArgumentException('Symbol is required');
        }
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('Name is required');
        }
        if (!isset($data['price_eur']) || !is_numeric($data['price_eur'])) {
            throw new \InvalidArgumentException('Price EUR must be numeric');
        }
    }
}
