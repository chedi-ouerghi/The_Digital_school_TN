<?php

namespace Database\Factories;

use App\Models\Cryptomoney;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cryptomoney>
 */
class CryptomoneyFactory extends Factory
{
    protected $model = Cryptomoney::class;

    public function definition(): array
    {
        return [
            'id' => strtoupper(\Illuminate\Support\Str::random(14)),
            'name' => fake()->word(),
            'symbol' => fake()->unique()->regexify('[A-Z]{3,5}'),
            'price_eur' => fake()->randomFloat(2, 0.01, 100000),
            'image' => null,
            'category' => null,
            'website' => null,
            'market_cap' => fake()->randomFloat(2, 0.01, 100000000),
            'change_24h_pct' => fake()->randomFloat(2, -100, 100),
        ];
    }
}