<?php

namespace App\Services;

use App\Models\Wallet;
use App\Models\Cryptomoney;
use App\Models\CryptoHistory;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PortefeuilleService
{
    /**
     * CORRECTION: Calcule les stats du portefeuille avec les bonnes colonnes
     */
    public function calculatePortfolioStats(string $walletId): array
    {
        $wallet = Wallet::with('cryptoWalletAssets.cryptomoney')->findOrFail($walletId);
        
        $totalInvested = 0.0;
        $currentValue = 0.0;
        
        foreach ($wallet->cryptoWalletAssets as $asset) {
            // CORRECTION: Utilise 'average_buy_price' au lieu de 'avg_buy_price_eur'
            $invested = (float) $asset->average_buy_price * (float) $asset->quantity;
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

    /**
     * CORRECTION: Calcule la plus-value par cryptomonnaie
     */
    public function calculatePlusValueByCrypto(string $walletId): array
    {
        $wallet = Wallet::with('cryptoWalletAssets.cryptomoney')->findOrFail($walletId);
        
        $result = [];
        
        foreach ($wallet->cryptoWalletAssets as $asset) {
            if ((float)$asset->quantity > 0) {
                $invested = (float) $asset->average_buy_price * (float) $asset->quantity;
                $current = (float) ($asset->cryptomoney?->price_eur ?? 0) * (float) $asset->quantity;
                $plusValue = $current - $invested;
                $plusValuePercent = $invested > 0 ? ($plusValue / $invested) * 100 : 0;

                $result[] = [
                    'symbol' => $asset->cryptomoney?->symbol,
                    'name' => $asset->cryptomoney?->name,
                    'quantity' => (float) $asset->quantity,
                    'avg_buy_price_eur' => (float) $asset->average_buy_price,
                    'current_price_eur' => (float) ($asset->cryptomoney?->price_eur ?? 0),
                    'invested_eur' => round($invested, 2),
                    'current_value_eur' => round($current, 2),
                    'plus_value_eur' => round($plusValue, 2),
                    'plus_value_percent' => round($plusValuePercent, 2),
                ];
            }
        }
        
        return $result;
    }

    /**
     * CORRECTION: Implémente l'historique réel du portefeuille
     * Calcule la valeur du portefeuille jour par jour basé sur les transactions et prix historiques
     */
    public function getPortfolioHistory(string $walletId, int $days = 30): array
    {
        $wallet = Wallet::with('cryptoWalletAssets')->findOrFail($walletId);
        
        $data = [];
        $today = Carbon::now();
        
        for ($i = $days; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $value = 0.0;

            // Pour chaque asset du portefeuille
            foreach ($wallet->cryptoWalletAssets as $asset) {
                // Calcule la quantité détenue à cette date
                $quantity = Transaction::where('crypto_wallet_asset_id', $asset->id)
                    ->where('created_at', '<=', $date->endOfDay())
                    ->whereNull('cancelled_at') // Exclut les transactions annulées
                    ->get()
                    ->reduce(function ($carry, $transaction) {
                        return $transaction->type === 'ACHAT'
                            ? $carry + (float)$transaction->quantity
                            : $carry - (float)$transaction->quantity;
                    }, 0);

                if ($quantity > 0) {
                    // Récupère le prix historique pour cette date
                    $history = CryptoHistory::where('cryptomoney_id', $asset->cryptomoney_id)
                        ->where('recorded_at', '<=', $date->endOfDay())
                        ->orderBy('recorded_at', 'desc')
                        ->first();
                    
                    $price = $history ? (float)$history->price : (float)($asset->cryptomoney?->price_eur ?? 0);
                    $value += $quantity * $price;
                }
            }

            $data[] = [
                'date' => $date->toDateString(),
                'timestamp' => $date->timestamp * 1000,
                'value_eur' => round($value, 2),
            ];
        }

        return $data;
    }

    /**
     * CORRECTION: Structure de données complète et cohérente
     * Retourne une hiérarchie par cryptomonnaie avec liste des achats/ventes
     */
    public function getPortfolioDetails(string $walletId): array
    {
        $wallet = Wallet::with([
            'cryptoWalletAssets.cryptomoney',
            'cryptoWalletAssets.transactions'
        ])->findOrFail($walletId);

        // CORRECTION: Restructure par cryptomonnaie
        $positions = $wallet->cryptoWalletAssets
            ->filter(fn($asset) => (float)$asset->quantity > 0) // Filtre les assets avec quantité > 0
            ->map(function ($asset) {
                $currentPrice = (float) ($asset->cryptomoney?->price_eur ?? 0);
                $quantity = (float) $asset->quantity;
                $avgBuyPrice = (float) $asset->average_buy_price;
                $invested = $avgBuyPrice * $quantity;
                $current = $currentPrice * $quantity;
                $plusValue = $current - $invested;

                return [
                    'id' => $asset->id,
                    'symbol' => $asset->cryptomoney?->symbol,
                    'name' => $asset->cryptomoney?->name,
                    'quantity' => $quantity,
                    'avg_buy_price_eur' => $avgBuyPrice,
                    'current_price_eur' => $currentPrice,
                    'invested_eur' => round($invested, 2),
                    'current_value_eur' => round($current, 2),
                    'plus_value_eur' => round($plusValue, 2),
                    'plus_value_percent' => $invested > 0 ? round(($plusValue / $invested) * 100, 2) : 0,
                    // CORRECTION: Liste des transactions par crypto
                    'transactions' => $asset->transactions
                        ->filter(fn($t) => !$t->cancelled_at)
                        ->map(function ($t) {
                            return [
                                'id' => $t->id,
                                'type' => $t->type,
                                'quantity' => (float) $t->quantity,
                                'unit_price_eur' => (float) $t->price,
                                'total_eur' => round((float) $t->quantity * (float) $t->price, 2),
                                'date' => optional($t->created_at)->toDateTimeString(),
                            ];
                        })->values()->all(),
                ];
            })->values()->all();

        return [
            'id' => $wallet->id,
            'balance_eur' => (float) $wallet->balance_eur,
            'total_invested_eur' => round(
                $wallet->cryptoWalletAssets->sum(fn($a) => (float)$a->average_buy_price * (float)$a->quantity),
                2
            ),
            'total_current_value_eur' => round(
                $wallet->cryptoWalletAssets->sum(fn($a) => (float)($a->cryptomoney?->price_eur ?? 0) * (float)$a->quantity),
                2
            ),
            'positions' => $positions,
        ];
    }
}
