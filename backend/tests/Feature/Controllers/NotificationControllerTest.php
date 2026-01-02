<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test get notifications for authenticated user
     */
    public function test_get_notifications_authenticated()
    {
        $user = User::factory()->create();
        Notification::factory()->count(3)->create(['user_id' => $user->id]);
        
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'user_id', 'title', 'message', 'is_read']
            ]);
    }

    /**
     * Test get notifications without authentication
     */
    public function test_get_notifications_without_authentication()
    {
        $response = $this->getJson('/api/v1/notifications');

        $response->assertStatus(401);
    }

    /**
     * Test get empty notifications list
     */
    public function test_get_empty_notifications()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/notifications');

        $response->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * Test mark notification as read
     */
    public function test_mark_notification_as_read()
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'is_read' => false
        ]);
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Notification marked as read.']);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'is_read' => true
        ]);
    }

    /**
     * Test mark notification as read for non-owned notification
     */
    public function test_mark_notification_as_read_forbidden()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $user1->id]);
        
        $token = $user2->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertStatus(403)
            ->assertJson(['error' => 'Access denied.']);
    }

    /**
     * Test mark non-existent notification as read
     */
    public function test_mark_non_existent_notification_as_read()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/v1/notifications/99999/read');

        $response->assertStatus(404);
    }
}
