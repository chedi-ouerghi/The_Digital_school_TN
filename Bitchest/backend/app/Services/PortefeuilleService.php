<?php

namespace App\Services;

use App\Helpers\DecimalMath;
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
        
        $totalInvested = '0';
        $currentValue = '0';
        foreach ($wallet->cryptoWalletAssets as $asset) {
            if (DecimalMath::compare((string)$asset->quantity, '0') <= 0) {
                continue;
            }
            $totalInvested = DecimalMath::add(
                $totalInvested,
                DecimalMath::multiply((string)$asset->average_buy_price, (string)$asset->quantity)
            );
            $currentValue = DecimalMath::add(
                $currentValue,
                DecimalMath::multiply((string)($asset->cryptomoney?->price_eur ?? '0'), (string)$asset->quantity)
            );
        }
        $plusValue = DecimalMath::subtract($currentValue, $totalInvested);
        
        return [
            'total_invested_eur' => DecimalMath::scale($totalInvested, 2),
            'current_value_eur' => DecimalMath::scale($currentValue, 2),
            'plus_value_eur' => DecimalMath::scale($plusValue, 2),
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
            if (DecimalMath::compare((string)$asset->quantity, '0') > 0) {
                $invested = DecimalMath::multiply((string)$asset->average_buy_price, (string)$asset->quantity);
                $current = DecimalMath::multiply((string)($asset->cryptomoney?->price_eur ?? '0'), (string)$asset->quantity);
                $plusValue = DecimalMath::subtract($current, $invested);
                $plusValuePercent = DecimalMath::compare($invested, '0') > 0
                    ? DecimalMath::multiply(DecimalMath::divide($plusValue, $invested, 18), '100', 4)
                    : '0';

                $result[] = [
                    'symbol' => $asset->cryptomoney?->symbol,
                    'name' => $asset->cryptomoney?->name,
                    'quantity' => (string) $asset->quantity,
                    'avg_buy_price_eur' => DecimalMath::scale((string)$asset->average_buy_price, 8),
                    'current_price_eur' => DecimalMath::scale((string)($asset->cryptomoney?->price_eur ?? '0'), 8),
                    'invested_eur' => DecimalMath::scale($invested, 2),
                    'current_value_eur' => DecimalMath::scale($current, 2),
                    'plus_value_eur' => DecimalMath::scale($plusValue, 2),
                    'plus_value_percent' => DecimalMath::scale($plusValuePercent, 2),
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
        $assets = $wallet->cryptoWalletAssets;
        $data = [];
        $today = Carbon::now();
        $endDate = $today->copy()->endOfDay();
        $assetIds = $assets->pluck('id');
        $cryptoIds = $assets->pluck('cryptomoney_id')->unique();

        // Load the complete range once; querying inside both loops causes a timeout.
        $transactionsByAsset = Transaction::whereIn('crypto_wallet_asset_id', $assetIds)
            ->where('created_at', '<=', $endDate)
            ->whereNull('cancelled_at')
            ->orderBy('created_at')
            ->get()
            ->groupBy('crypto_wallet_asset_id');

        $historiesByCrypto = CryptoHistory::whereIn('cryptomoney_id', $cryptoIds)
            ->where('recorded_at', '<=', $endDate)
            ->orderBy('recorded_at')
            ->get()
            ->groupBy('cryptomoney_id');
        
        for ($i = $days; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $value = '0';

            foreach ($assets as $asset) {
                // Calcule la quantité détenue à cette date
                $quantity = '0';
                foreach ($transactionsByAsset->get($asset->id, collect()) as $transaction) {
                    if ($transaction->created_at > $date->endOfDay()) {
                        break;
                    }
                    if (in_array($transaction->type, ['ACHAT', 'BUY'], true)) {
                        $quantity = DecimalMath::add($quantity, (string)$transaction->quantity);
                    } elseif (in_array($transaction->type, ['VENTE', 'SELL'], true)) {
                        $quantity = DecimalMath::subtract($quantity, (string)$transaction->quantity);
                    }
                }

                if (DecimalMath::compare($quantity, '0') > 0) {
                    // Récupère le prix historique le plus récent avant ou à cette date
                    $history = $historiesByCrypto
                        ->get($asset->cryptomoney_id, collect())
                        ->filter(fn ($item) => $item->recorded_at <= $date->endOfDay())
                        ->last();
                    
                    // Utilise le prix historique trouvé, sinon le prix actuel de la cryptomonnaie
                    $price = $history ? (string)$history->price : (string)($asset->cryptomoney?->price_eur ?? '0');
                    $value = DecimalMath::add($value, DecimalMath::multiply($quantity, $price));
                }
            }

            $data[] = [
                'date' => $date->toDateString(),
                'timestamp' => $date->timestamp * 1000,
                'value_eur' => DecimalMath::scale($value, 2),
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

        // Regroupement des actifs et transactions par cryptomonnaie
        $positions = $wallet->cryptoWalletAssets
            ->filter(fn($asset) => DecimalMath::compare((string)$asset->quantity, '0') > 0) // Filtre les assets avec quantité > 0
            ->map(function ($asset) {
                $currentPrice = (string) ($asset->cryptomoney?->price_eur ?? '0');
                $quantity = (string) $asset->quantity;
                $avgBuyPrice = (string) $asset->average_buy_price;
                $invested = DecimalMath::multiply($avgBuyPrice, $quantity);
                $current = DecimalMath::multiply($currentPrice, $quantity);
                $plusValue = DecimalMath::subtract($current, $invested);

                return [
                    'id' => $asset->id,
                    'symbol' => $asset->cryptomoney?->symbol,
                    'name' => $asset->cryptomoney?->name,
                    'quantity' => $quantity,
                    'avg_buy_price_eur' => DecimalMath::scale($avgBuyPrice, 8),
                    'current_price_eur' => DecimalMath::scale($currentPrice, 8),
                    'invested_eur' => DecimalMath::scale($invested, 2),
                    'current_value_eur' => DecimalMath::scale($current, 2),
                    'plus_value_eur' => DecimalMath::scale($plusValue, 2),
                    'plus_value_percent' => DecimalMath::compare($invested, '0') > 0
                        ? DecimalMath::scale(DecimalMath::multiply(DecimalMath::divide($plusValue, $invested), '100', 4), 2)
                        : '0',
                    // Transactions associées à la cryptomonnaie
                    'transactions' => $asset->transactions
                        ->filter(fn($t) => !$t->cancelled_at)
                        ->map(function ($t) {
                            return [
                                'id' => $t->id,
                                'type' => $t->type,
                                'quantity' => (string) $t->quantity,
                                'unit_price_eur' => DecimalMath::scale((string)$t->price, 8),
                                'total_eur' => DecimalMath::scale(DecimalMath::multiply((string)$t->quantity, (string)$t->price), 2),
                                'date' => optional($t->created_at)->toDateTimeString(),
                            ];
                        })->values()->all(),
                ];
            })->values()->all();

        $totalInvested = '0';
        $totalCurrentValue = '0';
        foreach ($wallet->cryptoWalletAssets as $asset) {
            if (DecimalMath::compare((string)$asset->quantity, '0') <= 0) {
                continue;
            }
            $totalInvested = DecimalMath::add($totalInvested, DecimalMath::multiply(
                (string)$asset->average_buy_price,
                (string)$asset->quantity
            ));
            $totalCurrentValue = DecimalMath::add($totalCurrentValue, DecimalMath::multiply(
                (string)($asset->cryptomoney?->price_eur ?? '0'),
                (string)$asset->quantity
            ));
        }

        $totalUnits = '0';
        $buyCount = 0;
        foreach ($positions as $position) {
            $totalUnits = DecimalMath::add($totalUnits, $position['quantity']);
            foreach ($position['transactions'] as $transaction) {
                if ($transaction['type'] === 'ACHAT') {
                    $buyCount++;
                }
            }
        }

    

        return [
            'totalValue' => $totalCurrentValue,
            'totalInvestment' => $totalInvested,
            'totalPlusValue' => DecimalMath::scale(DecimalMath::subtract($totalCurrentValue, $totalInvested), 2),
            'totalPlusValuePercent' => DecimalMath::compare($totalInvested, '0') > 0
                ? DecimalMath::scale(DecimalMath::multiply(DecimalMath::divide(DecimalMath::subtract($totalCurrentValue, $totalInvested), $totalInvested), '100', 4), 2)
                : '0',
            'assets' => $positions,
            'totalUnits' => $totalUnits,
            'buyCount' => $buyCount,
        ];
    }

    public function getPortfolioPlusValue(string $walletId): array
    {
        $wallet = Wallet::with('cryptoWalletAssets.cryptomoney')->findOrFail($walletId);

        $assets = [];
        $totalInvested = '0';
        $totalCurrentValue = '0';

        foreach ($wallet->cryptoWalletAssets as $asset) {
            if (DecimalMath::compare((string)$asset->quantity, '0') <= 0) {
                continue;
            }
            $quantity = (string)$asset->quantity;
            $avg = (string)$asset->average_buy_price;
            $price = (string)($asset->cryptomoney?->price_eur ?? '0');
            $invested = DecimalMath::multiply($avg, $quantity);
            $current = DecimalMath::multiply($price, $quantity);
            $pnl = DecimalMath::subtract($current, $invested);
            $pnlPct = DecimalMath::compare($invested, '0') > 0
                ? DecimalMath::multiply(DecimalMath::divide($pnl, $invested), '100', 4)
                : '0';

            $assets[] = [
                'symbol' => $asset->cryptomoney?->symbol,
                'name' => $asset->cryptomoney?->name,
                'quantity' => $quantity,
                'avg_buy_price_eur' => DecimalMath::scale($avg, 8),
                'current_price_eur' => DecimalMath::scale($price, 8),
                'invested_eur' => DecimalMath::scale($invested, 2),
                'current_value_eur' => DecimalMath::scale($current, 2),
                'plus_value_eur' => DecimalMath::scale($pnl, 2),
                'plus_value_percent' => DecimalMath::scale($pnlPct, 2),
            ];

            $totalInvested = DecimalMath::add($totalInvested, $invested);
            $totalCurrentValue = DecimalMath::add($totalCurrentValue, $current);
        }

        $totalPnl = DecimalMath::subtract($totalCurrentValue, $totalInvested);
        $totalPct = DecimalMath::compare($totalInvested, '0') > 0
            ? DecimalMath::multiply(DecimalMath::divide($totalPnl, $totalInvested), '100', 4)
            : '0';

        return [
            'total_invested' => DecimalMath::scale($totalInvested, 2),
            'total_current_value' => DecimalMath::scale($totalCurrentValue, 2),
            'total_plus_value_eur' => DecimalMath::scale($totalPnl, 2),
            'total_plus_value_percent' => DecimalMath::scale($totalPct, 2),
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
                    'quantity' => (string) $transaction->quantity,
                    'price' => (string) $transaction->price,
                    'unit_price_eur' => (string) $transaction->price,
                    'total_eur' => DecimalMath::scale(DecimalMath::multiply((string)$transaction->quantity, (string)$transaction->price), 2),
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
