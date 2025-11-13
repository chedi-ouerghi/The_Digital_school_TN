<?php

namespace App\Services;

use App\Models\Cryptomoney;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Models\CryptoHistory;
use App\Services\CotationService;

class CryptoService
{
    /**
     * Add or update a cryptocurrency from CoinGecko
     * @throws \Exception
     */
    public function addFromCoinGecko(string $cryptoId): string
    {
        $cotationPath = 'c:\\Users\\USER\\Downloads\\bitchest\\documents\\cotation_generator.php';
        if (!function_exists('getFirstCotation') && file_exists($cotationPath)) {
            require_once $cotationPath;
        }

        $response = Http::withOptions(['verify' => false])->get("https://api.coingecko.com/api/v3/coins/{$cryptoId}");
        if (!$response->ok()) {
            throw new \Exception('Crypto non trouvée sur CoinGecko.');
        }
        $data = $response->json();
        $priceEur = $data['market_data']['current_price']['eur'] ?? null;
        if ($priceEur === null && function_exists('getFirstCotation')) {
            $symbolForGen = $data['symbol'] ?? $data['id'] ?? $cryptoId;
            $priceEur = (float) getFirstCotation($symbolForGen);
        }

        $cryptodata = [
            'name' => $data['name'] ?? null,
            'symbol' => $data['symbol'] ?? null,
            'image' => null, // L'image sera gérée par l'upload dans le frontend
            'category' => $data['categories'][0] ?? null,
            'website' => $data['links']['homepage'][0] ?? null,
            'price_eur' => $priceEur,
            'market_cap' => $data['market_data']['market_cap']['eur'] ?? null,
            'volume_24h' => $data['market_data']['total_volume']['eur'] ?? null,
            'change_24h_pct' => $data['market_data']['price_change_percentage_24h'] ?? null,
            'updated_at_api' => isset($data['last_updated']) ? Carbon::parse($data['last_updated']) : now(),
            'coingecko_id' => $data['id'] ?? null,
        ];
        $crypto = Cryptomoney::updateOrCreate(
            ['symbol' => $cryptodata['symbol']],
            $cryptodata
        );
        return $crypto->id;
    }

    /**
     * Get market chart (last X days) from CoinGecko.
     * Returns CoinGecko 'prices' raw array: [[timestamp_ms, price], ...]
     * @throws \Exception
     */
    public function getMarketChart(string $coingeckoId, int $days = 30): array
    {
        $cacheKey = 'crypto_history:' . ($coingeckoId ?: 'synthetic_' . uniqid()) . ':' . $days;
        $ttl = 60 * 60 * 24 * 30;

        return Cache::remember($cacheKey, $ttl, function () use ($coingeckoId, $days) {
            $cotation = new CotationService();

            // Fallback: generate synthetic prices if CoinGecko id is empty or API fails
            if (empty($coingeckoId)) {
                $prices = [];
                $current = null;
                $cryptoModel = Cryptomoney::where('coingecko_id', $coingeckoId)->first();
                if ($cryptoModel) $current = $cryptoModel->price_eur;
                if ($current === null) $current = $cotation->getInitialPrice($coingeckoId);
                $p = (float)$current;
                for ($i = $days; $i >= 0; $i--) {
                    $ts = Carbon::now()->subDays($i)->timestamp * 1000;
                    if ($i !== $days) {
                        $deltaArr = $cotation->getDailyVariation($coingeckoId, $p);
                        $delta = $deltaArr['delta'] ?? 0;
                        $p = max(0.01, $p + $delta);
                    }
                    $prices[] = [$ts, round($p, 2)];
                }
                return $prices;
            }

            $url = "https://api.coingecko.com/api/v3/coins/{$coingeckoId}/market_chart";
            $response = Http::withOptions(['verify' => false])->get($url, [
                'vs_currency' => 'eur',
                'days' => $days,
            ]);
            if (!$response->ok()) {
                return $this->getMarketChart('', $days);
            }
            $data = $response->json();
            $prices = $data['prices'] ?? [];

            // Persist points to CryptoHistory
            $cryptoModel = Cryptomoney::where('coingecko_id', $coingeckoId)->first();
            if ($cryptoModel && !empty($prices)) {
                foreach ($prices as [$timestamp, $price]) {
                    $date = Carbon::createFromTimestampMs($timestamp);
                    CryptoHistory::updateOrCreate(
                        [
                            'cryptomoney_id' => $cryptoModel->id,
                            'recorded_at' => $date,
                        ],
                        [
                            'price' => $price,
                            'market_cap' => $cryptoModel->market_cap,
                            'volume' => $cryptoModel->volume_24h,
                        ]
                    );
                }
            }

            return $prices;
        });
    }

    /**
     * Ensure import/sync of a list of cryptos (e.g., top 10)
     * and generate/persist their history for $days days.
     */
    public function ensureTopCryptos(array $coingeckoIds = [], int $days = 30): array
    {
        $results = ['imported' => 0, 'failed' => 0, 'errors' => []];
        if (empty($coingeckoIds)) {
            $coingeckoIds = [
                'bitcoin','ethereum','tether','binancecoin','usd-coin',
                'ripple','cardano','dogecoin','polygon','solana'
            ];
        }
        foreach ($coingeckoIds as $id) {
            try {
                $this->addFromCoinGecko($id);
                $this->getMarketChart($id, $days);
                $results['imported']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = ['id' => $id, 'error' => $e->getMessage()];
            }
        }
        return $results;
    }
}