<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use App\Models\CryptoWalletAsset;
use Carbon\Carbon;

class ProfileService
{
    /**
     * Récupère toutes les informations du profil d’un coup
     */
    public function getFullProfileOverview(string $userId, int $days = 30): array
    {
        $user = User::with([
            'wallet.cryptoWalletAssets.cryptomoney'
        ])->findOrFail($userId);

        $wallet = $user->wallet;

        if (!$wallet) {
            return $this->getEmptyOverview();
        }

        // --- DATA CALCULEE ---
        $growth = $this->calculateGrowthHistory($wallet, $days);
        $stats = $this->calculateCurrentStats($wallet);
        $distribution = $this->getPortfolioDistribution($wallet);

        // --- FORMAT CHART JS ---
        return [
            'stats' => $stats,
            'growth' => [
                'labels' => array_column($growth, 'date'),
                'data' => array_column($growth, 'value'),
                'raw' => $growth
            ],
            'distribution' => [
                'labels' => array_map(fn($item) =>
                    $item['crypto_name'].' ('.$item['crypto_symbol'].')'
                , $distribution),
                'data' => array_column($distribution, 'value'),
                'colors' => $this->generateColors(count($distribution)),
                'raw' => $distribution
            ],
        ];
    }

    private function getEmptyOverview(): array
    {
        return [
            'stats' => [
                'total_invested' => 0,
                'current_value' => 0,
                'total_profit' => 0,
                'profit_percentage' => 0,
                'total_transactions' => 0
            ],
            'growth' => ['labels' => [], 'data' => [], 'raw' => []],
            'distribution' => ['labels' => [], 'data' => [], 'colors' => [], 'raw' => []]
        ];
    }

    private function calculateGrowthHistory($wallet, int $days): array
    {
        $data = [];
        $today = Carbon::now();

        for ($i = $days; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);

            $value = 0;

            foreach ($wallet->cryptoWalletAssets as $asset) {
                $quantity = $this->getQuantityAtDate($asset, $date);

                $price = $asset->cryptomoney->price_eur ?? 0;

                $value += $quantity * $price;
            }

            $data[] = [
                'date' => $date->format('Y-m-d'),
                'value' => round($value, 2),
                'timestamp' => $date->timestamp * 1000
            ];
        }

        return $data;
    }

    private function getQuantityAtDate(CryptoWalletAsset $asset, Carbon $date): float
    {
        return Transaction::where('crypto_wallet_asset_id', $asset->id)
            ->where('created_at', '<=', $date)
            ->get()
            ->reduce(function ($carry, $transaction) {
                return $transaction->type === 'ACHAT'
                    ? $carry + $transaction->quantity
                    : $carry - $transaction->quantity;
            }, 0);
    }

    private function calculateCurrentStats($wallet): array
    {
        $totalInvested = 0;
        $currentValue = 0;

        foreach ($wallet->cryptoWalletAssets as $asset) {
            $invested = $asset->average_buy_price * $asset->quantity;
            $value = ($asset->cryptomoney->price_eur ?? 0) * $asset->quantity;

            $totalInvested += $invested;
            $currentValue += $value;
        }

        $profit = $currentValue - $totalInvested;

        return [
            'total_invested' => round($totalInvested, 2),
            'current_value' => round($currentValue, 2),
            'total_profit' => round($profit, 2),
            'profit_percentage' => $totalInvested > 0
                ? round(($profit / $totalInvested) * 100, 2)
                : 0,
            'total_transactions' => Transaction::whereHas('cryptoWalletAsset', function ($q) use ($wallet) {
                $q->where('wallet_id', $wallet->id);
            })->count()
        ];
    }

    private function getPortfolioDistribution($wallet): array
    {
        $distribution = [];
        $total = 0;

        foreach ($wallet->cryptoWalletAssets as $asset) {
            $value = ($asset->cryptomoney->price_eur ?? 0) * $asset->quantity;
            $total += $value;
        }

        foreach ($wallet->cryptoWalletAssets as $asset) {
            $value = ($asset->cryptomoney->price_eur ?? 0) * $asset->quantity;

            $distribution[] = [
                'crypto_name' => $asset->cryptomoney->name ?? "Unknown",
                'crypto_symbol' => $asset->cryptomoney->symbol ?? "N/A",
                'value' => round($value, 2),
                'percentage' => $total > 0 ? round(($value / $total) * 100, 2) : 0,
                'quantity' => $asset->quantity
            ];
        }

        return $distribution;
    }

    private function generateColors(int $count): array
    {
        $colors = [
            "rgb(255, 99, 132)", "rgb(54, 162, 235)", "rgb(255, 205, 86)",
            "rgb(75, 192, 192)", "rgb(153, 102, 255)", "rgb(255, 159, 64)"
        ];

        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = $colors[$i % count($colors)];
        }
        return $result;
    }
}
