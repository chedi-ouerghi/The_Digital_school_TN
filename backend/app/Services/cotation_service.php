<?php
namespace App\Services;

// Service d'intégration pour la cotation dans le backend (bk).
// Exemples d'utilisation :
// require_once __DIR__ . '/cotation_service.php';
// $svc = new CotationService();
// $initial = $svc->getInitialPrice('Bitcoin');
// $res = $svc->getDailyVariation('Bitcoin', $initial);

class CotationService
{
    /**
     * Retourne le prix initial pour une crypto
     * @param string $name
     * @return float
     */
    public function getInitialPrice(string $name): float
    {
        // Si le fichier generator existe à un emplacement connu, on peut require le fichier.
        // Sinon se reposer sur une implémentation locale.
        if (function_exists('getFirstCotation')) {
            return (float) getFirstCotation($name);
        }

        // Implémentation locale de secours (même logique que le generator)
        $firstChar = mb_substr(trim($name), 0, 1);
        if ($firstChar === '') {
            return round((float) random_int(1, 100), 2);
        }
        return round(ord($firstChar[0]) + random_int(0, 10), 2);
    }

    /**
     * Calcule la variation journalière et renvoie delta et nouveau prix.
     * @param string $name
     * @param float|null $currentPrice Si null, la fonction récupère le prix initial.
     * @return array{delta: float, newPrice: float}
     */
    public function getDailyVariation(string $name, ?float $currentPrice = null): array
    {
        if (function_exists('getCotationFor')) {
            $delta = (float) getCotationFor($name);
        } else {
            // implémentation locale simple si le generator n'est pas inclus
            $useFirst = random_int(0, 99) > 49;
            $char = $useFirst ? mb_substr($name, 0, 1) : mb_substr($name, -1);
            $ordVal = $char === '' ? random_int(1, 10) : ord($char[0]);
            $sign = (random_int(0, 99) > 40) ? 1 : -1;
            $multiplier = random_int(1, 10) * 0.01;
            $delta = round($sign * $ordVal * $multiplier, 2);
        }

        $base = $currentPrice ?? $this->getInitialPrice($name);
        $newPrice = round($base + $delta, 2);

        return ['delta' => $delta, 'newPrice' => $newPrice];
    }
}
