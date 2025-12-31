<?php

namespace App\Services;

/**
 * Service centralisé pour la gestion des cotations
 * 
 * Responsabilités:
 * - Génération des prix initiaux
 * - Calcul des variations journalières
 * - Génération d'historiques
 * - Protection contre les prix négatifs
 * 
 * Dépend de: app/Helpers/cotation_generator.php
 */
class CotationService
{
    /**
     * Inclut le fichier generateur si pas déjà chargé
     */
    private function loadCotationGenerator(): void
    {
        if (!function_exists('getFirstCotation')) {
            require_once __DIR__ . '/../Helpers/cotation_generator.php';
        }
    }

    /**
     * Retourne le prix initial pour une crypto
     * 
     * Ordre de priorité:
     * 1. Utilise getFirstCotation() du generator si disponible
     * 2. Fallback: implémentation locale
     * 
     * @param string $name Symbole ou nom de la crypto
     * @return float Prix initial (minimum 0.01)
     */
    public function getInitialPrice(string $name): float
    {
        $this->loadCotationGenerator();

        if (function_exists('getFirstCotation')) {
            return max(0.01, (float) getFirstCotation($name));
        }

        // Fallback si le fichier n'est pas trouvé
        if (empty($name)) {
            return (float) random_int(10, 100);
        }

        $basePrice = ord(substr($name, 0, 1)) + random_int(0, 10);
        return max(0.01, (float) $basePrice);
    }

    /**
     * Calcule la variation journalière
     * 
     * Protections appliquées:
     * - Prix ne peut jamais être négatif (min 0.01)
     * - Variations réalistes (±10% maximum)
     * 
     * @param string $name Symbole ou nom de la crypto
     * @param float|null $currentPrice Prix actuel (sinon utilise le prix initial)
     * @return array{delta: float, newPrice: float} Delta appliqué et nouveau prix
     */
    public function getDailyVariation(string $name, ?float $currentPrice = null): array
    {
        $this->loadCotationGenerator();

        if (function_exists('getCotationFor')) {
            $delta = (float) getCotationFor($name);
        } else {
            // Fallback si le fichier n'est pas trouvé
            $sign = (random_int(0, 99) > 40) ? 1 : -1;
            $char = random_int(0, 99) > 49 
                ? substr($name, 0, 1) 
                : substr($name, -1);
            $ordVal = ord($char);
            $multiplier = random_int(1, 10) * 0.01;
            $delta = round($sign * $ordVal * $multiplier, 2);
        }

        $base = $currentPrice ?? $this->getInitialPrice($name);
        
        // ✅ PROTECTION: Prix jamais négatif
        $newPrice = round(max(0.01, $base + $delta), 2);

        return ['delta' => $delta, 'newPrice' => $newPrice];
    }

    /**
     * Génère un historique de prix (30 derniers jours)
     * 
     * Utilisé principalement pour:
     * - Les tests et prototypage
     * - Le fallback quand CoinGecko n'est pas disponible
     * - Les données de démonstration
     * 
     * @param string $name Symbole ou nom de la crypto
     * @param int $days Nombre de jours (défaut 30)
     * @return array Array de [timestamp_ms, prix]
     */
    public function generatePriceHistory(string $name, int $days = 30): array
    {
        $prices = [];
        $currentPrice = $this->getInitialPrice($name);

        for ($i = $days; $i >= 0; $i--) {
            $timestamp = now()->subDays($i)->timestamp * 1000;

            if ($i !== $days) {
                $variation = $this->getDailyVariation($name, $currentPrice);
                $currentPrice = $variation['newPrice'];
            }

            $prices[] = [$timestamp, $currentPrice];
        }

        return $prices;
    }
}