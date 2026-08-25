<?php

namespace Tests\Feature\Controllers;

use App\Models\Cryptomoney;
use App\Models\CryptoHistory;
use Tests\TestCase;

class CryptoControllerTest extends TestCase
{

    /**
     * Test get all cryptos (public endpoint)
     */

public function test_get_all_cryptos()
{
    Cryptomoney::factory()->count(5)->create();

    $response = $this->getJson('/api/v1/cryptos');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 
                    'name', 
                    'symbol', 
                    'price_eur'
                ]
            ]
        ]);
}

    /**
     * Test get single crypto by id
     */
    public function test_get_crypto_by_id()
    {
        $crypto = Cryptomoney::factory()->create([
            'name' => 'Bitcoin',
            'symbol' => 'BTC',
            'price_eur' => 50000
        ]);

        $response = $this->getJson("/api/v1/cryptos/{$crypto->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'name', 'symbol', 'price_eur']);
    }

    /**
     * Test get non-existent crypto
     */
    public function test_get_non_existent_crypto()
    {
        $response = $this->getJson('/api/v1/cryptos/99999');

        $response->assertStatus(404)
            ->assertJson(['error' => 'Cryptocurrency not found']);
    }

    /**
     * Test get crypto history
     */
public function test_get_crypto_history()
{
    $crypto = Cryptomoney::factory()->create();
    
    // Create some history records with distinct dates to respect UNIQUE(cryptomoney_id, recorded_at)
    for ($i = 0; $i < 5; $i++) {
        CryptoHistory::factory()->create([
            'cryptomoney_id' => $crypto->id,
            'recorded_at' => now()->subDays($i)->toDateString(),
        ]);
    }

    $response = $this->getJson("/api/v1/cryptos/{$crypto->id}/history");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'crypto' => ['id', 'symbol', 'name'],
            'meta' => ['count', 'from', 'to', 'days'],
            'history' => []
        ]);
    
    // Verify history is an array of history objects
    $data = $response->json();
    $this->assertArrayHasKey('history', $data);
    $this->assertIsArray($data['history']);
    
    if (count($data['history']) > 0) {
        $firstHistory = $data['history'][0];
        $this->assertArrayHasKey('timestamp', $firstHistory);
        $this->assertArrayHasKey('price', $firstHistory);
    }
}
}
