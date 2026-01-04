<?php
// ============================================================================
// FILE: app/Services/RealisticPriceHistoryService.php
// RESPONSABILITÉ: Générer 30 jours de données réalistes et cohérentes
// ============================================================================

namespace App\Services;

use App\Models\Cryptomoney;
use App\Models\CryptoHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RealisticPriceHistoryService
{
    /**
     * 🚀 GÉNÉRER UN HISTORIQUE RÉALISTE DE 30 JOURS
     * 
     * ALGO:
     * 1. Prendre le prix actuel de la crypto
     * 2. Générer 30 jours en arrière avec variations réalistes (±2 à 5%)
     * 3. Calculer change_24h_pct pour chaque jour
     * 4. Générer volumes aléatoires réalistes
     * 5. Sauvegarder tout en BD de manière cohérente
     * 
     * Résultat: Historique crédible avec prix variables chaque jour
     * 
     * @param Cryptomoney $crypto La crypto à générer
     * @param int $days Nombre de jours (défaut: 30)
     * @return void
     */
    public function generateRealisticHistory(Cryptomoney $crypto, int $days = 30): void
    {
        Log::info('📊 Generating realistic history', [
            'symbol' => $crypto->symbol,
            'name' => $crypto->name,
            'currentPrice' => $crypto->price_eur,
            'days' => $days,
        ]);

        // ✅ ÉTAPE 1: Paramètres réalistes selon la crypto
        $currentPrice = (float) $crypto->price_eur;
        $volatility = $this->getVolatilityFactor($crypto->symbol);
        
        // Générer les volumes réalistes (proportionnels au prix et à la crypto)
        $baseVolume = $this->getBaseVolume($crypto->symbol);
        
        $prices = [];
        $now = now();

        // ✅ ÉTAPE 2: Générer 30 jours en arrière
        for ($i = $days; $i >= 0; $i--) {
            $dayDate = $now->copy()->subDays($i)->startOfDay();
            
            // Variation quotidienne réaliste (±2% à ±5%)
            if ($i < $days) {
                $variation = $this->generateDailyVariation($volatility);
                $currentPrice = max(0.00000001, $currentPrice * (1 + $variation));
            }

            // Générer volume aléatoire (±20% du volume de base)
            $volume = $baseVolume * rand(80, 120) / 100;

            $prices[] = [
                'recorded_at' => $dayDate,
                'price' => round($currentPrice, 10),
                'volume' => round($volume, 2),
                'previous_price' => null, // Sera calculé après
            ];
        }

        // ✅ ÉTAPE 3: Calculer les change_24h_pct
        for ($i = 0; $i < count($prices); $i++) {
            if ($i === 0) {
                // Premier jour: pas de jour précédent
                $prices[$i]['change_24h_pct'] = 0.00;
            } else {
                $prevPrice = $prices[$i - 1]['price'];
                $currentPrice = $prices[$i]['price'];
                
                $change = (($currentPrice - $prevPrice) / $prevPrice) * 100;
                $prices[$i]['change_24h_pct'] = round($change, 2);
            }
        }

        // ✅ ÉTAPE 4: Supprimer l'ancien historique
        CryptoHistory::where('cryptomoney_id', $crypto->id)->delete();
        
        // ✅ ÉTAPE 5: Sauvegarder en BD avec timestamps distincts
        $saved = 0;
        $failed = 0;

        foreach ($prices as $priceData) {
            try {
                CryptoHistory::create([
                    'cryptomoney_id' => $crypto->id,
                    'price' => $priceData['price'],
                    'volume' => $priceData['volume'],
                    'recorded_at' => $priceData['recorded_at'],
                ]);

                $saved++;

            } catch (\Exception $e) {
                Log::warning('Error saving price history', [
                    'crypto_id' => $crypto->id,
                    'date' => $priceData['recorded_at'],
                    'error' => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        // ✅ ÉTAPE 6: Mettre à jour le change_24h_pct dans Cryptomoney
        $lastEntry = end($prices);
        $crypto->update([
            'change_24h_pct' => $lastEntry['change_24h_pct'],
        ]);

        Log::info('✅ Realistic history generated', [
            'symbol' => $crypto->symbol,
            'saved' => $saved,
            'failed' => $failed,
            'finalPrice' => $lastEntry['price'],
            'change24h' => $lastEntry['change_24h_pct'],
        ]);
    }

    /**
     * GÉNÉRER 30 JOURS DE DONNÉES RÉALISTES POUR TOUTES LES CRYPTOS
     * 
     * Utilise cette méthode dans le seeder ou dans une commande artisan
     * 
     * @return array {success, failed, details}
     */
    public function generateForAllCryptos(): array
    {
        Log::info('🚀 Generating realistic history for ALL cryptos');

        $result = [
            'success' => 0,
            'failed' => 0,
            'details' => [],
        ];

        $cryptos = Cryptomoney::all();

        foreach ($cryptos as $crypto) {
            try {
                $this->generateRealisticHistory($crypto, 30);
                
                $result['success']++;
                $result['details'][$crypto->symbol] = '✅ Generated';

                Log::info("✅ {$crypto->symbol}: History generated");

            } catch (\Exception $e) {
                $result['failed']++;
                $result['details'][$crypto->symbol] = '❌ ' . $e->getMessage();

                Log::error("❌ {$crypto->symbol}: {$e->getMessage()}");
            }
        }

        Log::info('✅ All cryptos history generation complete', [
            'success' => $result['success'],
            'failed' => $result['failed'],
        ]);

        return $result;
    }

    /**
     * Déterminer le facteur de volatilité selon la crypto
     * 
     * Certaines cryptos sont plus volatiles que d'autres
     * Bitcoin: stable (±2-3%)
     * NEM, IOTA: très volatile (±4-6%)
     * Autres: moyen (±3-4%)
     * 
     * @param string $symbol
     * @return float Facteur de volatilité (0.02 à 0.06)
     */
    private function getVolatilityFactor(string $symbol): float
    {
        $volatilities = [
            'BTC' => 0.025,  // Bitcoin: stable
            'ETH' => 0.03,   // Ethereum: moyen
            'XRP' => 0.035,  // Ripple: moyen-haut
            'BCH' => 0.035,  // Bitcoin Cash: moyen-haut
            'ADA' => 0.04,   // Cardano: haut
            'LTC' => 0.035,  // Litecoin: moyen-haut
            'XEM' => 0.055,  // NEM: très volatile
            'XLM' => 0.045,  // Stellar: haut
            'IOTA' => 0.06,  // IOTA: très volatile
            'DASH' => 0.04,  // Dash: haut
        ];

        return $volatilities[strtoupper($symbol)] ?? 0.04;
    }

    /**
     * Déterminer le volume quotidien de base selon la crypto
     * 
     * Volume en unités (approximatif):
     * Bitcoin: 500 000 - 1 000 000
     * Ethereum: 1 000 000 - 2 000 000
     * Altcoins: 100 000 - 500 000
     * 
     * @param string $symbol
     * @return float Volume de base
     */
    private function getBaseVolume(string $symbol): float
    {
        $volumes = [
            'BTC' => 750000,   // Bitcoin: très élevé
            'ETH' => 1500000,  // Ethereum: très élevé
            'XRP' => 500000,   // Ripple: moyen-élevé
            'BCH' => 300000,   // Bitcoin Cash: moyen
            'ADA' => 400000,   // Cardano: moyen
            'LTC' => 350000,   // Litecoin: moyen
            'XEM' => 200000,   // NEM: bas-moyen
            'XLM' => 250000,   // Stellar: bas-moyen
            'IOTA' => 150000,  // IOTA: bas
            'DASH' => 200000,  // Dash: bas-moyen
        ];

        return $volumes[strtoupper($symbol)] ?? 300000;
    }

    /**
     * Générer une variation quotidienne réaliste
     * 
     * ALGO:
     * - 60% chance augmentation
     * - 40% chance baisse
     * - Amplitude: ±volatilityFactor (adapté à la crypto)
     * 
     * Résultat: variation entre -0.06 et +0.06 (±6% max)
     * 
     * @param float $volatility Facteur de volatilité (0.02 à 0.06)
     * @return float Variation relative (-0.06 à +0.06)
     */
    private function generateDailyVariation(float $volatility): float
    {
        // 60% augmentation, 40% baisse
        $direction = (random_int(0, 99) > 40) ? 1 : -1;
        
        // Variation aléatoire entre 0% et volatility
        $variation = (random_int(0, (int)($volatility * 10000)) / 10000) * $direction;
        
        return $variation;
    }
}