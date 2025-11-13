<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $notification;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email_verified_at' => now()
        ]);
        
        Sanctum::actingAs($this->user);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_list_user_notifications_successfully()
    {
        $notification1 = Notification::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Notification 1',
            'message' => 'Message 1',
            'is_read' => false
        ]);

        $notification2 = Notification::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Notification 2',
            'message' => 'Message 2',
            'is_read' => true
        ]);

        $response = $this->getJson('/api/v1/notifications');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment([
                'title' => 'Notification 1',
                'message' => 'Message 1',
                'is_read' => false
            ])
            ->assertJsonFragment([
                'title' => 'Notification 2',
                'message' => 'Message 2',
                'is_read' => true
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_empty_array_when_no_notifications()
    {
        $response = $this->getJson('/api/v1/notifications');

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_only_returns_notifications_for_authenticated_user()
    {
        $otherUser = User::factory()->create();
        
        Notification::factory()->create([
            'user_id' => $otherUser->id,
            'title' => 'Other user notification',
            'message' => 'Should not be visible'
        ]);

        Notification::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'User notification',
            'message' => 'Should be visible'
        ]);

        $response = $this->getJson('/api/v1/notifications');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'title' => 'User notification',
                'message' => 'Should be visible'
            ])
            ->assertJsonMissing([
                'title' => 'Other user notification',
                'message' => 'Should not be visible'
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_401_for_unauthenticated_users()
    {
        // Clear authentication completely
        $this->app['auth']->guard('sanctum')->forgetUser();

        $response = $this->getJson('/api/v1/notifications');

        $response->assertStatus(401);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_mark_notification_as_read_successfully()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Notification',
            'message' => 'Test Message',
            'is_read' => false
        ]);

        $response = $this->putJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Notification marquée comme lue.'
            ]);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_403_when_trying_to_mark_other_users_notification_as_read()
    {
        $otherUser = User::factory()->create();
        $otherNotification = Notification::factory()->create([
            'user_id' => $otherUser->id,
            'title' => 'Other user notification',
            'message' => 'Should not be accessible',
            'is_read' => false
        ]);

        $response = $this->putJson("/api/v1/notifications/{$otherNotification->id}/read");

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Accès refusé.'
            ]);

        // Verify notification is still unread
        $this->assertDatabaseHas('notifications', [
            'id' => $otherNotification->id,
            'is_read' => false
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_404_when_notification_not_found()
    {
        $nonExistentId = 999999;

        $response = $this->putJson("/api/v1/notifications/{$nonExistentId}/read");

        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_mark_already_read_notification_as_read_again()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Notification',
            'message' => 'Test Message',
            'is_read' => true
        ]);

        $response = $this->putJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Notification marquée comme lue.'
            ]);

        // Verify it's still read
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_orders_notifications_by_created_at_desc()
    {
        $oldNotification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Old Notification',
            'created_at' => now()->subDays(2)
        ]);

        $newNotification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'New Notification',
            'created_at' => now()
        ]);

        $response = $this->getJson('/api/v1/notifications');

        $response->assertStatus(200);
        
        $notifications = $response->json();
        $this->assertEquals('New Notification', $notifications[0]['title']);
        $this->assertEquals('Old Notification', $notifications[1]['title']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_401_for_mark_as_read_when_unauthenticated()
    {
        // Clear authentication completely
        $this->app['auth']->guard('sanctum')->forgetUser();
        
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Notification',
            'is_read' => false
        ]);

        $response = $this->putJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertStatus(401);
    }
}