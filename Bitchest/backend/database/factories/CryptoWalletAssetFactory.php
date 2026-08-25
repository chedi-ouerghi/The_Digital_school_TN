<?php

namespace Database\Factories;

use App\Models\CryptoWalletAsset;
use App\Models\Wallet;
use App\Models\Cryptomoney;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CryptoWalletAsset>
 */
class CryptoWalletAssetFactory extends Factory
{
    protected $model = CryptoWalletAsset::class;

    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory(),
            'cryptomoney_id' => Cryptomoney::factory(),
            'quantity' => fake()->randomFloat(8, 0.001, 10),
            'average_buy_price' => fake()->randomFloat(8, 0.01, 5000),
        ];
    }

    public function forWalletAndCrypto(string $walletId, string $cryptoId): static
    {
        return $this->state(fn () => ['wallet_id' => $walletId, 'cryptomoney_id' => $cryptoId]);
    }
}