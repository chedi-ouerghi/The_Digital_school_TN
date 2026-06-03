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
        
        $totalInvested = $wallet->cryptoWalletAssets
            ->filter(fn($asset) => (float)$asset->quantity > 0)
            ->sum(fn($asset) => (float)$asset->average_buy_price * (float)$asset->quantity);

        $currentValue = $wallet->cryptoWalletAssets
            ->filter(fn($asset) => (float)$asset->quantity > 0)
            ->sum(fn($asset) => (float)($asset->cryptomoney?->price_eur ?? 0) * (float)$asset->quantity);
        
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
                    'avg_buy_price_eur' => round((float) $asset->average_buy_price, 8),
                    'current_price_eur' => round((float) ($asset->cryptomoney?->price_eur ?? 0), 8),
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
                $transactions = Transaction::where('crypto_wallet_asset_id', $asset->id)
                    ->where('created_at', '<=', $date->endOfDay())
                    ->whereNull('cancelled_at')
                    ->get();

                $quantity = 0;
                foreach ($transactions as $transaction) {
                    if (in_array($transaction->type, ['ACHAT', 'BUY'], true)) {
                        $quantity += (float)$transaction->quantity;
                    } elseif (in_array($transaction->type, ['VENTE', 'SELL'], true)) {
                        $quantity -= (float)$transaction->quantity;
                    }
                }

                if ($quantity > 0) {
                    // Récupère le prix historique le plus récent avant ou à cette date
                    $history = CryptoHistory::where('cryptomoney_id', $asset->cryptomoney_id)
                        ->where('recorded_at', '<=', $date->endOfDay())
                        ->orderBy('recorded_at', 'desc')
                        ->first();
                    
                    // Utilise le prix historique trouvé, sinon le prix actuel de la cryptomonnaie
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
                    'avg_buy_price_eur' => round($avgBuyPrice, 8),
                    'current_price_eur' => round($currentPrice, 8),
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
                                'unit_price_eur' => round((float) $t->price, 8),
                                'total_eur' => round((float) $t->quantity * (float) $t->price, 2),
                                'date' => optional($t->created_at)->toDateTimeString(),
                            ];
                        })->values()->all(),
                ];
            })->values()->all();

        $totalInvested = round(
            $wallet->cryptoWalletAssets
                ->filter(fn($a) => (float)$a->quantity > 0)
                ->sum(fn($a) => (float)$a->average_buy_price * (float)$a->quantity),
            2
        );
        $totalCurrentValue = round(
            $wallet->cryptoWalletAssets
                ->filter(fn($a) => (float)$a->quantity > 0)
                ->sum(fn($a) => (float)($a->cryptomoney?->price_eur ?? 0) * (float)$a->quantity),
            2
        );

        $totalUnits = 0;
        $buyCount = 0;
        foreach ($positions as $position) {
            $totalUnits += $position['quantity'];
            foreach ($position['transactions'] as $transaction) {
                if ($transaction['type'] === 'ACHAT') {
                    $buyCount++;
                }
            }
        }

    

        return [
            'totalValue' => $totalCurrentValue,
            'totalInvestment' => $totalInvested,
            'totalPlusValue' => round($totalCurrentValue - $totalInvested, 2),
            'totalPlusValuePercent' => $totalInvested > 0 ? round((($totalCurrentValue - $totalInvested) / $totalInvested) * 100, 2) : 0,
            'assets' => $positions,
            'totalUnits' => $totalUnits,
            'buyCount' => $buyCount,
        ];
    }

    public function getPortfolioPlusValue(string $walletId): array
    {
        $wallet = Wallet::with('cryptoWalletAssets.cryptomoney')->findOrFail($walletId);

        $assets = [];
        $totalInvested = 0.0;
        $totalCurrentValue = 0.0;

        foreach ($wallet->cryptoWalletAssets as $asset) {
            if ((float)$asset->quantity <= 0) {
                continue;
            }
            $quantity = (float)$asset->quantity;
            $avg = (float)$asset->average_buy_price;
            $price = (float)($asset->cryptomoney?->price_eur ?? 0);
            $invested = $avg * $quantity;
            $current = $price * $quantity;
            $pnl = $current - $invested;
            $pnlPct = $invested > 0 ? ($pnl / $invested) * 100 : 0;

            $assets[] = [
                'symbol' => $asset->cryptomoney?->symbol,
                'name' => $asset->cryptomoney?->name,
                'quantity' => $quantity,
                'avg_buy_price_eur' => round($avg, 8),
                'current_price_eur' => round($price, 8),
                'invested_eur' => round($invested, 2),
                'current_value_eur' => round($current, 2),
                'plus_value_eur' => round($pnl, 2),
                'plus_value_percent' => round($pnlPct, 2),
            ];

            $totalInvested += $invested;
            $totalCurrentValue += $current;
        }

        $totalPnl = $totalCurrentValue - $totalInvested;
        $totalPct = $totalInvested > 0 ? ($totalPnl / $totalInvested) * 100 : 0;

        return [
            'total_invested' => round($totalInvested, 2),
            'total_current_value' => round($totalCurrentValue, 2),
            'total_plus_value_eur' => round($totalPnl, 2),
            'total_plus_value_percent' => round($totalPct, 2),
            'assets' => $assets,
        ];
    }

/**
 * Get transaction history for a wallet, with optional type filter.
 *
 * @param string $walletId
 * @param string|null $type
 * @return array
 */
public function getTransactionsHistory(string $walletId, ?string $type = null): array
{
    $wallet = Wallet::with([
        'cryptoWalletAssets.cryptomoney',
        'cryptoWalletAssets.transactions' => function ($query) use ($type) {
            if ($type) {
                $query->where('type', $type);
            }
            $query->whereNull('cancelled_at')->orderBy('created_at', 'desc');
        }
    ])->findOrFail($walletId);

    $allTransactions = [];

    foreach ($wallet->cryptoWalletAssets as $asset) {
        foreach ($asset->transactions as $transaction) {
            $allTransactions[] = [
                    'id' => $transaction->id,
                    'crypto_id' => $asset->cryptomoney->id,
                    'crypto_symbol' => $asset->cryptomoney->symbol,
                    'crypto_name' => $asset->cryptomoney->name,
                    'crypto_image' => $asset->cryptomoney->image,
                    'crypto_image_url' => $asset->cryptomoney->image_url,
                    'type' => $transaction->type,
                    'quantity' => (float) $transaction->quantity,
                    'price' => (float) $transaction->price,
                    'unit_price_eur' => (float) $transaction->price,
                    'total_eur' => round((float) $transaction->quantity * (float) $transaction->price, 2),
                    'date' => optional($transaction->created_at)->toDateTimeString(),
            ];
        }
    }

    // Sort all transactions by date in descending order
    usort($allTransactions, function ($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    return $allTransactions;
}
}
