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
                throw new \Exception('La quantité doit être positive');
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
                throw new \Exception('Le prix de la crypto est invalide');
            }
            
            $totalAmount = (float)$quantity * $currentPrice;

            $asset = CryptoWalletAsset::firstOrNew([
                'wallet_id' => $wallet->id,
                'cryptomoney_id' => $crypto->id,
            ]);

            if ($type === 'ACHAT') {
                if ((float)$wallet->balance_eur < $totalAmount) {
                    throw new \Exception('Solde insuffisant pour cette transaction');
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
                    throw new \Exception('Quantité insuffisante pour cette vente');
                }
                
                // CORRECTION: Validation du solde EUR après la vente (pour éviter solde négatif)
                $newBalance = (float)$wallet->balance_eur + $totalAmount;
                if ($newBalance < 0) {
                    throw new \Exception('Solde EUR invalide après cette vente');
                }

                $wallet->balance_eur = $newBalance;
                $wallet->save();

                $newQuantity = (float)$asset->quantity - (float)$quantity;
                // Conserver l'asset même à 0 pour l'historique des transactions
                $asset->quantity = max(0, $newQuantity);
                
                // CORRECTION: Recalcul du prix moyen d'achat après une vente
                // Le prix moyen ne change que si la quantité restante est > 0
                if ($newQuantity <= 0) {
                    $asset->average_buy_price = 0;
                }
                // Sinon, le prix moyen reste inchangé (on vend au prix moyen courant)
                
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

            return 'Transaction réussie.';
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
                throw new \Exception('Wallet non trouvé pour cette transaction');
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
            } else {
                // VENTE: retirer l'argent et rendre les cryptos
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
