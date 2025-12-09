<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Cryptomoney;
use App\Models\CryptoWalletAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortefeuilleControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test get wallets for authenticated user
     */
    public function test_get_wallets_authenticated()
    {
        $user = User::factory()->create(['role' => 'CLIENT']);
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/wallets');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'wallet',
                'solde_eur',
                'stats'
            ]);
    }

    /**
     * Test get wallet without authentication
     */
    public function test_get_wallets_without_authentication()
    {
        $response = $this->getJson('/api/v1/wallets');

        $response->assertStatus(401);
    }

    /**
     * Test get specific wallet
     */
    public function test_get_specific_wallet()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance_eur' => 1000.00
        ]);
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/v1/wallets/{$wallet->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'wallet',
                'solde_eur'
            ]);
    }

    /**
     * Test get wallet plus-value
     */
    public function test_get_wallet_plus_value()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/wallets/plus-value');

        $response->assertStatus(200);
    }

    /**
     * Test get wallet history
     */
    public function test_get_wallet_history()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/wallets/history');

        $response->assertStatus(200);
    }

    /**
     * Test get specific wallet history
     */
    public function test_get_specific_wallet_history()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/v1/wallets/{$wallet->id}/history");

        $response->assertStatus(200);
    }

    /**
     * Test perform transaction (buy crypto)
     */
    public function test_perform_buy_transaction()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance_eur' => 5000.00
        ]);
        $crypto = Cryptomoney::factory()->create([
            'symbol' => 'BTC',
            'price_eur' => 50000
        ]);
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/wallets/transaction', [
                'symbol' => 'BTC',
                'type' => 'ACHAT',
                'quantity' => 0.1
            ]);

        $response->assertStatus(200);
    }

    /**
     * Test perform transaction without sufficient funds
     */
    public function test_perform_transaction_without_sufficient_funds()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance_eur' => 100.00
        ]);
        $crypto = Cryptomoney::factory()->create([
            'symbol' => 'BTC',
            'price_eur' => 50000
        ]);
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/wallets/transaction', [
                'symbol' => 'BTC',
                'type' => 'ACHAT',
                'quantity' => 1.0
            ]);

        $response->assertStatus(500);
    }

    /**
     * Test perform transaction with invalid crypto
     */
    public function test_perform_transaction_with_invalid_crypto()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/wallets/transaction', [
                'symbol' => 'INVALID',
                'type' => 'ACHAT',
                'quantity' => 1.0
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test perform sell transaction
     */
    public function test_perform_sell_transaction()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $crypto = Cryptomoney::factory()->create(['symbol' => 'BTC']);
        
        // Create wallet asset with crypto
        CryptoWalletAsset::factory()->create([
            'wallet_id' => $wallet->id,
            'cryptomoney_id' => $crypto->id,
            'quantity' => 0.5
        ]);

        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/wallets/transaction', [
                'symbol' => 'BTC',
                'type' => 'VENTE',
                'quantity' => 0.1
            ]);

        $response->assertStatus(200);
    }
}
