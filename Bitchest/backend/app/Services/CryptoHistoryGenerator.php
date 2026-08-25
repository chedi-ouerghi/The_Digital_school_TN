<?php

namespace App\Services;

use App\Models\Cryptomoney;
use App\Models\CryptoHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Service unique (SSOT) pour la génération d'historique crypto.
 *
 * Responsabilités :
 *  - Générer 40 jours d'historique (J-39 .. J) pour une crypto
 *  - Garantir : dernier prix historique == cryptomoney.price_eur
 *  - Garantir : UNIQUE(cryptomoney_id, recorded_at) via updateOrCreate
 *  - Garantir : prix > 0, variation contrôlée
 *  - Garantir : recorded_at = date métier, created_at/updated_at = now()
 *  - Idempotent : plusieurs exécutions produisent exactement 400 lignes
 */
class CryptoHistoryGenerator
{
    /**
     * Génère l'historique pour une crypto donnée.
     *
     * @param Cryptomoney $crypto
     * @param int|null $days Nombre de jours (défaut: config bitchest.history_days)
     * @return int Nombre de lignes créées/mises à jour
     */
    public function generateFor(Cryptomoney $crypto, ?int $days = null): int
    {
        $days = $days ?? (int) config('bitchest.history_days', 40);
        if ($days < 1) {
            throw new \InvalidArgumentException('Days must be >= 1');
        }

        $currentPrice = (float) $crypto->price_eur;
        if ($currentPrice <= 0) {
            throw new \InvalidArgumentException("Invalid current price for {$crypto->symbol}: {$currentPrice}");
        }

        $volatility = $this->getVolatility($crypto->symbol);
        $baseVolume = $this->getBaseVolume($crypto->symbol);

        $today = Carbon::now()->startOfDay(); // J
        $startDate = $today->copy()->subDays($days - 1); // J-39

        // Construire les 40 dates (J-39 .. J)
        $dates = [];
        for ($i = 0; $i < $days; $i++) {
            $dates[] = $startDate->copy()->addDays($i);
        }

        // Génération backward : on part du prix courant (J) et on remonte
        $pricesByDate = [];
        $pricesByDate[$days - 1] = $currentPrice; // J = prix courant exact

        // Seed déterministe basé sur symbol pour reproductibilité intra-journée
        // Variation jouée à l'envers : chaque jour précédent est dérivé du suivant
        for ($i = $days - 2; $i >= 0; $i--) {
            $nextPrice = $pricesByDate[$i + 1];
            // Variation déterministe mais pseudo-aléatoire : utiliser hash du symbole + index + date
            $seed = crc32($crypto->symbol . '|' . $dates[$i]->toDateString() . '|' . $i);
            // Générer variation entre -volatility et +volatility
            // On dé-randomise via seed: extraire un float entre 0 et 1 depuis seed
            $rand01 = (($seed & 0x7FFFFFFF) % 10000) / 10000; // 0..1
            // 60% hausse, 40% baisse en forward => en backward on inverse simplement le signe pour varier
            $directionRand = (($seed >> 16) % 100); // 0..99
            $direction = $directionRand > 40 ? 1 : -1;
            // Variation relative entre 0 et volatility
            $variation = $direction * ($rand01 * $volatility);

            // Prix précédent = prix suivant * (1 + variation_backward)
            // Mais variation_backward est déjà aléatoire, on applique directement
            // Pour éviter drift inverse, on fait : previous = next * (1 - variation_forward_approx)
            // Simplifié : previous = next * (1 + variation)
            $prevPrice = $nextPrice * (1 + $variation);

            // Garde-fou : jamais <= 0, plancher à 1% du prix courant ou 0.00000001
            $floor = max(0.00000001, $currentPrice * 0.01);
            if ($prevPrice <= $floor) {
                $prevPrice = $floor + abs($variation) * $nextPrice * 0.5;
            }
            $prevPrice = max($floor, $prevPrice);

            $pricesByDate[$i] = round($prevPrice, 10);
        }

        // S'assurer que le dernier est EXACTEMENT le prix courant (précision 10)
        $pricesByDate[$days - 1] = round($currentPrice, 10);

        // Sauvegarde idempotente via updateOrCreate
        $count = 0;
        $now = Carbon::now();
        foreach ($dates as $idx => $date) {
            $price = round($pricesByDate[$idx], 10);
            if ($price <= 0) {
                $price = 0.00000001;
            }

            // Volume déterministe : base * (0.8 à 1.2)
            $seedVol = crc32($crypto->symbol . '|vol|' . $date->toDateString());
            $volFactor = 80 + ($seedVol % 41); // 80..120
            $volume = round($baseVolume * $volFactor / 100, 2);

            try {
                CryptoHistory::updateOrCreate(
                    [
                        'cryptomoney_id' => $crypto->id,
                        'recorded_at' => $date->toDateString(), // date sans heure
                    ],
                    [
                        'price' => $price,
                        'volume' => $volume,
                        // created_at / updated_at gérés par updateOrCreate mais on force updated_at = now
                        // recorded_at reste la date métier
                    ]
                );
                // Forcer updated_at = now() même si record existait ? updateOrCreate le fait déjà
                $count++;
            } catch (\Throwable $e) {
                Log::warning('CryptoHistoryGenerator: save failed', [
                    'symbol' => $crypto->symbol,
                    'date' => $date->toDateString(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Mettre à jour change_24h_pct sur Cryptomoney (J vs J-1)
        if ($days >= 2) {
            $todayPrice = $pricesByDate[$days - 1];
            $yesterdayPrice = $pricesByDate[$days - 2];
            if ($yesterdayPrice > 0) {
                $change = (($todayPrice - $yesterdayPrice) / $yesterdayPrice) * 100;
                $crypto->update(['change_24h_pct' => round($change, 2)]);
            }
        }

        Log::info('CryptoHistoryGenerator: generated', [
            'symbol' => $crypto->symbol,
            'days' => $days,
            'count' => $count,
        ]);

        return $count;
    }

    /**
     * Génère pour toutes les cryptos.
     */
    public function generateForAll(?int $days = null): array
    {
        $days = $days ?? (int) config('bitchest.history_days', 40);
        $result = ['success' => 0, 'failed' => 0, 'details' => []];

        foreach (Cryptomoney::all() as $crypto) {
            try {
                $this->generateFor($crypto, $days);
                $result['success']++;
                $result['details'][$crypto->symbol] = 'OK';
            } catch (\Throwable $e) {
                $result['failed']++;
                $result['details'][$crypto->symbol] = $e->getMessage();
            }
        }

        return $result;
    }

    private function getVolatility(string $symbol): float
    {
        $map = [
            'BTC' => 0.025,
            'ETH' => 0.030,
            'XRP' => 0.035,
            'BCH' => 0.035,
            'ADA' => 0.040,
            'LTC' => 0.035,
            'XEM' => 0.055,
            'XLM' => 0.045,
            'IOTA' => 0.060,
            'DASH' => 0.040,
        ];
        return $map[strtoupper($symbol)] ?? 0.04;
    }

    private function getBaseVolume(string $symbol): float
    {
        $map = [
            'BTC' => 750000,
            'ETH' => 1500000,
            'XRP' => 500000,
            'BCH' => 300000,
            'ADA' => 400000,
            'LTC' => 350000,
            'XEM' => 200000,
            'XLM' => 250000,
            'IOTA' => 150000,
            'DASH' => 200000,
        ];
        return $map[strtoupper($symbol)] ?? 300000;
    }
}
