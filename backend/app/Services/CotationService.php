<?php
namespace App\Services;

class CotationService
{
    /**
     * Retourne le prix initial pour une crypto
     */
    public function getInitialPrice(string $name): float
    {
        if (function_exists('getFirstCotation')) {
            return (float) getFirstCotation($name);
        }

        $firstChar = mb_substr(trim($name), 0, 1);
        if ($firstChar === '') {
            return round((float) random_int(1, 100), 2);
        }
        return round(ord($firstChar[0]) + random_int(0, 10), 2);
    }

    /**
     * Calcule la variation journalière et renvoie delta et nouveau prix.
     * @return array{delta: float, newPrice: float}
     */
    public function getDailyVariation(string $name, ?float $currentPrice = null): array
    {
        if (function_exists('getCotationFor')) {
            $delta = (float) getCotationFor($name);
        } else {
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