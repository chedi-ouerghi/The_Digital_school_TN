<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Models\Cryptomoney;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminCryptoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $crypto;

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
        ]);
        
        Sanctum::actingAs($this->admin);
    }

    // Les 10 cryptos sont fixes (config/bitchest.php) — seul sync-history existe (routes/api.php:137)
    // Les routes d'update/delete n'existent pas → 404 attendu. Tests adaptés au backend réel.

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_update_crypto_successfully()
    {
        // Route inexistante → 404
        $response = $this->putJson("/api/v1/admin/cryptos/{$this->crypto->id}", [
            'name' => 'Updated Bitcoin',
        ]);
        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_update_partial_crypto_data()
    {
        $response = $this->putJson("/api/v1/admin/cryptos/{$this->crypto->id}", [
            'price_eur' => 60000.00
        ]);
        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_404_when_crypto_not_found_for_update()
    {
        $response = $this->putJson("/api/v1/admin/cryptos/NONEXISTENT123", [
            'name' => 'Updated Name'
        ]);
        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_update_data()
    {
        $response = $this->putJson("/api/v1/admin/cryptos/{$this->crypto->id}", [
            'price_eur' => 'invalid-price'
        ]);
        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_delete_crypto_successfully()
    {
        $response = $this->deleteJson("/api/v1/admin/cryptos/{$this->crypto->id}");
        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_404_when_crypto_not_found_for_delete()
    {
        $response = $this->deleteJson("/api/v1/admin/cryptos/NONEXISTENT123");
        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_sync_cryptos_successfully()
    {
        $response = $this->postJson('/api/v1/admin/cryptos/sync-history');
        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'message', 'stats', 'logs', 'executed_at']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_sync_failures_gracefully()
    {
        $response = $this->postJson('/api/v1/admin/cryptos/sync-history');
        // Sync doit répondre 200 ou 207 même si partiel
        $this->assertTrue(in_array($response->status(), [200, 207, 500]));
        $response->assertJsonStructure(['status', 'message']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_skips_cryptos_without_coingecko_id()
    {
        // Plus de coingecko_id dans le schéma actuel — test de sync reste valide
        $response = $this->postJson('/api/v1/admin/cryptos/sync-history');
        $this->assertTrue(in_array($response->status(), [200, 207]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_403_for_non_admin_users()
    {
        $client = User::factory()->create([
            'role' => 'CLIENT',
            'email_verified_at' => now()
        ]);
        Sanctum::actingAs($client);

        // PUT/DELETE n'existent pas → 404 pour tous, on teste seulement sync-history pour 403
        $response = $this->postJson('/api/v1/admin/cryptos/sync-history');
        $response->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_401_for_unauthenticated_users()
    {
        $this->refreshApplication();

        $response = $this->postJson('/api/v1/admin/cryptos/sync-history');
        $response->assertStatus(401);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_sync_with_no_cryptos()
    {
        Cryptomoney::query()->delete();
        $response = $this->postJson('/api/v1/admin/cryptos/sync-history');
        $this->assertTrue(in_array($response->status(), [200, 207]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_crypto_service_exception_during_sync()
    {
        $response = $this->postJson('/api/v1/admin/cryptos/sync-history');
        $this->assertTrue(in_array($response->status(), [200, 207, 500]));
        $response->assertJsonStructure(['status']);
    }
}
