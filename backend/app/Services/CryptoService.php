<?php

namespace App\Services;

use App\Models\Cryptomoney;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Models\CryptoHistory;

class CryptoService
{
    protected $cotationService;

    public function __construct()
    {
        $this->cotationService = new CotationService();
    }

    /**
     * Add or update a cryptocurrency from CoinGecko
     * @throws \Exception
     */
    public function addFromCoinGecko(string $cryptoId): string
    {
        $response = Http::withOptions(['verify' => false])
            ->get("https://api.coingecko.com/api/v3/coins/{$cryptoId}");

        if (!$response->ok()) {
            throw new \Exception('Crypto non trouvée sur CoinGecko.');
        }

        $data = $response->json();
        
        // ✅ Essayer d'abord CoinGecko
        $priceEur = $data['market_data']['current_price']['eur'] ?? null;

        // ✅ Si CoinGecko n'a pas le prix, utiliser le générateur local
        if ($priceEur === null) {
            $symbolForGen = $data['symbol'] ?? $data['id'] ?? $cryptoId;
            $priceEur = $this->cotationService->getInitialPrice($symbolForGen);
        }

        $cryptodata = [
            'name' => $data['name'] ?? null,
            'symbol' => $data['symbol'] ?? null,
            'image' => null,
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
     * Get market chart (last X days) from CoinGecko
     * Fallback to synthetic prices if CoinGecko fails
     */
    public function getMarketChart(string $coingeckoId, int $days = 30): array
    {
        $cacheKey = 'crypto_history:' . ($coingeckoId ?: 'synthetic_' . uniqid()) . ':' . $days;
        $ttl = 60 * 60 * 24 * 30;

        return Cache::remember($cacheKey, $ttl, function () use ($coingeckoId, $days) {
            // Cas 1: Pas de coingecko_id → Générer les prix localement
            if (empty($coingeckoId)) {
                $prices = $this->cotationService->generatePriceHistory($coingeckoId, $days);
                return $prices;
            }

            // Cas 2: Essayer CoinGecko d'abord
            $url = "https://api.coingecko.com/api/v3/coins/{$coingeckoId}/market_chart";
            $response = Http::withOptions(['verify' => false])->get($url, [
                'vs_currency' => 'eur',
                'days' => $days,
            ]);

            if (!$response->ok()) {
                // ❌ CoinGecko a échoué → Fallback au générateur local
                return $this->cotationService->generatePriceHistory($coingeckoId, $days);
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
     * Import and sync top 10 cryptos
     */
    public function ensureTopCryptos(array $coingeckoIds = [], int $days = 30): array
    {
        $results = ['imported' => 0, 'failed' => 0, 'errors' => []];

        if (empty($coingeckoIds)) {
            $coingeckoIds = [
                'bitcoin', 'ethereum', 'tether', 'binancecoin', 'usd-coin',
                'ripple', 'cardano', 'dogecoin', 'polygon', 'solana'
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