<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Cryptomoney;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CryptoWalletAsset;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $types = ['ACHAT', 'VENTE'];
        $type = fake()->randomElement($types);
        $quantity = fake()->randomFloat(8, 0.00000001, 10);
        $price = fake()->randomFloat(2, 0.01, 100000);
        $total = $quantity * $price;

        return [
            'crypto_wallet_asset_id' => CryptoWalletAsset::factory(),
            'cryptomoney_id' => Cryptomoney::factory(),
            'type' => $type,
            'quantity' => $quantity,
            'price' => $price,
            'total_eur' => $total,
        ];
    }
}