<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Models\Cryptomoney;
use App\Services\CryptoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Mockery;

class AdminCryptoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $crypto;
    protected $cryptoService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'role' => 'ADMIN',
            'email_verified_at' => now()
        ]);
        
        $this->crypto = Cryptomoney::factory()->create([
            'name' => 'Bitcoin',
            'symbol' => 'BTC',
            'price_eur' => 50000.00,
            'coingecko_id' => 'bitcoin'
        ]);
        
        Sanctum::actingAs($this->admin);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_update_crypto_successfully()
    {
        $updateData = [
            'name' => 'Updated Bitcoin',
            'symbol' => 'BTC',
            'price_eur' => 55000.00,
            'image' => 'https://example.com/new-image.png',
            'coingecko_id' => 'updated-bitcoin'
        ];

        $response = $this->putJson("/api/v1/admin/cryptos/{$this->crypto->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'Updated Bitcoin',
                'symbol' => 'BTC',
                'image' => 'https://example.com/new-image.png',
                'coingecko_id' => 'updated-bitcoin'
            ])
            ->assertJsonPath('price_eur', '55000.00000000'); // Check decimal string format

        $this->assertDatabaseHas('cryptomoney', [
            'id' => $this->crypto->id,
            'name' => 'Updated Bitcoin',
            'price_eur' => 55000.00
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_update_partial_crypto_data()
    {
        $updateData = [
            'price_eur' => 60000.00
        ];

        $response = $this->putJson("/api/v1/admin/cryptos/{$this->crypto->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'name' => 'Bitcoin', // Should remain unchanged
                'symbol' => 'BTC'    // Should remain unchanged
            ])
            ->assertJsonPath('price_eur', '60000.00000000'); // Check decimal string format
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_404_when_crypto_not_found_for_update()
    {
        $nonExistentId = 'NONEXISTENT123';

        $response = $this->putJson("/api/v1/admin/cryptos/{$nonExistentId}", [
            'name' => 'Updated Name'
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Crypto non trouvée'
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_update_data()
    {
        $response = $this->putJson("/api/v1/admin/cryptos/{$this->crypto->id}", [
            'price_eur' => 'invalid-price'
        ]);

        $response->assertStatus(422); // Validation error
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_delete_crypto_successfully()
    {
        $response = $this->deleteJson("/api/v1/admin/cryptos/{$this->crypto->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Crypto supprimée'
            ]);

        $this->assertDatabaseMissing('cryptomoney', [
            'id' => $this->crypto->id
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_404_when_crypto_not_found_for_delete()
    {
        $nonExistentId = 'NONEXISTENT123';

        $response = $this->deleteJson("/api/v1/admin/cryptos/{$nonExistentId}");

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Crypto non trouvée'
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_sync_cryptos_successfully()
    {
        $this->mock(CryptoService::class, function ($mock) {
            $mock->shouldReceive('addFromCoinGecko')
                ->once()
                ->with('bitcoin')
                ->andReturn(true);
        });

        $response = $this->postJson('/api/v1/admin/cryptos/sync');

        $response->assertStatus(200)
            ->assertJson([
                'updated' => 1,
                'failed' => 0,
                'errors' => []
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_sync_failures_gracefully()
    {
        // Create another crypto with coingecko_id
        $crypto2 = Cryptomoney::factory()->create([
            'name' => 'Ethereum',
            'symbol' => 'ETH',
            'price_eur' => 3000.00,
            'coingecko_id' => 'ethereum'
        ]);

        $this->mock(CryptoService::class, function ($mock) {
            $mock->shouldReceive('addFromCoinGecko')
                ->with('bitcoin')
                ->andReturn(true);
            
            $mock->shouldReceive('addFromCoinGecko')
                ->with('ethereum')
                ->andThrow(new \Exception('API Error'));
        });

        $response = $this->postJson('/api/v1/admin/cryptos/sync');

        $response->assertStatus(200)
            ->assertJson([
                'updated' => 1,
                'failed' => 1
            ]);

        // Check that errors array contains the failed crypto
        $responseData = $response->json();
        $this->assertCount(1, $responseData['errors']);
        $this->assertEquals('API Error', $responseData['errors'][0]['error']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_skips_cryptos_without_coingecko_id()
    {
        // Create crypto without coingecko_id
        $cryptoWithoutId = Cryptomoney::factory()->create([
            'name' => 'Custom Crypto',
            'symbol' => 'CUSTOM',
            'price_eur' => 100.00,
            'coingecko_id' => null
        ]);

        $this->mock(CryptoService::class, function ($mock) {
            $mock->shouldReceive('addFromCoinGecko')
                ->with('bitcoin')
                ->andReturn(true);
        });

        $response = $this->postJson('/api/v1/admin/cryptos/sync');

        $response->assertStatus(200)
            ->assertJson([
                'updated' => 1, // Only the bitcoin crypto
                'failed' => 0,
                'errors' => []
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

        // Test update
        $response = $this->putJson("/api/v1/admin/cryptos/{$this->crypto->id}", [
            'name' => 'Updated Name'
        ]);
        $response->assertStatus(403);

        // Test delete
        $response = $this->deleteJson("/api/v1/admin/cryptos/{$this->crypto->id}");
        $response->assertStatus(403);

        // Test sync
        $response = $this->postJson('/api/v1/admin/cryptos/sync');
        $response->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_401_for_unauthenticated_users()
    {
        // Create a new test instance without authentication
        $this->refreshApplication();

        // Test update without authentication
        $response = $this->putJson("/api/v1/admin/cryptos/{$this->crypto->id}", [
            'name' => 'Updated Name'
        ]);
        $response->assertStatus(401);

        // Test delete without authentication
        $response = $this->deleteJson("/api/v1/admin/cryptos/{$this->crypto->id}");
        $response->assertStatus(401);

        // Test sync without authentication
        $response = $this->postJson('/api/v1/admin/cryptos/sync');
        $response->assertStatus(401);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_sync_with_no_cryptos()
    {
        // Delete all cryptos
        Cryptomoney::query()->delete();

        $response = $this->postJson('/api/v1/admin/cryptos/sync');

        $response->assertStatus(200)
            ->assertJson([
                'updated' => 0,
                'failed' => 0,
                'errors' => []
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_crypto_service_exception_during_sync()
    {
        $this->mock(CryptoService::class, function ($mock) {
            $mock->shouldReceive('addFromCoinGecko')
                ->once()
                ->andThrow(new \Exception('Service indisponible'));
        });

        $response = $this->postJson('/api/v1/admin/cryptos/sync');

        $response->assertStatus(200)
            ->assertJson([
                'updated' => 0,
                'failed' => 1
            ]);

        $responseData = $response->json();
        $this->assertCount(1, $responseData['errors']);
        $this->assertEquals('Service indisponible', $responseData['errors'][0]['error']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}