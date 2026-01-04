<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{

    /**
     * Test get profile overview for authenticated user
     */
    public function test_get_profile_overview_authenticated()
    {
        $user = $this->createAuthenticatedUser();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);

        $response = $this->authenticatedJson('GET', '/api/v1/profile/stats', [], $user);

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

        $user = $this->createAuthenticatedUser();

        $file = \Illuminate\Http\UploadedFile::fake()->image('avatar.jpg');

        $response = $this->authenticatedJson('POST', '/api/v1/profile/picture/upload', [
            'profile_picture' => $file
        ], $user);

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
        $user = $this->createAuthenticatedUser();

        $response = $this->authenticatedJson('POST', '/api/v1/profile/picture/upload', [], $user);

        $response->assertStatus(422);
    }

    /**
     * Test upload profile picture with invalid file type
     */
    public function test_upload_profile_picture_with_invalid_file_type()
    {
        $user = $this->createAuthenticatedUser();

        $file = \Illuminate\Http\UploadedFile::fake()->create('document.txt');

        $response = $this->authenticatedJson('POST', '/api/v1/profile/picture/upload', [
            'profile_picture' => $file
        ], $user);

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

        $user = $this->createAuthenticatedUser();

        $file = \Illuminate\Http\UploadedFile::fake()->image('banner.jpg');

        $response = $this->authenticatedJson('POST', '/api/v1/profile/banner/upload', [
            'profile_banner' => $file
        ], $user);

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
        $user = $this->createAuthenticatedUser([
            'profile_picture' => null
        ]);

        $response = $this->authenticatedJson('DELETE', '/api/v1/profile/picture', [], $user);

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
        $user = $this->createAuthenticatedUser([
            'profile_banner' => null
        ]);

        $response = $this->authenticatedJson('DELETE', '/api/v1/profile/banner', [], $user);

        $response->assertStatus(404);
        
        $data = $response->json();
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('No profile banner to delete', $data['error']);
    }
}
