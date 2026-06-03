<?php

namespace Database\Factories;

use App\Models\AccountRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccountRequest>
 */
class AccountRequestFactory extends Factory
{
    protected $model = AccountRequest::class;

    public function definition(): array
    {
        return [
            'id' => strtoupper(\Illuminate\Support\Str::random(14)),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'status' => 'pending',
        ];
    }
}