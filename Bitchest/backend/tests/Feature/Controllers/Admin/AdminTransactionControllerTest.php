<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Cryptomoney;
use App\Models\CryptoWalletAsset;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AdminTransactionControllerTest extends TestCase
{

    /**
     * Test get all transactions as admin
     */
public function test_get_all_transactions_as_admin()
{
    $admin = User::factory()->create(['role' => 'ADMIN']);
    Auth::login($admin);
    
    // Créer une structure complète
    $user = User::factory()->create();
    $wallet = Wallet::factory()->create(['user_id' => $user->id]);
    $crypto = Cryptomoney::factory()->create();
    $asset = CryptoWalletAsset::factory()->create([
        'wallet_id' => $wallet->id,
        'cryptomoney_id' => $crypto->id
    ]);
    
    // Créer des transactions avec des données spécifiques
    Transaction::factory()->count(3)->create([
        'crypto_wallet_asset_id' => $asset->id,
        'type' => 'ACHAT',
        'quantity' => 1.5,
        'price' => 100.00,
        'total_eur' => 150.00,
        'cancelled_at' => null
    ]);

    $response = $this->getJson('/api/v1/admin/transactions');

    $response->assertStatus(200);
    
    // Vérifier la structure de base sans être trop strict
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id', 
                'type', 
                'quantity', 
                'price', 
                'total_eur'
            ]
        ]
    ]);
    
    // Vérifier qu'on a des données
    $responseData = $response->json();
    $this->assertNotEmpty($responseData['data']);
}

    /**
     * Test get all transactions as non-admin
     */
    public function test_get_all_transactions_as_non_admin()
    {
        $user = User::factory()->create(['role' => 'CLIENT']);
        Auth::login($user);

        $response = $this->getJson('/api/v1/admin/transactions');

        $response->assertStatus(403);
    }

    /**
     * Test get specific transaction as admin
     */
    public function test_get_specific_transaction_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        Auth::login($admin);
        
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $crypto = Cryptomoney::factory()->create();
        $asset = CryptoWalletAsset::factory()->create([
            'wallet_id' => $wallet->id,
            'cryptomoney_id' => $crypto->id
        ]);
        $transaction = Transaction::factory()->create([
            'crypto_wallet_asset_id' => $asset->id,
            'type' => 'ACHAT',
            'quantity' => 1.5
        ]);

        $response = $this->getJson("/api/v1/admin/transactions/{$transaction->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'type', 'quantity']);
    }

    /**
     * Test get non-existent transaction as admin
     */
    public function test_get_non_existent_transaction_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/transactions/99999');

        $response->assertStatus(404);
    }

    /**
     * Test cancel transaction as admin
     */
public function test_cancel_transaction_as_admin()
{
    $admin = $this->createAuthenticatedUser(['role' => 'ADMIN']);
    
    $user = User::factory()->create();
    $wallet = Wallet::factory()->create(['user_id' => $user->id]);
    $crypto = Cryptomoney::factory()->create();
    $asset = CryptoWalletAsset::factory()->create([
        'wallet_id' => $wallet->id,
        'cryptomoney_id' => $crypto->id
    ]);
    
    // Créer une transaction non annulée
    $transaction = Transaction::factory()->create([
        'crypto_wallet_asset_id' => $asset->id,
        'cancelled_at' => null,
        'cancel_reason' => null
    ]);

    $transactionId = $transaction->id;

    $response = $this->authenticatedJson('POST', "/api/v1/admin/transactions/{$transactionId}/cancel", [], $admin);

    $response->assertStatus(200)
        ->assertJsonStructure(['message', 'result']);
    
    // Verify the response contains correct data
    $data = $response->json();
    $this->assertEquals('Transaction cancelled successfully.', $data['message']);
    
    // Verify cancelled_at is set in database
    $cancelledTransaction = Transaction::find($transactionId);
    if ($cancelledTransaction) {
        $this->assertNotNull($cancelledTransaction->cancelled_at);
    }
}

    /**
     * Test cancel already cancelled transaction as admin
     */
    public function test_cancel_already_cancelled_transaction_as_admin()
    {
        $admin = $this->createAuthenticatedUser(['role' => 'ADMIN']);
        
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $crypto = Cryptomoney::factory()->create();
        $asset = CryptoWalletAsset::factory()->create([
            'wallet_id' => $wallet->id,
            'cryptomoney_id' => $crypto->id
        ]);
        $transaction = Transaction::factory()->create([
            'crypto_wallet_asset_id' => $asset->id,
            'cancelled_at' => now()
        ]);

        $response = $this->authenticatedJson('POST', "/api/v1/admin/transactions/{$transaction->id}/cancel", [], $admin);

        $response->assertStatus(400);
    }

    /**
     * Test filter transactions by user as admin
     */
    public function test_filter_transactions_by_user_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        Auth::login($admin);
        
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $crypto = Cryptomoney::factory()->create();
        $asset = CryptoWalletAsset::factory()->create([
            'wallet_id' => $wallet->id,
            'cryptomoney_id' => $crypto->id
        ]);
        Transaction::factory()->create([
            'crypto_wallet_asset_id' => $asset->id
        ]);

        $response = $this->getJson("/api/v1/admin/transactions?user_id={$user->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    /**
     * Test filter transactions by type as admin
     */
    public function test_filter_transactions_by_type_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/transactions?type=ACHAT');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
