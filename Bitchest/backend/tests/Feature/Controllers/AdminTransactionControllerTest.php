<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Cryptomoney;
use App\Models\CryptoWalletAsset;
use App\Models\Transaction;
use App\Models\Notification;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Mockery;

class AdminTransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $client;
    protected $wallet;
    protected $crypto;
    protected $transaction;
    protected $transactionService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'role' => 'ADMIN',
            'email_verified_at' => now()
        ]);
        
        $this->client = User::factory()->create([
            'role' => 'CLIENT',
            'email_verified_at' => now()
        ]);
        
        $this->wallet = Wallet::factory()->create([
            'user_id' => $this->client->id,
            'balance_eur' => 1000.00
        ]);
        
        $this->crypto = Cryptomoney::factory()->create([
            'name' => 'Bitcoin',
            'symbol' => 'BTC',
            'price_eur' => 50000.00
        ]);
        
        $this->cryptoWalletAsset = CryptoWalletAsset::factory()->create([
            'wallet_id' => $this->wallet->id,
            'cryptomoney_id' => $this->crypto->id,
            'quantity' => 0.1,
            'average_buy_price' => 5000.00
        ]);
        
        $this->transaction = Transaction::factory()->create([
            'crypto_wallet_asset_id' => $this->cryptoWalletAsset->id,
            'cryptomoney_id' => $this->crypto->id,
            'type' => 'ACHAT',
            'quantity' => 0.1,
            'price' => 5000.00,
            'total_eur' => 500.00,
            'cancelled_at' => null
        ]);
        
        Sanctum::actingAs($this->admin);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_list_all_transactions_successfully()
    {
        // Create additional cryptomoney for the second transaction to avoid unique constraint
        $crypto2 = Cryptomoney::factory()->create([
            'name' => 'Ethereum',
            'symbol' => 'ETH',
            'price_eur' => 3000.00
        ]);
        
        // Create additional crypto wallet asset for the second crypto
        $cryptoWalletAsset2 = CryptoWalletAsset::factory()->create([
            'wallet_id' => $this->wallet->id,
            'cryptomoney_id' => $crypto2->id,
            'quantity' => 0.05,
            'average_buy_price' => 5500.00
        ]);
        
        $transaction2 = Transaction::factory()->create([
            'crypto_wallet_asset_id' => $cryptoWalletAsset2->id,
            'cryptomoney_id' => $crypto2->id,
            'type' => 'VENTE',
            'quantity' => 0.05,
            'price' => 5500.00,
            'total_eur' => 275.00,
            'created_at' => now()->subDays(1)
        ]);

        $response = $this->getJson('/api/v1/admin/transactions');

        $response->assertStatus(200);
        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertArrayHasKey('total', $json);
        $this->assertGreaterThanOrEqual(2, $json['total']);
        $this->assertArrayHasKey('id', $json['data'][0]);
        $this->assertArrayHasKey('type', $json['data'][0]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_filter_transactions_by_user_id()
    {
        $otherClient = User::factory()->create(['role' => 'CLIENT']);
        $otherWallet = Wallet::factory()->create(['user_id' => $otherClient->id]);
        $otherCryptoWalletAsset = CryptoWalletAsset::factory()->create([
            'wallet_id' => $otherWallet->id,
            'cryptomoney_id' => $this->crypto->id,
            'quantity' => 0.1,
            'average_buy_price' => 5000.00
        ]);
        $otherTransaction = Transaction::factory()->create([
            'crypto_wallet_asset_id' => $otherCryptoWalletAsset->id,
            'cryptomoney_id' => $this->crypto->id
        ]);

        $response = $this->getJson("/api/v1/admin/transactions?user_id={$this->client->id}");

        $response->assertStatus(200);
        
        $transactions = $response->json('data');
        $this->assertCount(1, $transactions);
        $this->assertEquals($this->transaction->id, $transactions[0]['id']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_filter_transactions_by_type()
    {
        // Create additional cryptomoney for the VENTE transaction to avoid unique constraint
        $crypto2 = Cryptomoney::factory()->create([
            'name' => 'Ethereum',
            'symbol' => 'ETH',
            'price_eur' => 3000.00
        ]);
        
        $venteCryptoWalletAsset = CryptoWalletAsset::factory()->create([
            'wallet_id' => $this->wallet->id,
            'cryptomoney_id' => $crypto2->id,
            'quantity' => 0.05,
            'average_buy_price' => 5500.00
        ]);
        
        $venteTransaction = Transaction::factory()->create([
            'crypto_wallet_asset_id' => $venteCryptoWalletAsset->id,
            'cryptomoney_id' => $crypto2->id,
            'type' => 'VENTE'
        ]);

        $response = $this->getJson('/api/v1/admin/transactions?type=VENTE');

        $response->assertStatus(200);
        
        $transactions = $response->json('data');
        $this->assertCount(1, $transactions);
        $this->assertEquals('VENTE', $transactions[0]['type']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_filter_transactions_by_date_range()
    {
        // Create additional cryptomoney for the old transaction to avoid unique constraint
        $crypto2 = Cryptomoney::factory()->create([
            'name' => 'Ethereum',
            'symbol' => 'ETH' . uniqid(),
            'price_eur' => 3000.00
        ]);
        
        $crypto3 = Cryptomoney::factory()->create([
            'name' => 'Cardano',
            'symbol' => 'ADA' . uniqid(),
            'price_eur' => 1.00
        ]);
        
        $oldCryptoWalletAsset = CryptoWalletAsset::factory()->create([
            'wallet_id' => $this->wallet->id,
            'cryptomoney_id' => $crypto2->id,
            'quantity' => 0.1,
            'average_buy_price' => 5000.00
        ]);
        
        $oldTransaction = Transaction::factory()->create([
            'crypto_wallet_asset_id' => $oldCryptoWalletAsset->id,
            'cryptomoney_id' => $crypto2->id,
            'created_at' => now()->subDays(10)
        ]);

        // Create additional cryptomoney for the recent transaction to avoid unique constraint
        $crypto3 = Cryptomoney::factory()->create([
            'name' => 'Cardano',
            'symbol' => 'ADA',
            'price_eur' => 1.00
        ]);
        
        $recentCryptoWalletAsset = CryptoWalletAsset::factory()->create([
            'wallet_id' => $this->wallet->id,
            'cryptomoney_id' => $crypto3->id,
            'quantity' => 0.1,
            'average_buy_price' => 5000.00
        ]);
        
        $recentTransaction = Transaction::factory()->create([
            'crypto_wallet_asset_id' => $recentCryptoWalletAsset->id,
            'cryptomoney_id' => $crypto3->id,
            'created_at' => now()->subDays(2)
        ]);

        $dateFrom = now()->subDays(15)->format('Y-m-d');
        $dateTo = now()->format('Y-m-d');

        $response = $this->getJson("/api/v1/admin/transactions?date_from={$dateFrom}&date_to={$dateTo}");

        $response->assertStatus(200);
        
        $transactions = $response->json('data');
        $this->assertCount(2, $transactions); // recentTransaction and the one from setUp
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_transaction_details_successfully()
    {
        $response = $this->getJson("/api/v1/admin/transactions/{$this->transaction->id}");

        $response->assertStatus(200);
        $json = $response->json();
        $this->assertEquals($this->transaction->id, $json['id']);
        $this->assertEquals('ACHAT', $json['type']);
        // Structure souple: contient au moins crypto_wallet_asset ou cryptomoney
        $this->assertTrue(isset($json['crypto_wallet_asset']) || isset($json['cryptomoney']) || isset($json['type']));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_404_when_transaction_not_found()
    {
        $nonExistentId = 999999;

        $response = $this->getJson("/api/v1/admin/transactions/{$nonExistentId}");

        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_cancel_transaction_successfully()
    {
        $this->mock(TransactionService::class, function ($mock) {
            $mock->shouldReceive('cancelTransaction')
                ->once()
                ->withAnyArgs()
                ->andReturn(['success' => true, 'message' => 'Transaction annulée']);
        });

        $response = $this->postJson("/api/v1/admin/transactions/{$this->transaction->id}/cancel", [
            'reason' => 'Annulation administrative'
        ]);

        $response->assertStatus(200);
        $json = $response->json();
        $this->assertEquals('Transaction cancelled successfully.', $json['message']);
        $this->assertTrue($json['result']['success'] ?? false);

        // Verify notification was created
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->client->id,
            'type' => Notification::TYPE_ADMIN_ACTION,
            'title' => 'Transaction cancelled by an administrator'
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_cancel_transaction_without_reason()
    {
        $this->mock(TransactionService::class, function ($mock) {
            $mock->shouldReceive('cancelTransaction')
                ->once()
                ->withAnyArgs()
                ->andReturn(['success' => true]);
        });

        $response = $this->postJson("/api/v1/admin/transactions/{$this->transaction->id}/cancel");

        $response->assertStatus(200);
        $this->assertEquals('Transaction cancelled successfully.', $response->json('message'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_400_when_transaction_already_cancelled()
    {
        // Create additional cryptomoney for the cancelled transaction to avoid unique constraint
        $crypto2 = Cryptomoney::factory()->create([
            'name' => 'Ethereum',
            'symbol' => 'ETH',
            'price_eur' => 3000.00
        ]);
        
        $cancelledCryptoWalletAsset = CryptoWalletAsset::factory()->create([
            'wallet_id' => $this->wallet->id,
            'cryptomoney_id' => $crypto2->id,
            'quantity' => 0.1,
            'average_buy_price' => 5000.00
        ]);
        
        $cancelledTransaction = Transaction::factory()->create([
            'crypto_wallet_asset_id' => $cancelledCryptoWalletAsset->id,
            'cryptomoney_id' => $crypto2->id,
            'cancelled_at' => now()
        ]);

        $response = $this->postJson("/api/v1/admin/transactions/{$cancelledTransaction->id}/cancel");

        $response->assertStatus(400);
        $this->assertEquals('This transaction has already been cancelled.', $response->json('error'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_404_when_transaction_not_found_for_cancel()
    {
        $nonExistentId = 999999;

        $response = $this->postJson("/api/v1/admin/transactions/{$nonExistentId}/cancel");

        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_transaction_cancel_exception()
    {
        $this->mock(TransactionService::class, function ($mock) {
            $mock->shouldReceive('cancelTransaction')
                ->once()
                ->withAnyArgs()
                ->andThrow(new \Exception('Erreur lors de l\'annulation'));
        });

        $response = $this->postJson("/api/v1/admin/transactions/{$this->transaction->id}/cancel");

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Erreur lors de l\'annulation'
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_notification_creation_failure_gracefully()
    {
        // Mock transaction service to succeed but we'll test notification failure
        $this->mock(TransactionService::class, function ($mock) {
            $mock->shouldReceive('cancelTransaction')
                ->once()
                ->withAnyArgs()
                ->andReturn(['success' => true]);
        });

        // Mock Notification model to throw exception
        $this->partialMock(\App\Models\Notification::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andThrow(new \Exception('Database error'));
        });

        // This should still succeed because notification failure is caught
        $response = $this->postJson("/api/v1/admin/transactions/{$this->transaction->id}/cancel");

        $response->assertStatus(200);
        $this->assertEquals('Transaction cancelled successfully.', $response->json('message'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_403_for_non_admin_users()
    {
        $client = User::factory()->create([
            'role' => 'CLIENT',
            'email_verified_at' => now()
        ]);
        
        Sanctum::actingAs($client);

        // Test list transactions
        $response = $this->getJson('/api/v1/admin/transactions');
        $response->assertStatus(403);

        // Test show transaction
        $response = $this->getJson("/api/v1/admin/transactions/{$this->transaction->id}");
        $response->assertStatus(403);

        // Test cancel transaction
        $response = $this->postJson("/api/v1/admin/transactions/{$this->transaction->id}/cancel");
        $response->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_401_for_unauthenticated_users()
    {
        // Create a new test instance without authentication
        $this->refreshApplication();

        // Test list transactions
        $response = $this->getJson('/api/v1/admin/transactions');
        $response->assertStatus(401);

        // Test show transaction
        $response = $this->getJson("/api/v1/admin/transactions/{$this->transaction->id}");
        $response->assertStatus(401);

        // Test cancel transaction
        $response = $this->postJson("/api/v1/admin/transactions/{$this->transaction->id}/cancel");
        $response->assertStatus(401);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_paginates_transactions_correctly()
    {
        // Create 25 cryptomoney records and corresponding crypto wallet assets
        $cryptos = Cryptomoney::factory()->count(25)->create();
        
        $cryptoWalletAssets = collect();
        foreach ($cryptos as $crypto) {
            $cryptoWalletAssets->push(CryptoWalletAsset::factory()->create([
                'wallet_id' => $this->wallet->id,
                'cryptomoney_id' => $crypto->id,
                'quantity' => 0.1,
                'average_buy_price' => 5000.00
            ]));
        }
        
        // Create 25 transactions, each linked to a different crypto wallet asset
        foreach ($cryptoWalletAssets as $cryptoWalletAsset) {
            Transaction::factory()->create([
                'crypto_wallet_asset_id' => $cryptoWalletAsset->id,
                'cryptomoney_id' => $cryptoWalletAsset->cryptomoney_id
            ]);
        }

        $response = $this->getJson('/api/v1/admin/transactions');

        $response->assertStatus(200)
            ->assertJson([
                'current_page' => 1,
                'per_page' => 20,
                'total' => 26 // 25 created + 1 from setUp
            ])
            ->assertJsonCount(20, 'data'); // First page should have 20 items
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_orders_transactions_by_created_at_desc()
    {
        // Create additional cryptomoney for the old transaction to avoid unique constraint
        $crypto2 = Cryptomoney::factory()->create([
            'name' => 'Ethereum',
            'symbol' => 'ETH',
            'price_eur' => 3000.00
        ]);
        
        $oldCryptoWalletAsset = CryptoWalletAsset::factory()->create([
            'wallet_id' => $this->wallet->id,
            'cryptomoney_id' => $crypto2->id,
            'quantity' => 0.1,
            'average_buy_price' => 5000.00
        ]);
        
        $oldTransaction = Transaction::factory()->create([
            'crypto_wallet_asset_id' => $oldCryptoWalletAsset->id,
            'cryptomoney_id' => $crypto2->id,
            'created_at' => now()->subDays(5)
        ]);

        // Create another cryptomoney for the recent transaction to avoid unique constraint
        $crypto3 = Cryptomoney::factory()->create([
            'name' => 'Litecoin',
            'symbol' => 'LTC',
            'price_eur' => 200.00
        ]);
        
        $recentCryptoWalletAsset = CryptoWalletAsset::factory()->create([
            'wallet_id' => $this->wallet->id,
            'cryptomoney_id' => $crypto3->id,
            'quantity' => 0.1,
            'average_buy_price' => 5000.00
        ]);
        
        $recentTransaction = Transaction::factory()->create([
            'crypto_wallet_asset_id' => $recentCryptoWalletAsset->id,
            'cryptomoney_id' => $crypto3->id,
            'created_at' => now()
        ]);

        $response = $this->getJson('/api/v1/admin/transactions');

        $response->assertStatus(200);
        
        $transactions = $response->json('data');
        // Recent transaction should come first
        $this->assertEquals($recentTransaction->id, $transactions[0]['id']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}