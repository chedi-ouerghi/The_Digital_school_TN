<?php

namespace Database\Factories;

use App\Models\CryptoHistory;
use App\Models\Cryptomoney;
use Illuminate\Database\Eloquent\Factories\Factory;

class CryptoHistoryFactory extends Factory
{
    protected $model = CryptoHistory::class;

    public function definition(): array
    {
        return [
            'cryptomoney_id' => Cryptomoney::factory(),
            'price' => $this->faker->randomFloat(8, 1000, 100000),
            'market_cap' => $this->faker->randomFloat(8, 1000000000, 1000000000000),
            'volume' => $this->faker->randomFloat(8, 100000000, 10000000000),
            'recorded_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}