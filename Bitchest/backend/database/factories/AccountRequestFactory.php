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
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'status' => 'pending',
        ];
    }
}