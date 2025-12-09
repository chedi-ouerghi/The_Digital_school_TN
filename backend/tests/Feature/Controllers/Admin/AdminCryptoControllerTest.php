<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\User;
use App\Models\Cryptomoney;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCryptoControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test edit crypto as admin
     */
    public function test_edit_crypto_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $crypto = Cryptomoney::factory()->create();
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/v1/admin/cryptos/{$crypto->id}/edit");

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'name', 'symbol', 'price_eur']);
    }

    /**
     * Test edit non-existent crypto as admin
     */
    public function test_edit_non_existent_crypto_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/cryptos/99999/edit');

        $response->assertStatus(404)
            ->assertJson(['error' => 'Crypto non trouvée']);
    }

    /**
     * Test update crypto as admin
     */
    public function test_update_crypto_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $crypto = Cryptomoney::factory()->create([
            'name' => 'Bitcoin',
            'price_eur' => 50000
        ]);
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/v1/admin/cryptos/{$crypto->id}", [
                'name' => 'Bitcoin Updated',
                'price_eur' => 55000
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('cryptomoney', [
            'id' => $crypto->id,
            'name' => 'Bitcoin Updated',
            'price_eur' => 55000
        ]);
    }

    /**
     * Test update non-existent crypto as admin
     */
    public function test_update_non_existent_crypto_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/v1/admin/cryptos/99999', [
                'name' => 'Bitcoin',
                'price_eur' => 50000
            ]);

        $response->assertStatus(404)
            ->assertJson(['error' => 'Crypto non trouvée']);
    }

    /**
     * Test delete crypto as admin
     */
    public function test_delete_crypto_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $crypto = Cryptomoney::factory()->create();
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/v1/admin/cryptos/{$crypto->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Crypto supprimée']);

        $this->assertDatabaseMissing('cryptomoney', ['id' => $crypto->id]);
    }

    /**
     * Test delete non-existent crypto as admin
     */
    public function test_delete_non_existent_crypto_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson('/api/v1/admin/cryptos/99999');

        $response->assertStatus(404)
            ->assertJson(['error' => 'Crypto non trouvée']);
    }

    /**
     * Test sync crypto history as admin
     */
    public function test_sync_crypto_history_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/admin/cryptos/sync-history');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'message']);
    }

    /**
     * Test sync crypto history as non-admin
     */
    public function test_sync_crypto_history_as_non_admin()
    {
        $user = User::factory()->create(['role' => 'CLIENT']);
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/admin/cryptos/sync-history');

        $response->assertStatus(403);
    }
}
