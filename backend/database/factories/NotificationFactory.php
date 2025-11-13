<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'message' => $this->faker->paragraph(2),
            'type' => $this->faker->randomElement([
                Notification::TYPE_ACCOUNT_REQUEST,
                Notification::TYPE_TRANSACTION,
                Notification::TYPE_PRICE_UPDATE,
                Notification::TYPE_ADMIN_ACTION
            ]),
            'is_read' => $this->faker->boolean(20), // 20% chance of being read
        ];
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_read' => true,
            ];
        });
    }

    /**
     * Indicate that the notification is unread.
     */
    public function unread(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'is_read' => false,
            ];
        });
    }

    /**
     * Set the notification type to account request.
     */
    public function accountRequest(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Notification::TYPE_ACCOUNT_REQUEST,
            ];
        });
    }

    /**
     * Set the notification type to transaction.
     */
    public function transaction(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Notification::TYPE_TRANSACTION,
            ];
        });
    }

    /**
     * Set the notification type to price update.
     */
    public function priceUpdate(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Notification::TYPE_PRICE_UPDATE,
            ];
        });
    }

    /**
     * Set the notification type to admin action.
     */
    public function adminAction(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => Notification::TYPE_ADMIN_ACTION,
            ];
        });
    }
}