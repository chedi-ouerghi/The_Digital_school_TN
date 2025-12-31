<?php

/**
 * Génère le prix initial d'une cryptomonnaie
 * Basé sur le premier caractère du symbole
 * 
 * Exemple:
 * - Bitcoin (B = 66) → prix entre 66 et 76 EUR
 * - Ethereum (E = 69) → prix entre 69 et 79 EUR
 * 
 * @param string $cryptoname Symbole ou nom de la crypto
 * @return float Prix initial entre 0.01 et 200+ EUR
 */
function getFirstCotation($cryptoname): float
{
    // Vérifier que l'entrée n'est pas vide
    if (empty($cryptoname)) {
        return random_int(10, 100);
    }

    // Caractère ASCII du premier caractère + random de 0 à 10
    $basePrice = ord(substr($cryptoname, 0, 1)) + rand(0, 10);
    
    // S'assurer que le prix est positif (minimum 0.01)
    return max(0.01, (float) $basePrice);
}

/**
 * Génère la variation journalière d'une cryptomonnaie
 * Résultat: entre -10 et +10% de variation basée sur les caractères
 * 
 * Exemple:
 * - "BTC" avec signe positif → +5% ou +6% du premier caractère
 * - "ETH" avec signe négatif → -5% ou -6% du dernier caractère
 * 
 * @param string $cryptoname Symbole ou nom de la crypto
 * @return float Delta de prix (peut être négatif)
 */
function getCotationFor($cryptoname): float
{
    if (empty($cryptoname)) {
        return (random_int(0, 99) > 40) ? random_int(1, 10) : -random_int(1, 10);
    }

    // 60% de chance baisse, 40% de chance hausse
    $sign = (rand(0, 99) > 40) ? 1 : -1;

    // Utiliser le premier ou dernier caractère pour varier
    $useFirst = rand(0, 99) > 49;
    $char = $useFirst ? substr($cryptoname, 0, 1) : substr($cryptoname, -1);
    $ordVal = ord($char);

    // Variation: ordVal × 1-10% (ex: 66 × 0.05 = 3.30)
    $multiplier = rand(1, 10) * 0.01;
    $delta = $sign * $ordVal * $multiplier;

    return round($delta, 2);
}

/**
 * Génère les prix historiques (30 derniers jours)
 * Utilisé principalement pour les tests et le prototypage
 * 
 * @param string $cryptoname
 * @param int $days Nombre de jours
 * @return array Array de [timestamp_ms, prix]
 */
function generatePriceHistory($cryptoname, int $days = 30): array
{
    $prices = [];
    $currentPrice = getFirstCotation($cryptoname);

    for ($i = $days; $i >= 0; $i--) {
        $timestamp = now()->subDays($i)->timestamp * 1000;
        
        if ($i !== $days) {
            $delta = getCotationFor($cryptoname);
            $currentPrice = max(0.01, $currentPrice + $delta);
        }

        $prices[] = [$timestamp, round($currentPrice, 2)];
    }

    return $prices;
}