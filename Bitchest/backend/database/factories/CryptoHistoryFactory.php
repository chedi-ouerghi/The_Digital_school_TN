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
            'price' => $this->faker->randomFloat(10, 0.01, 50000),
            'volume' => $this->faker->randomFloat(2, 100000, 2000000),
            'recorded_at' => $this->faker->dateTimeBetween('-39 days', 'now')->format('Y-m-d'),
        ];
    }

    /**
     * Forcer une date précise (utile pour tests d'unicité).
     */
    public function forDate(string $date): static
    {
        return $this->state(fn () => ['recorded_at' => $date]);
    }
}