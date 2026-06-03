<?php

namespace App\Services;

use App\Models\User;
use App\Models\Cryptomoney;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\CryptoWalletAsset;
use Illuminate\Support\Facades\DB;
use App\Jobs\DeleteTransactionJob;

class TransactionService
{
    /**
     * Gère une transaction d'achat ou de vente de crypto pour un utilisateur.
     * Par défaut utilise le premier wallet de l'utilisateur (ou en crée un si absent).
     */
    public function handleTransaction(
        User $user,
        string $symbol,
        string $type,
        float $quantity
    ): string {
        return DB::transaction(function () use ($user, $symbol, $type, $quantity) {
            // CORRECTION: Validations complètes dès le départ
            if ($quantity <= 0) {
                throw new \Exception('Quantity must be positive');
            }

            $crypto = Cryptomoney::where('symbol', $symbol)->firstOrFail();

            $wallet = $user->wallets()->first();
            if (!$wallet) {
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'balance_eur' => 0,
                ]);
            }

            $currentPrice = (float)$crypto->price_eur;
            // CORRECTION: Validation du prix
            if ($currentPrice <= 0) {
                throw new \Exception('Invalid crypto price');
            }
            
            $totalAmount = (float)$quantity * $currentPrice;

            $asset = CryptoWalletAsset::firstOrNew([
                'wallet_id' => $wallet->id,
                'cryptomoney_id' => $crypto->id,
            ]);

            if ($type === 'ACHAT') {
                if ((float)$wallet->balance_eur < $totalAmount) {
                    throw new \Exception('Insufficient balance for this transaction');
                }

                $wallet->balance_eur = (float)$wallet->balance_eur - $totalAmount;
                $wallet->save();

                $oldQuantity = (float)($asset->quantity ?? 0);
                $oldPrice = (float)($asset->average_buy_price ?? 0);
                $newQuantity = $oldQuantity + (float)$quantity;

                $asset->quantity = $newQuantity;
                $asset->average_buy_price = $newQuantity > 0
                    ? (($oldQuantity * $oldPrice) + ((float)$quantity * $currentPrice)) / $newQuantity
                    : 0;
                $asset->save();

            } elseif ($type === 'VENTE') {
                if (!$asset->exists || (float)$asset->quantity < (float)$quantity) {
                    throw new \Exception('Insufficient quantity for this sale');
                }
                
                // Validate EUR balance after sale (to prevent negative balance)
                $newBalance = (float)$wallet->balance_eur + $totalAmount;
                if ($newBalance < 0) {
                    throw new \Exception('Invalid EUR balance after this sale');
                }

                $wallet->balance_eur = $newBalance;
                $wallet->save();

                $newQuantity = (float)$asset->quantity - (float)$quantity;
                // Conserver l'asset même à 0 pour l'historique des transactions
                $asset->quantity = max(0, $newQuantity);
                
                // Recalculate average buy price after a sale
                // The average price remains unchanged for partial sales, as it represents the cost of remaining assets.
                // If quantity becomes zero, average buy price is set to zero.
                if ($newQuantity <= 0) {
                    $asset->average_buy_price = 0;
                }
                // For partial sales, average_buy_price remains the same (cost of remaining assets)
                
                $asset->save();
            }

            Transaction::create([
                'crypto_wallet_asset_id' => $asset->id,
                'cryptomoney_id' => $crypto->id,
                'type' => $type,
                'quantity' => (float)$quantity,
                'price' => $currentPrice,
                'total_eur' => $totalAmount,
            ]);

            return 'Transaction successful.';
        });
    }

    /**
     * Créditer le solde initial (ou ajouter un crédit) à un utilisateur.
     * Si aucun wallet n'existe, en créer un.
     */
    public function creditInitialBalance(User $user, float $amount): void
    {
        DB::transaction(function () use ($user, $amount) {
            $wallet = $user->wallets()->first();
            if (!$wallet) {
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'balance_eur' => 0,
                ]);
            }
            $wallet->balance_eur = (float)$amount;
            $wallet->save();
        });
    }

    /**
     * Annuler une transaction.
     */
    public function cancelTransaction(Transaction $transaction, string $reason): array
    {
        return DB::transaction(function () use ($transaction, $reason) {
            $asset = $transaction->cryptoWalletAsset;
            $wallet = $asset ? $asset->wallet : null;

            if (!$wallet) {
                throw new \Exception('Wallet not found for this transaction');
            }

            if ($transaction->type === 'ACHAT') {
                // Rendre l'argent et retirer les cryptos
                $wallet->balance_eur = (float)$wallet->balance_eur + (float)$transaction->total_eur;
                $wallet->save();

                if ($asset && $asset->exists) {
                    $newQuantity = (float)$asset->quantity - (float)$transaction->quantity;
                    $asset->quantity = max(0, $newQuantity);
                    $asset->save();
                }
            } else { // VENTE: retirer l'argent et rendre les cryptos
                $wallet->balance_eur = (float)$wallet->balance_eur - (float)$transaction->total_eur;
                $wallet->save();

                if ($asset) {
                    $asset->quantity = (float)$asset->quantity + (float)$transaction->quantity;
                    // si pas d'avg, fixer à prix de la transaction
                    if (!$asset->average_buy_price) {
                        $asset->average_buy_price = (float)$transaction->price;
                    }
                    $asset->save();
                } else {
                    // Créer l'asset si inexistant
                    $asset = CryptoWalletAsset::create([
                        'wallet_id' => $wallet->id,
                        'cryptomoney_id' => $transaction->cryptomoney_id,
                        'quantity' => (float)$transaction->quantity,
                        'average_buy_price' => (float)$transaction->price,
                    ]);
                }
            }

            $transaction->update([
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
            ]);

            DeleteTransactionJob::dispatch($transaction->id)->delay(now()->addHour());

            return [
                'wallet_id' => $wallet->id,
                'new_balance' => (float)$wallet->balance_eur,
                'cancelled_transaction' => $transaction->id,
            ];
        });
    }
}
