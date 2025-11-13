<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Services\StatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Mockery;

class AdminStatsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $statsService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'role' => 'ADMIN',
            'email_verified_at' => now()
        ]);
        
        Sanctum::actingAs($this->admin);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_admin_stats_successfully()
    {
        $this->mock(StatsService::class, function ($mock) {
            $mock->shouldReceive('getGlobalStats')
                ->once()
                ->andReturn([
                    'total_users' => 150,
                    'total_wallets' => 120,
                    'total_transactions' => 500,
                    'total_volume_eur' => 1250000.50,
                    'total_plus_value' => 45000.75,
                    'average_wallet_value' => 10416.67,
                    'top_cryptos' => [
                        ['symbol' => 'BTC', 'total_value' => 800000.00],
                        ['symbol' => 'ETH', 'total_value' => 300000.00]
                    ],
                    'recent_transactions' => 50
                ]);
        });

        $response = $this->getJson('/api/v1/admin/stats');

        $response->assertStatus(200)
            ->assertJson([
                'total_users' => 150,
                'total_wallets' => 120,
                'total_transactions' => 500,
                'total_volume_eur' => 1250000.50,
                'total_plus_value' => 45000.75,
                'average_wallet_value' => 10416.67
            ])
            ->assertJsonStructure([
                'total_users',
                'total_wallets',
                'total_transactions',
                'total_volume_eur',
                'total_plus_value',
                'average_wallet_value',
                'top_cryptos',
                'recent_transactions'
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_empty_stats_when_no_data()
    {
        $this->mock(StatsService::class, function ($mock) {
            $mock->shouldReceive('getGlobalStats')
                ->once()
                ->andReturn([
                    'total_users' => 0,
                    'total_wallets' => 0,
                    'total_transactions' => 0,
                    'total_volume_eur' => 0.00,
                    'total_plus_value' => 0.00,
                    'average_wallet_value' => 0.00,
                    'top_cryptos' => [],
                    'recent_transactions' => 0
                ]);
        });

        $response = $this->getJson('/api/v1/admin/stats');

        $response->assertStatus(200)
            ->assertJson([
                'total_users' => 0,
                'total_wallets' => 0,
                'total_transactions' => 0,
                'total_volume_eur' => 0.00,
                'total_plus_value' => 0.00,
                'average_wallet_value' => 0.00,
                'top_cryptos' => [],
                'recent_transactions' => 0
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_403_for_non_admin_users()
    {
        $client = User::factory()->create([
            'role' => 'CLIENT',
            'email_verified_at' => now()
        ]);
        
        Sanctum::actingAs($client);

        $response = $this->getJson('/api/v1/admin/stats');

        $response->assertStatus(403);
    }

#[\PHPUnit\Framework\Attributes\Test]
public function it_returns_401_for_unauthenticated_users()
{
    // Create a new test instance without authentication
    $this->refreshApplication();
    
    $response = $this->getJson('/api/v1/admin/stats');

    $response->assertStatus(401);
}

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_stats_service_exception()
    {
        $this->mock(StatsService::class, function ($mock) {
            $mock->shouldReceive('getGlobalStats')
                ->once()
                ->andThrow(new \Exception('Erreur de base de données'));
        });

        $response = $this->getJson('/api/v1/admin/stats');

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'Erreur de base de données'
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_stats_service_timeout()
    {
        $this->mock(StatsService::class, function ($mock) {
            $mock->shouldReceive('getGlobalStats')
                ->once()
                ->andThrow(new \Exception('Timeout lors du calcul des statistiques'));
        });

        $response = $this->getJson('/api/v1/admin/stats');

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'Timeout lors du calcul des statistiques'
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_complex_stats_data_structure()
    {
        $this->mock(StatsService::class, function ($mock) {
            $mock->shouldReceive('getGlobalStats')
                ->once()
                ->andReturn([
                    'total_users' => 1000,
                    'total_wallets' => 800,
                    'total_transactions' => 5000,
                    'total_volume_eur' => 2500000.00,
                    'total_plus_value' => 125000.25,
                    'average_wallet_value' => 3125.00,
                    'top_cryptos' => [
                        ['symbol' => 'BTC', 'total_value' => 1500000.00, 'percentage' => 60.0],
                        ['symbol' => 'ETH', 'total_value' => 750000.00, 'percentage' => 30.0],
                        ['symbol' => 'ADA', 'total_value' => 250000.00, 'percentage' => 10.0]
                    ],
                    'recent_transactions' => 100,
                    'daily_active_users' => 150,
                    'weekly_growth' => 5.5,
                    'monthly_growth' => 12.3
                ]);
        });

        $response = $this->getJson('/api/v1/admin/stats');

        $response->assertStatus(200)
            ->assertJson([
                'total_users' => 1000,
                'total_wallets' => 800,
                'total_transactions' => 5000,
                'total_volume_eur' => 2500000.00,
                'total_plus_value' => 125000.25,
                'average_wallet_value' => 3125.00,
                'recent_transactions' => 100,
                'daily_active_users' => 150,
                'weekly_growth' => 5.5,
                'monthly_growth' => 12.3
            ])
            ->assertJsonCount(3, 'top_cryptos')
            ->assertJsonFragment([
                'symbol' => 'BTC',
                'total_value' => 1500000.00,
                'percentage' => 60.0
            ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}