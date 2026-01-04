<?php


// ============================================================================
// FILE: app/Services/CryptoService.php
// RESPONSABILITÉ: SANS API - Créer/gérer les cryptos avec données locales
// ============================================================================

namespace App\Services;

use App\Models\Cryptomoney;
use App\Models\CryptoHistory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class CryptoService
{
    protected $cotationService;

    public function __construct()
    {
        $this->cotationService = new CotationService();
    }

    /**
     * 🚀 INITIALISER les 10 cryptos SANS API CoinGecko
     * 
     * Cette méthode est appelée UNE SEULE FOIS lors du seeding initial.
     * 
     * ÉTAPES:
     * 1. Définir les 10 cryptos avec leurs prix de référence
     * 2. Pour chaque: créer/mettre à jour + générer historique 30j
     * 3. Copier les images depuis assests/
     * 
     * @return array Résultat de l'import {imported, failed, errors}
     */
    public function ensureTopCryptos(): array
    {
        Log::info('🚀 Initializing 10 cryptos WITHOUT CoinGecko API');

        $results = [
            'imported' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        // ✅ LES 10 CRYPTOS OBLIGATOIRES - HARDCODÉES (pas d'API)
        $cryptos = [
            'BTC' => [
                'name' => 'Bitcoin',
                'category' => 'Payment',
                'website' => 'https://bitcoin.org',
                'price_eur' => 95000.00,
            ],
            'ETH' => [
                'name' => 'Ethereum',
                'category' => 'Smart Contracts',
                'website' => 'https://ethereum.org',
                'price_eur' => 3500.00,
            ],
            'XRP' => [
                'name' => 'Ripple',
                'category' => 'Payment',
                'website' => 'https://ripple.com',
                'price_eur' => 2.50,
            ],
            'BCH' => [
                'name' => 'Bitcoin Cash',
                'category' => 'Payment',
                'website' => 'https://bitcoincash.org',
                'price_eur' => 450.00,
            ],
            'ADA' => [
                'name' => 'Cardano',
                'category' => 'Smart Contracts',
                'website' => 'https://cardano.org',
                'price_eur' => 1.05,
            ],
            'LTC' => [
                'name' => 'Litecoin',
                'category' => 'Payment',
                'website' => 'https://litecoin.org',
                'price_eur' => 180.00,
            ],
            'XEM' => [
                'name' => 'NEM',
                'category' => 'Smart Contracts',
                'website' => 'https://nem.io',
                'price_eur' => 0.0012,
            ],
            'XLM' => [
                'name' => 'Stellar',
                'category' => 'Payment',
                'website' => 'https://stellar.org',
                'price_eur' => 0.35,
            ],
            'IOTA' => [
                'name' => 'IOTA',
                'category' => 'IoT',
                'website' => 'https://www.iota.org',
                'price_eur' => 0.30,
            ],
            'DASH' => [
                'name' => 'Dash',
                'category' => 'Payment',
                'website' => 'https://www.dash.org',
                'price_eur' => 40.00,
            ],
        ];

        // ✅ PROCESSUS: Créer/mettre à jour + générer historique
        foreach ($cryptos as $symbol => $data) {
            try {
                Log::info("📝 Processing {$symbol}...", [
                    'name' => $data['name'],
                    'price' => $data['price_eur'],
                ]);

                // Créer/mettre à jour la crypto
                $this->createOrUpdate($symbol, $data);

                Log::info("✅ {$symbol}: {$data['name']} | €{$data['price_eur']}");
                $results['imported']++;

            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'symbol' => $symbol,
                    'error' => $e->getMessage(),
                ];
                Log::error("❌ Failed to create {$symbol}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('✅ Top cryptos initialization complete', [
            'imported' => $results['imported'],
            'failed' => $results['failed'],
        ]);

        return $results;
    }

    /**
     * Créer ou mettre à jour une crypto SANS API
     * 
     * ÉTAPES:
     * 1. Valider les données
     * 2. Créer/mettre à jour en BD
     * 3. Générer historique 30j avec ajustement aux prix réels
     * 
     * @param string $symbol Code (BTC, ETH, etc.)
     * @param array $data {name, price_eur, category, website, ...}
     * @return string ID de la crypto
     * @throws \Exception
     */
    private function createOrUpdate(string $symbol, array $data): string
    {
        $this->validateCryptoData($symbol, $data);

        $symbol = strtoupper($symbol);
        $name = $data['name'];
        $priceEur = (float) $data['price_eur'];

        // ✅ Construire les données complètes
        $cryptoData = [
            'name' => $name,
            'symbol' => $symbol,
            'category' => $data['category'] ?? 'Layer 1',
            'website' => $data['website'] ?? null,
            'price_eur' => round($priceEur, 8),
            'market_cap' => isset($data['market_cap']) ? round((float) $data['market_cap'], 8) : null,
            'change_24h_pct' => 0.00, // Sera calculé après historique
        ];

        // ✅ Sauvegarder ou mettre à jour
        $crypto = Cryptomoney::updateOrCreate(
            ['symbol' => $symbol],
            $cryptoData
        );

        Log::info('✅ Crypto saved', [
            'id' => $crypto->id,
            'symbol' => $symbol,
            'name' => $name,
            'price_eur' => $priceEur,
        ]);

        // ✅ Générer historique 30j avec ajustement aux prix réels
        $this->generateHistoryWithRealPrices($crypto, $priceEur, 30);

        return $crypto->id;
    }

    /**
     * Générer l'historique 30 jours AVEC ajustement aux prix réels
     * 
     * ALGO:
     * 1. Générer prix synthétiques (structure de variation)
     * 2. Calculer facteur d'ajustement: realPrice / generatedPrice
     * 3. Appliquer le facteur à TOUS les prix
     * 4. Sauvegarder en BD
     * 
     * Exemple pour NEM:
     * - Prix réel actuel: €0.0012
     * - Prix généré: 76.42
     * - Facteur: 0.0012 / 76.42 = 0.00001570
     * - Prix j-30 généré: 75.8 → ajusté: 75.8 × 0.00001570 = €0.00119
     * - Prix j0 généré: 76.42 → ajusté: 76.42 × 0.00001570 = €0.0012 ✓
     * 
     * @param Cryptomoney $crypto
     * @param float $currentRealPrice Prix réel actuel (EUR)
     * @param int $days Nombre de jours
     * @return void
     */
    private function generateHistoryWithRealPrices(
        Cryptomoney $crypto,
        float $currentRealPrice,
        int $days = 30
    ): void {
        Log::info('📊 Generating price history with adjustment', [
            'symbol' => $crypto->symbol,
            'realPrice' => $currentRealPrice,
            'days' => $days,
        ]);

        // ✅ ÉTAPE 1: Générer les prix synthétiques
        $generatedPrices = $this->cotationService->generatePriceHistory($crypto->symbol, $days);

        if (empty($generatedPrices)) {
            Log::warning('No prices generated', ['crypto_id' => $crypto->id]);
            return;
        }

        // ✅ ÉTAPE 2: Récupérer le prix généré "aujourd'hui" (dernier élément)
        $generatedToday = end($generatedPrices)[1];

        // ✅ ÉTAPE 3: Calculer le facteur d'ajustement
        $adjustmentFactor = $generatedToday > 0 ? ($currentRealPrice / $generatedToday) : 1;

        Log::info('📐 Adjustment factor', [
            'generatedToday' => $generatedToday,
            'realPrice' => $currentRealPrice,
            'factor' => $adjustmentFactor,
        ]);

        // ✅ ÉTAPE 4: Appliquer le facteur et sauvegarder
        $adjustedPrices = [];
        foreach ($generatedPrices as [$timestamp, $generatedPrice]) {
            $adjustedPrice = round($generatedPrice * $adjustmentFactor, 8);
            $adjustedPrices[] = [$timestamp, $adjustedPrice];
        }

        // ✅ ÉTAPE 5: Sauvegarder en BD
        $this->savePricesToDatabase($crypto->id, $adjustedPrices);
    }

    /**
     * Sauvegarder les prix en BD
     * 
     * @param string $cryptoId ID de la crypto
     * @param array $prices Array de [timestamp_ms, prix]
     */
    private function savePricesToDatabase(string $cryptoId, array $prices): void
    {
        if (empty($prices)) {
            return;
        }

        $saved = 0;
        $failed = 0;

        foreach ($prices as [$timestamp, $price]) {
            try {
                $date = Carbon::createFromTimestampMs($timestamp);

                CryptoHistory::updateOrCreate(
                    [
                        'cryptomoney_id' => $cryptoId,
                        'recorded_at' => $date,
                    ],
                    [
                        'price' => round($price, 10),
                        'volume' => 0,
                    ]
                );

                $saved++;

            } catch (\Exception $e) {
                Log::warning('Error saving price', [
                    'crypto_id' => $cryptoId,
                    'timestamp' => $timestamp,
                    'error' => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        Log::info('✅ Prices saved', [
            'crypto_id' => $cryptoId,
            'saved' => $saved,
            'failed' => $failed,
        ]);
    }


    /**
     * ✅ VERSION CORRIGÉE - Calcule VRAIMENT le change_24h_pct
     * 
     * Le problème était:
     * 1. Le cache retient les anciennes données
     * 2. La boucle foreach retourne toujours index 0
     * 
     * SOLUTION: Vider le cache ET recalculer proprement
     * 
     * @param string $cryptoId ID de la crypto
     * @param int $days Nombre de jours
     * @return array Array de [timestamp_ms, prix, change_24h_pct]
     */
    public function getMarketChart(string $cryptoId, int $days = 30): array
    {
        $cacheKey = 'crypto_history:' . $cryptoId . ':' . $days;
        $ttl = 60 * 60 * 24; // Cache 24h

        // ✅ MODIFICATION: Utiliser forget() pour forcer la recalcul
        Cache::forget($cacheKey);

        return Cache::remember($cacheKey, $ttl, function () use ($cryptoId, $days) {
            
            Log::info('📊 Fetching market chart', [
                'crypto_id' => $cryptoId,
                'days' => $days,
            ]);

            // ✅ Récupérer l'historique en BDD - ORDONNÉ CORRECTEMENT
            $history = CryptoHistory::where('cryptomoney_id', $cryptoId)
                ->where('recorded_at', '>=', now()->subDays($days))
                ->orderBy('recorded_at', 'asc')
                ->get()
                ->toArray(); // ✅ Convertir en array pour accès facile aux index

            if (empty($history)) {
                Log::warning('No history found', ['crypto_id' => $cryptoId]);
                return [];
            }

            // ✅ DÉBOGAGE: Logger le nombre d'entrées
            Log::info('History count: ' . count($history));

            // ✅ Construire le tableau avec change_24h_pct CORRECTEMENT
            $prices = [];

            for ($i = 0; $i < count($history); $i++) {
                $currentRecord = $history[$i];
                $change24h = 0.00;

                // ✅ Si ce n'est pas le premier élément (index 0)
                if ($i > 0) {
                    $prevPrice = (float) $history[$i - 1]['price'];
                    $currentPrice = (float) $currentRecord['price'];

                    if ($prevPrice > 0) {
                        $change24h = (($currentPrice - $prevPrice) / $prevPrice) * 100;
                        $change24h = round($change24h, 2);

                        // ✅ DÉBOGAGE: Logger le calcul
                        Log::debug("Day {$i}: {$prevPrice} → {$currentPrice} = {$change24h}%");
                    }
                }

                $prices[] = [
                    (int) strtotime($currentRecord['recorded_at']) * 1000,
                    (float) $currentRecord['price'],
                    $change24h, // ✅ CLÉS: Index 2 = change_24h_pct
                ];
            }

            Log::info('Prices array created', [
                'count' => count($prices),
                'sample' => array_slice($prices, 0, 2), // Voir les 2 premiers
            ]);

            return $prices;
        });
    }



    /**
     * Lister toutes les cryptos (paginated)
     * 
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
public function listCryptos(int $perPage = 10)
{
    return Cryptomoney::paginate($perPage);
}


    /**
     * Obtenir une crypto par ID
     * 
     * @param string $id
     * @return Cryptomoney
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getCryptoById(string $id): Cryptomoney
    {
        return Cryptomoney::findOrFail($id);
    }

    /**
     * Valider les données d'une crypto
     * 
     * @param string $symbol
     * @param array $data
     * @throws \Exception
     */
    private function validateCryptoData(string $symbol, array $data): void
    {
        if (empty($symbol)) {
            throw new \Exception('Symbol is required');
        }

        if (empty($data['name'])) {
            throw new \Exception('Name is required');
        }

        if (!isset($data['price_eur'])) {
            throw new \Exception('Price EUR is required');
        }

        if (!is_numeric($data['price_eur'])) {
            throw new \Exception('Price EUR must be numeric');
        }
    }
}