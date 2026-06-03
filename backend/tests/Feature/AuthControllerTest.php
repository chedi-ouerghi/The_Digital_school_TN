<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{

    /**
     * Test user login with valid credentials
     */
    public function test_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'chediouerghi88@gmail.com',
            'password' => bcrypt('Admin123!'),
            'role' => 'CLIENT'
        ]);

        // Obtenir le token CSRF pour les endpoints publics
        $csrfToken = $this->getCsrfToken();
        
        $response = $this->withHeader('X-XSRF-TOKEN', $csrfToken)
            ->postJson('/api/v1/login', [
                'email' => 'chediouerghi88@gmail.com',
                'password' => 'Admin123!',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'role'],
                'message'
            ]);
    }

    /**
     * Test login with invalid email
     */
    public function test_login_with_invalid_email()
    {
        // Obtenir le token CSRF pour les endpoints publics
        $csrfToken = $this->getCsrfToken();
        
        $response = $this->withHeader('X-XSRF-TOKEN', $csrfToken)
            ->postJson('/api/v1/login', [
                'email' => 'nonexistent@example.com',
                'password' => 'Admin123!',
            ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid credentials']);
    }

    /**
     * Test login with invalid password
     */
    public function test_login_with_invalid_password()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Admin123!'),
        ]);

        // Obtenir le token CSRF pour les endpoints publics
        $csrfToken = $this->getCsrfToken();
        
        $response = $this->withHeader('X-XSRF-TOKEN', $csrfToken)
            ->postJson('/api/v1/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);

        $response->assertStatus(401);
    }

    /**
     * Test logout authenticated user
     */
    public function test_logout_authenticated_user()
    {
        $user = $this->createAuthenticatedUser();

        $response = $this->authenticatedJson('POST', '/api/v1/logout', [], $user);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logout successful']);
    }

    /**
     * Test logout without authentication
     */
    public function test_logout_without_authentication()
    {
        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(401);
    }

    /**
     * Test get profile of authenticated user
     */
    public function test_get_profile_authenticated()
    {
        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'role']
            ]);
    }

    /**
     * Test get profile without authentication
     */
    public function test_get_profile_without_authentication()
    {
        $response = $this->getJson('/api/v1/profile');

        $response->assertStatus(401);
    }

    /**
     * Test update profile
     */
    public function test_update_profile()
    {
        $user = $this->createAuthenticatedUser([
            'name' => 'Old Name',
            'email' => 'old@example.com'
        ]);

        $response = $this->authenticatedJson('PUT', '/api/v1/profile', [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ], $user);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com'
        ]);
    }

    /**
     * Test change password
     */
    public function test_change_password()
    {
        $user = $this->createAuthenticatedUser([
            'password' => bcrypt('oldpassword')
        ]);

        $response = $this->authenticatedJson('PUT', '/api/v1/profile/password', [
            'current_password' => 'oldpassword',
            'password' => 'newAdmin123!',
            'password_confirmation' => 'newAdmin123!',
        ], $user);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Password updated successfully.']);
    }

    /**
     * Test change password with wrong current password
     */
    public function test_change_password_with_wrong_current_password()
    {
        $user = $this->createAuthenticatedUser([
            'password' => bcrypt('oldpassword')
        ]);

        $response = $this->authenticatedJson('PUT', '/api/v1/profile/password', [
            'current_password' => 'wrongpassword',
            'password' => 'newAdmin123!',
            'password_confirmation' => 'newAdmin123!',
        ], $user);

        $response->assertStatus(400);
    }

    /**
     * Test request account (public endpoint)
     */
    public function test_request_account()
    {
        // Obtenir le token CSRF pour les endpoints publics
        $csrfToken = $this->getCsrfToken();
        
        $response = $this->withHeader('X-XSRF-TOKEN', $csrfToken)
            ->postJson('/api/v1/request-account', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Your request has been sent successfully. Please check your email to confirm your address.'
            ]);

        $this->assertDatabaseHas('account_requests', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'status' => 'PENDING'
        ]);
    }

    /**
     * Test request account with duplicate email
     */
    public function test_request_account_with_duplicate_email()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        // Obtenir le token CSRF pour les endpoints publics
        $csrfToken = $this->getCsrfToken();
        
        $response = $this->withHeader('X-XSRF-TOKEN', $csrfToken)
            ->postJson('/api/v1/request-account', [
                'name' => 'John Doe',
                'email' => 'existing@example.com',
            ]);

        $response->assertStatus(422);
    }
}
