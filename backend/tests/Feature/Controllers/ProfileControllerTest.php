<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test get profile overview for authenticated user
     */
    public function test_get_profile_overview_authenticated()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/profile/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ]);
    }

    /**
     * Test get profile overview without authentication
     */
    public function test_get_profile_overview_without_authentication()
    {
        $response = $this->getJson('/api/v1/profile/stats');

        $response->assertStatus(401);
    }

    /**
     * Test upload profile picture
     */
    public function test_upload_profile_picture()
    {
        // Skip this test if GD extension is not available
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not installed.');
        }

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $file = \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/profile/picture/upload', [
                'profile_picture' => $file
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'path',
                'url'
            ]);
    }

    /**
     * Test upload profile picture without file
     */
    public function test_upload_profile_picture_without_file()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/profile/picture/upload', []);

        $response->assertStatus(422);
    }

    /**
     * Test upload profile picture with invalid file type
     */
    public function test_upload_profile_picture_with_invalid_file_type()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $file = \Illuminate\Http\UploadedFile::fake()->create('document.txt');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/profile/picture/upload', [
                'profile_picture' => $file
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test upload profile banner
     */
    public function test_upload_profile_banner()
    {
        // Skip this test if GD extension is not available
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is not installed.');
        }

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $file = \Illuminate\Http\UploadedFile::fake()->image('banner.jpg');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/profile/banner/upload', [
                'profile_banner' => $file
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'path',
                'url'
            ]);
    }

 

    /**
     * Test delete profile picture when none exists
     */
    public function test_delete_profile_picture_when_none_exists()
    {
        $user = User::factory()->create([
            'profile_picture' => null
        ]);
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson('/api/v1/profile/picture');

        $response->assertStatus(404);
        
        $data = $response->json();
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('No profile picture to delete', $data['error']);
    }

    /**
     * Test delete profile banner when none exists
     */
    public function test_delete_profile_banner_when_none_exists()
    {
        $user = User::factory()->create([
            'profile_banner' => null
        ]);
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson('/api/v1/profile/banner');

        $response->assertStatus(404);
        
        $data = $response->json();
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('No profile banner to delete', $data['error']);
    }
}
