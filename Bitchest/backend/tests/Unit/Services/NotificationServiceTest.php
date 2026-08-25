<?php

namespace Tests\Unit\Services;

use App\Models\CryptoWalletAsset;
use App\Models\Cryptomoney;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_notification_is_idempotent(): void
    {
        $user = User::factory()->create(['role' => 'CLIENT']);
        $service = app(NotificationService::class);

        $service->createWelcome($user);
        $service->createWelcome($user);

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => Notification::TYPE_WELCOME,
            'is_read' => false,
        ]);
    }

    public function test_price_change_notifies_only_holders_and_is_idempotent(): void
    {
        $holder = User::factory()->create(['role' => 'CLIENT']);
        $otherClient = User::factory()->create(['role' => 'CLIENT']);
        $crypto = Cryptomoney::factory()->create(['name' => 'Bitcoin', 'symbol' => 'BTC']);
        CryptoWalletAsset::factory()->create([
            'wallet_id' => $holder->wallets()->create(['balance_eur' => 500])->id,
            'cryptomoney_id' => $crypto->id,
        ]);
        $service = app(NotificationService::class);

        $service->createPriceChange($crypto, '90000.00', '95000.00');
        $service->createPriceChange($crypto, '90000.00', '95000.00');
        $service->createPriceChange($crypto, '95000.00', '95000.00');

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $holder->id,
            'type' => Notification::TYPE_PRICE_CHANGE,
        ]);
        $this->assertDatabaseMissing('notifications', ['user_id' => $otherClient->id]);
    }

    public function test_mark_all_sets_read_at_for_owned_notifications(): void
    {
        $user = User::factory()->create();
        Notification::factory()->count(3)->create(['user_id' => $user->id, 'is_read' => false]);
        $service = app(NotificationService::class);

        $this->assertSame(3, $service->getUnreadCount($user));
        $this->assertSame(3, $service->markAllAsRead($user));
        $this->assertSame(0, $service->getUnreadCount($user));
        $this->assertSame(3, Notification::where('user_id', $user->id)->whereNotNull('read_at')->count());
    }
}
