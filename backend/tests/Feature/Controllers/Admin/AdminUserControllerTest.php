<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test get all clients as admin
     */
    public function test_get_all_clients_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        User::factory()->count(5)->create(['role' => 'CLIENT']);
        
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/clients');

        $response->assertStatus(200);
        
        $data = $response->json();
        // The response is a paginated collection
        $this->assertIsArray($data);
        
        // Check that we have pagination keys or data directly
        if (isset($data['data'])) {
            // Laravel pagination structure
            $this->assertArrayHasKey('data', $data);
            if (!empty($data['data'])) {
                $this->assertArrayHasKey('id', $data['data'][0]);
                $this->assertArrayHasKey('name', $data['data'][0]);
                $this->assertArrayHasKey('email', $data['data'][0]);
                $this->assertArrayHasKey('role', $data['data'][0]);
                $this->assertArrayHasKey('balance_eur', $data['data'][0]);
            }
        }
    }

    /**
     * Test get all clients as non-admin
     */
    public function test_get_all_clients_as_non_admin()
    {
        $user = User::factory()->create(['role' => 'CLIENT']);
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/clients');

        $response->assertStatus(403);
    }

    /**
     * Test get specific client as admin
     */
    public function test_get_specific_client_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $client = User::factory()->create(['role' => 'CLIENT']);
        Wallet::factory()->create(['user_id' => $client->id]);
        
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/v1/admin/clients/{$client->id}");

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('account_balance', $data);
    }

    /**
     * Test get non-existent client as admin
     */
    public function test_get_non_existent_client_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/clients/99999');

        $response->assertStatus(404);
    }

    /**
     * Test create client as admin
     */
    public function test_create_client_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/admin/clients', [
                'name' => 'New Client',
                'email' => 'newclient@example.com',
                'initial_balance' => 1000.00
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'name' => 'New Client',
            'email' => 'newclient@example.com'
        ]);
    }

    /**
     * Test create client with duplicate email
     */
    public function test_create_client_with_duplicate_email()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        User::factory()->create(['email' => 'existing@example.com']);
        
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/admin/clients', [
                'name' => 'New Client',
                'email' => 'existing@example.com',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test update client as admin
     */
    public function test_update_client_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $client = User::factory()->create([
            'role' => 'CLIENT',
            'name' => 'Old Name'
        ]);
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/v1/admin/clients/{$client->id}", [
                'name' => 'New Name'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $client->id,
            'name' => 'New Name'
        ]);
    }

    /**
     * Test delete client as admin
     */
    public function test_delete_client_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $client = User::factory()->create(['role' => 'CLIENT']);
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/v1/admin/clients/{$client->id}");

        $response->assertStatus(200);
    }

    /**
     * Test get client transactions as admin
     */
    public function test_get_client_transactions_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $client = User::factory()->create(['role' => 'CLIENT']);
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/v1/admin/clients/{$client->id}/transactions");

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('transactions', $data);
    }

    /**
     * Test get account requests as admin
     */
    public function test_get_account_requests_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/account-requests');

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertIsArray($data);
    }
}
