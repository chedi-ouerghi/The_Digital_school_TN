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
        $quantity = fake()->randomFloat(8, 0.001, 5);
        $price = fake()->randomFloat(8, 0.01, 5000);
        $total = bcmul((string) $quantity, (string) $price, 8);

        return [
            'crypto_wallet_asset_id' => CryptoWalletAsset::factory(),
            'cryptomoney_id' => null,
            'type' => $type,
            'quantity' => $quantity,
            'price' => $price,
            'total_eur' => $total,
        ];
    }

    public function achat(): static
    {
        return $this->state(fn () => ['type' => 'ACHAT']);
    }

    public function vente(): static
    {
        return $this->state(fn () => ['type' => 'VENTE']);
    }
}