<?php

namespace App\Services;

use App\Helpers\DecimalMath;
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
        string $quantity
    ): string {
        // SECURISER - Etape 5 : toutes les modifications (solde, quantite et
        // historique) sont atomiques. En cas d'erreur, DB annule tout.
        return DB::transaction(function () use ($user, $symbol, $type, $quantity) {
            // VERIFIER - Etape 6 : la quantite et le prix doivent etre positifs
            // avant tout calcul financier.
            if (DecimalMath::compare($quantity, '0') <= 0) {
                throw new \Exception('Quantity must be positive');
            }

            $crypto = Cryptomoney::where('symbol', $symbol)->firstOrFail();

            // SECURISER - Etape 7 : le verrou empeche deux transactions
            // simultanees de lire puis depenser le meme solde.
            $wallet = $user->wallets()->lockForUpdate()->first();
            if (!$wallet) {
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'balance_eur' => '0',
                ]);
            }

            $currentPrice = (string)$crypto->price_eur;
            //  Validation du prix
            if (DecimalMath::compare($currentPrice, '0') <= 0) {
                throw new \Exception('Invalid crypto price');
            }
            
            $totalAmount = DecimalMath::multiply($quantity, $currentPrice);

            $assetQuery = CryptoWalletAsset::where('wallet_id', $wallet->id)
                ->where('cryptomoney_id', $crypto->id);

            $asset = $type === 'VENTE'
                ? $assetQuery->lockForUpdate()->first()
                : $assetQuery->firstOrNew([
                    'wallet_id' => $wallet->id,
                    'cryptomoney_id' => $crypto->id,
                ]);

            if ($type === 'ACHAT') {
                // VERIFIER - Etape 8 : un achat n'est autorise que si le solde
                // EUR couvre le montant total de l'operation.
                if (DecimalMath::compare((string)$wallet->balance_eur, $totalAmount) < 0) {
                    throw new \Exception('Insufficient balance for this transaction');
                }

                $wallet->balance_eur = DecimalMath::subtract((string)$wallet->balance_eur, $totalAmount, 2);
                $wallet->save();

                $oldQuantity = (string)($asset->quantity ?? '0');
                $oldPrice = (string)($asset->average_buy_price ?? '0');
                $newQuantity = DecimalMath::add($oldQuantity, $quantity);

                $asset->quantity = $newQuantity;
                $weightedOldValue = DecimalMath::multiply($oldQuantity, $oldPrice);
                $weightedNewValue = DecimalMath::multiply($quantity, $currentPrice);
                $asset->average_buy_price = DecimalMath::divide(
                    DecimalMath::add($weightedOldValue, $weightedNewValue),
                    $newQuantity
                );
                $asset->save();

            } elseif ($type === 'VENTE') {
                // VERIFIER - Etape 9 : une vente n'est autorisee que si le
                // portefeuille contient la quantite demandee.
                if (!$asset->exists || DecimalMath::compare((string)$asset->quantity, $quantity) < 0) {
                    throw new \Exception('Insufficient quantity for this sale');
                }
                
                // Validate EUR balance after sale (to prevent negative balance)
                $newBalance = DecimalMath::add((string)$wallet->balance_eur, $totalAmount, 2);
                if (DecimalMath::compare($newBalance, '0', 2) < 0) {
                    throw new \Exception('Invalid EUR balance after this sale');
                }

                $wallet->balance_eur = $newBalance;
                $wallet->save();

                $newQuantity = DecimalMath::subtract((string)$asset->quantity, $quantity);
                // Conserver l'asset même à 0 pour l'historique des transactions
                $asset->quantity = DecimalMath::compare($newQuantity, '0') > 0 ? $newQuantity : '0';
                
                // Recalculate average buy price after a sale
                // The average price remains unchanged for partial sales, as it represents the cost of remaining assets.
                // If quantity becomes zero, average buy price is set to zero.
                if (DecimalMath::compare($newQuantity, '0') <= 0) {
                    $asset->average_buy_price = '0';
                }
                // For partial sales, average_buy_price remains the same (cost of remaining assets)
                
                $asset->save();
            }

            // TRACER - Etape 10 : conserver le type, la quantite, le prix et
            // le total permet de reconstituer l'operation dans l'historique.
            Transaction::create([
                'crypto_wallet_asset_id' => $asset->id,
                'cryptomoney_id' => $crypto->id,
                'type' => $type,
                'quantity' => $quantity,
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
    public function creditInitialBalance(User $user, string $amount): void
    {
        DB::transaction(function () use ($user, $amount) {
            $wallet = $user->wallets()->first();
            if (!$wallet) {
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'balance_eur' => '0',
                ]);
            }
            $wallet->balance_eur = $amount;
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
                $wallet->balance_eur = DecimalMath::add((string)$wallet->balance_eur, (string)$transaction->total_eur, 2);
                $wallet->save();

                if ($asset && $asset->exists) {
                    $newQuantity = DecimalMath::subtract((string)$asset->quantity, (string)$transaction->quantity);
                    $asset->quantity = DecimalMath::compare($newQuantity, '0') > 0 ? $newQuantity : '0';
                    $asset->save();
                }
            } else { // VENTE: retirer l'argent et rendre les cryptos
                $wallet->balance_eur = DecimalMath::subtract((string)$wallet->balance_eur, (string)$transaction->total_eur, 2);
                $wallet->save();

                if ($asset) {
                    $asset->quantity = DecimalMath::add((string)$asset->quantity, (string)$transaction->quantity);
                    // si pas d'avg, fixer à prix de la transaction
                    if (!$asset->average_buy_price) {
                        $asset->average_buy_price = (string)$transaction->price;
                    }
                    $asset->save();
                } else {
                    // Créer l'asset si inexistant
                    $asset = CryptoWalletAsset::create([
                        'wallet_id' => $wallet->id,
                        'cryptomoney_id' => $transaction->cryptomoney_id,
                        'quantity' => (string)$transaction->quantity,
                        'average_buy_price' => (string)$transaction->price,
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
                'new_balance' => (string)$wallet->balance_eur,
                'cancelled_transaction' => $transaction->id,
            ];
        });
    }
}
