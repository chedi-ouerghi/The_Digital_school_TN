<?php

/**
 * Renvoie la valeur de mise sur le marché de la crypto monnaie
 * @param $cryptoname {string} Le nom de la crypto monnaie
 */
function getFirstCotation($cryptoname){
  return ord(substr($cryptoname,0,1)) + rand(0, 10);
}

/**
 * Renvoie la variation de cotation de la crypto monnaie sur un jour
 * @param $cryptoname {string} Le nom de la crypto monnaie
 */
function getCotationFor($cryptoname){	
	return ((rand(0, 99)>40) ? 1 : -1) * ((rand(0, 99)>49) ? ord(substr($cryptoname,0,1)) : ord(substr($cryptoname,-1))) * (rand(1,10) * .01);
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