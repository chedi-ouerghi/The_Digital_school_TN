<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{

    /**
     * Test get notifications for authenticated user
     */
    public function test_get_notifications_authenticated()
    {
        $user = User::factory()->create();
        $user->email_verified_at = now();
        $user->save();
        
        Notification::factory()->count(3)->create(['user_id' => $user->id]);
        
        Auth::login($user);

        $response = $this->getJson('/api/v1/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'user_id', 'title', 'message', 'is_read']
                ]
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
        $user = $this->createAuthenticatedUser();

        $response = $this->authenticatedJson('GET', '/api/v1/notifications', [], $user);

        $response->assertStatus(200)
            ->assertJson([]);
    }

    /**
     * Test mark notification as read
     */
    public function test_mark_notification_as_read()
    {
        $user = $this->createAuthenticatedUser();
        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'is_read' => false
        ]);

        $response = $this->authenticatedJson('PUT', "/api/v1/notifications/{$notification->id}/read", [], $user);

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
        $user2 = $this->createAuthenticatedUser();
        $notification = Notification::factory()->create(['user_id' => $user1->id]);

        $response = $this->authenticatedJson('PUT', "/api/v1/notifications/{$notification->id}/read", [], $user2);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Access denied.']);
    }

    /**
     * Test mark non-existent notification as read
     */
    public function test_mark_non_existent_notification_as_read()
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->authenticatedJson('PUT', '/api/v1/notifications/99999/read', [], $user);

        $response->assertStatus(404);
    }
}
