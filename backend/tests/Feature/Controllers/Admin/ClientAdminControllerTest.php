<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Cryptomoney;
use App\Models\CryptoWalletAsset;
use Tests\TestCase;

class ClientAdminControllerTest extends TestCase
{

    /**
     * Test get client portfolio as admin
     */
    public function test_get_client_portfolio_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $client = User::factory()->create(['role' => 'CLIENT']);
        $wallet = Wallet::factory()->create([
            'user_id' => $client->id,
            'balance_eur' => 5000.00
        ]);
        
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/v1/admin/clients/{$client->id}/wallet");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'portfolio' => [
                    'id',
                    'balance_eur',
                    'valeur_totale',
                    'plus_value_totale',
                    'cryptos',
                    'transactions'
                ]
            ]);
    }

    /**
     * Test get client portfolio with cryptos as admin
     */
    public function test_get_client_portfolio_with_cryptos_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $client = User::factory()->create(['role' => 'CLIENT']);
        $wallet = Wallet::factory()->create(['user_id' => $client->id]);
        
        $crypto = Cryptomoney::factory()->create([
            'name' => 'Bitcoin',
            'symbol' => 'BTC',
            'price_eur' => 50000
        ]);
        
        CryptoWalletAsset::factory()->create([
            'wallet_id' => $wallet->id,
            'cryptomoney_id' => $crypto->id,
            'quantity' => 0.5
        ]);
        
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/v1/admin/clients/{$client->id}/wallet");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'portfolio' => [
                    'cryptos' => [
                        '*' => ['symbole', 'nom', 'quantite', 'valeur_actuelle']
                    ]
                ]
            ]);
    }

    /**
     * Test get portfolio for non-existent client as admin
     */
    public function test_get_portfolio_for_non_existent_client_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/clients/99999/wallet');

        $response->assertStatus(404)
            ->assertJson(['error' => 'User not found.']);
    }

    /**
     * Test get client portfolio without wallet as admin
     */
    public function test_get_client_portfolio_without_wallet_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $client = User::factory()->create(['role' => 'CLIENT']);
        
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/v1/admin/clients/{$client->id}/wallet");

        $response->assertStatus(404)
            ->assertJson(['error' => 'No wallet was found for this user.']);
    }

    /**
     * Test get client portfolio as non-admin
     */
    public function test_get_client_portfolio_as_non_admin()
    {
        $user = User::factory()->create(['role' => 'CLIENT']);
        $client = User::factory()->create(['role' => 'CLIENT']);
        
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/v1/admin/clients/{$client->id}/wallet");

        $response->assertStatus(403);
    }

    /**
     * Test get client portfolio without authentication
     */
    public function test_get_client_portfolio_without_authentication()
    {
        $client = User::factory()->create(['role' => 'CLIENT']);

        $response = $this->getJson("/api/v1/admin/clients/{$client->id}/wallet");

        $response->assertStatus(401);
    }

    /**
     * Test get portfolio includes user information
     */
    public function test_get_portfolio_includes_user_information()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $client = User::factory()->create([
            'role' => 'CLIENT',
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
        $wallet = Wallet::factory()->create(['user_id' => $client->id]);
        
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/v1/admin/clients/{$client->id}/wallet");

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $client->id,
                    'name' => 'John Doe',
                    'email' => 'john@example.com'
                ]
            ]);
    }
}
