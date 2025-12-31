<?php

namespace Tests\Feature\Controllers;

use App\Models\Cryptomoney;
use App\Models\CryptoHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CryptoControllerTest extends TestCase
{
    use RefreshDatabase;

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
                    'price_eur',
                    'image_url', // Votre modèle inclut image_url via l'accesseur
                    'price',     // Accesseur price
                    'change_24h' // Accesseur change_24h
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
            ->assertJson([
                'id' => $crypto->id,
                'name' => 'Bitcoin',
                'symbol' => 'BTC',
                'price_eur' => 50000
            ]);
    }

    /**
     * Test get non-existent crypto
     */
    public function test_get_non_existent_crypto()
    {
        $response = $this->getJson('/api/v1/cryptos/99999');

        $response->assertStatus(404)
            ->assertJson(['error' => 'Cryptomonnaie non trouvée']);
    }

    /**
     * Test get crypto history
     */
public function test_get_crypto_history()
{
    $crypto = Cryptomoney::factory()->create();
    
    // Create some history records
    CryptoHistory::factory()->count(5)->create([
        'cryptomoney_id' => $crypto->id
    ]);

    $response = $this->getJson("/api/v1/cryptos/{$crypto->id}/history");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'prices' => []
        ]);
    
    // Verify prices is an array of price arrays [timestamp, price]
    $data = $response->json();
    $this->assertArrayHasKey('prices', $data);
    $this->assertIsArray($data['prices']);
    
    if (count($data['prices']) > 0) {
        $firstPrice = $data['prices'][0];
        $this->assertIsArray($firstPrice);
        $this->assertCount(2, $firstPrice);
    }
}
}
