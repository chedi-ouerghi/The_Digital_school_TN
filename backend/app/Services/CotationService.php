<?php
// ============================================================================
// FILE: app/Services/CotationService.php
// RESPONSABILITÉ: Générer des prix synthétiques via cotation_generator.php
// ============================================================================

namespace App\Services;

use Illuminate\Support\Carbon;

class CotationService
{
    /**
     * Charger le fichier générateur de cotations
     */
    private function loadCotationGenerator(): void
    {
        if (!function_exists('getFirstCotation')) {
            require_once __DIR__ . '/../Helpers/cotation_generator.php';
        }
    }

    /**
     * Récupérer le prix initial synthétique pour une crypto
     * 
     * UTILISE: cotation_generator.php::getFirstCotation()
     * 
     * Retourne le code ASCII du 1er caractère + aléatoire
     * Exemples:
     * - BTC: ord('B') = 66 + rand(0-10) = 66-76
     * - ETH: ord('E') = 69 + rand(0-10) = 69-79
     * - XRP: ord('X') = 88 + rand(0-10) = 88-98
     * 
     * @param string $symbol Code de la crypto (BTC, ETH, etc.)
     * @return float Prix synthétique brut
     */
    public function getInitialPrice(string $symbol): float
    {
        $this->loadCotationGenerator();

        if (function_exists('getFirstCotation')) {
            $price = getFirstCotation($symbol);
            return max(0.01, (float) $price);
        }

        return max(0.01, (float) random_int(10, 100));
    }

    /**
     * Calculer la variation journalière
     * 
     * UTILISE: cotation_generator.php::getCotationFor()
     * 
     * ALGO:
     * - 60% chance augmentation, 40% baisse
     * - Amplitude basée sur les caractères du symbole
     * - Multiplicateur aléatoire 0.01-0.10
     * 
     * Exemples:
     * - BTC: variation entre ±0.66 et ±7.6
     * - ETH: variation entre ±0.69 et ±6.9
     * - XRP: variation entre ±0.88 et ±9.8
     * 
     * @param string $symbol Code de la crypto
     * @param float|null $currentPrice Prix actuel (optionnel)
     * @return array{delta: float, newPrice: float, changePercent: float, volume: float}
     */
public function getDailyVariation(string $symbol, ?float $currentPrice = null): array
{
    $this->loadCotationGenerator();

    $base = $currentPrice ?? $this->getInitialPrice($symbol);

    // Sécurité absolue
    if ($base <= 0.000001) {
        return [
            'delta' => 0,
            'newPrice' => max(0.01, $base),
            'changePercent' => 0,
            'volume' => 0.0,
        ];
    }

    // Variation RELATIVE (-5% → +5%)
    $relativeChange = random_int(-500, 500) / 10000; // -0.05 → +0.05

    $newPrice = round(max(0.01, $base * (1 + $relativeChange)), 6);

    $changePercent = round($relativeChange * 100, 2);

    $delta = round($newPrice - $base, 6);

    // ✅ CORRECTION: Générer un volume réaliste basé sur le prix et le symbole
    $volumeMultiplier = random_int(1000, 50000); // Volume aléatoire réaliste
    $volume = round($newPrice * $volumeMultiplier, 2);

    return [
        'delta' => $delta,
        'newPrice' => $newPrice,
        'changePercent' => $changePercent,
        'volume' => $volume,
    ];
}


    /**
     * Générer l'historique de prix pour 30 jours
     * 
     * UTILISE: cotation_generator.php (getFirstCotation + getCotationFor)
     * 
     * ⚠️ IMPORTANT: Les prix retournés sont SYNTHÉTIQUES (66-98, etc.)
     * Ils seront AJUSTÉS plus tard par le CryptoService avec un facteur multiplicateur
     * pour correspondre aux prix réels.
     * 
     * Exemple:
     * - Généré: [timestamp, 76.42] (NEM synthétique)
     * - Après ajustement ×0.00001570: [timestamp, 0.0012] (NEM réel) ✓
     * 
     * @param string $symbol Code de la crypto
     * @param int $days Nombre de jours (défaut: 30)
     * @return array Array de [timestamp_ms, prix]
     */
    public function generatePriceHistory(string $symbol, int $days = 30): array
    {
        $this->loadCotationGenerator();

        $prices = [];
        $currentPrice = $this->getInitialPrice($symbol);

        for ($i = $days; $i >= 0; $i--) {
            $timestamp = now()->subDays($i)->timestamp * 1000;

            if ($i !== $days) {
                $variation = $this->getDailyVariation($symbol, $currentPrice);
                $currentPrice = $variation['newPrice'];
            }

            $prices[] = [$timestamp, round($currentPrice, 2)];
        }

        return $prices;
    }
}
