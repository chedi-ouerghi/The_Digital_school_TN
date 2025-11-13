<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\Cryptomoney;
use Illuminate\Support\Facades\DB;

class PortefeuilleService
{
    public function calculatePortfolioStats(string $walletId): array
    {
        $wallet = Wallet::with('cryptoWalletAssets.cryptomoney')->findOrFail($walletId);
        
        $totalInvested = 0.0;
        $currentValue = 0.0;
        
        foreach ($wallet->cryptoWalletAssets as $asset) {
            $invested = (float) $asset->avg_buy_price_eur * (float) $asset->quantity;
            $current = (float) ($asset->cryptomoney?->price_eur ?? 0) * (float) $asset->quantity;
            $totalInvested += $invested;
            $currentValue += $current;
        }
        
        $plusValue = $currentValue - $totalInvested;
        
        return [
            'total_invested_eur' => round($totalInvested, 2),
            'current_value_eur' => round($currentValue, 2),
            'plus_value_eur' => round($plusValue, 2),
        ];
    }

    public function calculatePlusValue(string $walletId): array
    {
        return $this->calculatePortfolioStats($walletId);
    }

    public function getPortfolioHistory(string $walletId): array
    {
        // Placeholder for history logic. Adjust to your schema.
        return [
            ['date' => now()->subDays(3)->toDateString(), 'value_eur' => 1000.00],
            ['date' => now()->subDays(2)->toDateString(), 'value_eur' => 1050.50],
            ['date' => now()->subDay()->toDateString(), 'value_eur' => 1035.20],
        ];
    }

    public function getPortfolioDetails(string $walletId): array
    {
        $wallet = Wallet::with(['cryptoWalletAssets.cryptomoney', 'transactions.cryptomoney'])->findOrFail($walletId);

        $positions = $wallet->cryptoWalletAssets->map(function ($asset) {
            return [
                'symbol' => $asset->cryptomoney?->symbol,
                'name' => $asset->cryptomoney?->name,
                'quantity' => (float) $asset->quantity,
                'avg_buy_price_eur' => (float) $asset->avg_buy_price_eur,
                'current_price_eur' => (float) ($asset->cryptomoney?->price_eur ?? 0),
                'current_value_eur' => round((float) $asset->quantity * (float) ($asset->cryptomoney?->price_eur ?? 0), 2),
            ];
        })->values()->all();

        $transactions = $wallet->transactions->map(function ($t) {
            return [
                'id' => $t->id,
                'type' => $t->type,
                'symbol' => $t->cryptomoney?->symbol,
                'quantity' => (float) $t->quantity,
                'unit_price_eur' => (float) $t->price_eur,
                'total_eur' => round((float) $t->quantity * (float) $t->price_eur, 2),
                'status' => $t->cancelled_at ? 'CANCELLED' : 'CONFIRMED',
                'date' => optional($t->created_at)->toDateTimeString(),
            ];
        })->values()->all();

        return [
            'id' => $wallet->id,
            'balance_eur' => (float) $wallet->balance_eur,
            'positions' => $positions,
            'transactions' => $transactions,
        ];
    }
}
