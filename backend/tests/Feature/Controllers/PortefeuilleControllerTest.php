<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Cryptomoney;
use App\Models\CryptoWalletAsset;
use App\Services\PortefeuilleService;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Mockery;

class PortefeuilleControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $wallet;
    protected $crypto;
    protected $transactionService;
    protected $walletService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'role' => 'CLIENT',
            'email_verified_at' => now()
        ]);
        
        $this->wallet = Wallet::factory()->create([
            'user_id' => $this->user->id,
            'balance_eur' => 1000.00
        ]);
        
        $this->crypto = Cryptomoney::factory()->create([
            'symbol' => 'BTC',
            'name' => 'Bitcoin',
            'price_eur' => 50000.00
        ]);
        
        Sanctum::actingAs($this->user);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_list_user_wallet_successfully()
    {
        CryptoWalletAsset::factory()->create([
            'wallet_id' => $this->wallet->id,
            'cryptomoney_id' => $this->crypto->id,
            'quantity' => 0.5
        ]);

        $response = $this->getJson('/api/v1/wallets');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'wallet' => [
                    'id',
                    'user_id',
                    'balance_eur',
                    'crypto_wallet_assets'
                ],
                'solde_eur',
                'stats'
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_403_for_non_client_users()
    {
        $admin = User::factory()->create([
            'role' => 'ADMIN',
            'email_verified_at' => now()
        ]);
        
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/wallets');

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Seuls les clients peuvent accéder à leur wallet'
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_404_when_client_has_no_wallet()
    {
        $userWithoutWallet = User::factory()->create([
            'role' => 'CLIENT',
            'email_verified_at' => now()
        ]);
        
        Sanctum::actingAs($userWithoutWallet);

        $response = $this->getJson('/api/v1/wallets');

        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_401_for_unauthenticated_users()
    {
        // Clear authentication completely
        $this->app['auth']->guard('sanctum')->forgetUser();

        $response = $this->getJson('/api/v1/wallets');

        $response->assertStatus(401);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_perform_buy_transaction_successfully()
    {
        $this->mock(TransactionService::class, function ($mock) {
            $mock->shouldReceive('handleTransaction')
                ->once()
                ->with($this->user, 'BTC', 'ACHAT', 0.1)
                ->andReturn('Achat effectué avec succès');
        });

        $response = $this->postJson('/api/v1/wallets/transaction', [
            'symbol' => 'BTC',
            'type' => 'ACHAT',
            'quantity' => 0.1
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'new_balance',
                'transaction_details' => [
                    'type',
                    'quantity',
                    'crypto',
                    'price'
                ]
            ])
            ->assertJson([
                'message' => 'Achat effectué avec succès',
                'transaction_details' => [
                    'type' => 'ACHAT',
                    'quantity' => 0.1,
                    'crypto' => 'BTC'
                ]
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_perform_sell_transaction_successfully()
    {
        $this->mock(TransactionService::class, function ($mock) {
            $mock->shouldReceive('handleTransaction')
                ->once()
                ->with($this->user, 'BTC', 'VENTE', 0.1)
                ->andReturn('Vente effectuée avec succès');
        });

        $response = $this->postJson('/api/v1/wallets/transaction', [
            'symbol' => 'BTC',
            'type' => 'VENTE',
            'quantity' => 0.1
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Vente effectuée avec succès',
                'transaction_details' => [
                    'type' => 'VENTE',
                    'quantity' => 0.1,
                    'crypto' => 'BTC'
                ]
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_422_for_invalid_transaction_data()
    {
        $response = $this->postJson('/api/v1/wallets/transaction', [
            'symbol' => 'INVALID',
            'type' => 'INVALID_TYPE',
            'quantity' => -1
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['error']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_422_for_missing_transaction_data()
    {
        $response = $this->postJson('/api/v1/wallets/transaction', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['error']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_calculate_plus_value_successfully()
    {
        $this->mock(PortefeuilleService::class, function ($mock) {
            $mock->shouldReceive('calculatePlusValue')
                ->once()
                ->with($this->wallet->id)
                ->andReturn([
                    'plus_value_totale' => 500.00,
                    'pourcentage_gain' => 25.5,
                    'details' => []
                ]);
        });

        $response = $this->getJson('/api/v1/wallets/plus-value');

        $response->assertStatus(200)
            ->assertJson([
                'plus_value_totale' => 500.00,
                'pourcentage_gain' => 25.5
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_404_for_plus_value_when_no_wallet()
    {
        $userWithoutWallet = User::factory()->create([
            'role' => 'CLIENT',
            'email_verified_at' => now()
        ]);
        
        Sanctum::actingAs($userWithoutWallet);

        $response = $this->getJson('/api/v1/wallets/plus-value');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'wallet non trouvé'
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_wallet_history_successfully()
    {
        $this->mock(PortefeuilleService::class, function ($mock) {
            $mock->shouldReceive('getPortfolioHistory')
                ->once()
                ->with($this->wallet->id)
                ->andReturn([
                    'history' => [
                        ['date' => '2024-01-01', 'value' => 1000.00],
                        ['date' => '2024-01-02', 'value' => 1050.00]
                    ]
                ]);
        });

        $response = $this->getJson('/api/v1/wallets/history');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'history' => [
                    '*' => ['date', 'value']
                ]
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_404_for_history_when_no_wallet()
    {
        $userWithoutWallet = User::factory()->create([
            'role' => 'CLIENT',
            'email_verified_at' => now()
        ]);
        
        Sanctum::actingAs($userWithoutWallet);

        $response = $this->getJson('/api/v1/wallets/history');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'wallet non trouvé'
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_transaction_service_exception()
    {
        $this->mock(TransactionService::class, function ($mock) {
            $mock->shouldReceive('handleTransaction')
                ->once()
                ->andThrow(new \Exception('Erreur de transaction'));
        });

        $response = $this->postJson('/api/v1/wallets/transaction', [
            'symbol' => 'BTC',
            'type' => 'ACHAT',
            'quantity' => 0.1
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'Erreur lors de la transaction',
                'details' => 'Erreur de transaction'
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_plus_value_service_exception()
    {
        $this->mock(PortefeuilleService::class, function ($mock) {
            $mock->shouldReceive('calculatePlusValue')
                ->once()
                ->andThrow(new \Exception('Erreur de calcul'));
        });

        $response = $this->getJson('/api/v1/wallets/plus-value');

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'Erreur lors du calcul de la plus-value',
                'details' => 'Erreur de calcul'
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_history_service_exception()
    {
        $this->mock(PortefeuilleService::class, function ($mock) {
            $mock->shouldReceive('getPortfolioHistory')
                ->once()
                ->andThrow(new \Exception('Erreur d\'historique'));
        });

        $response = $this->getJson('/api/v1/wallets/history');

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'Erreur lors de la récupération de l\'historique',
                'details' => 'Erreur d\'historique'
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_wallet_list_exception()
    {
        // Create a user with role CLIENT but no wallet
        $userWithoutWallet = User::factory()->create([
            'role' => 'CLIENT',
            'email_verified_at' => now()
        ]);
        
        Sanctum::actingAs($userWithoutWallet);

        $response = $this->getJson('/api/v1/wallets');

        $response->assertStatus(404);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}