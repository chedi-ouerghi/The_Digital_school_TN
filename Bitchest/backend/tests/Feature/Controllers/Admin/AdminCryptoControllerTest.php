<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\User;
use App\Models\Cryptomoney;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AdminCryptoControllerTest extends TestCase
{

    /**
     * Test get crypto as admin
     */
    public function test_edit_crypto_as_admin()
    {
        $admin = $this->createAuthenticatedUser(['role' => 'ADMIN']);
        $crypto = Cryptomoney::factory()->create();

        $response = $this->authenticatedJson('GET', "/api/v1/cryptos/{$crypto->id}", [], $admin);

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'name', 'symbol', 'price_eur']);
    }

    /**
     * Test get non-existent crypto as admin
     */
    public function test_edit_non_existent_crypto_as_admin()
    {
        $admin = $this->createAuthenticatedUser(['role' => 'ADMIN']);

        $response = $this->authenticatedJson('GET', '/api/v1/cryptos/99999', [], $admin);

        $response->assertStatus(404);
    }



    /**
     * Test sync crypto history as admin
     */
    public function test_sync_crypto_history_as_admin()
    {
        $admin = $this->createAuthenticatedUser(['role' => 'ADMIN']);

        $response = $this->authenticatedJson('POST', '/api/v1/admin/cryptos/sync-history', [], $admin);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'message']);
    }

    /**
     * Test sync crypto history as non-admin
     */
    public function test_sync_crypto_history_as_non_admin()
    {
        $user = $this->createAuthenticatedUser(['role' => 'CLIENT']);

        $response = $this->authenticatedJson('POST', '/api/v1/admin/cryptos/sync-history', [], $user);

        $response->assertStatus(403);
    }
}
