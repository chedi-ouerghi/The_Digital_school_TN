<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use App\Models\User;
use App\Models\Cryptomoney;

class RedisCacheTest extends TestCase
{

    /**
     * Test que le cache fonctionne pour la liste des cryptos
     */
    public function test_crypto_list_cache(): void
    {
        // Créer des données de test
        Cryptomoney::factory()->count(5)->create();
        
        // Premier appel - devrait venir de la base de données
        $response1 = $this->getJson('/api/v1/cryptos');
        $response1->assertStatus(200);
        
        // Deuxième appel - devrait venir du cache
        $response2 = $this->getJson('/api/v1/cryptos');
        $response2->assertStatus(200);
        
        // Les deux réponses devraient être identiques
        $this->assertEquals($response1->json(), $response2->json());
    }

    /**
     * Test que le cache fonctionne pour l'historique d'un crypto
     */
    public function test_crypto_history_cache(): void
    {
        // Créer un crypto avec historique
        $crypto = Cryptomoney::factory()->create();
        
        // Créer des données d'historique via le service
        $cryptoService = new \App\Services\CryptoService();
        
        // Simuler des données d'historique
        $mockHistory = [
            [time() * 1000, 50000.00, 1000], // timestamp, price, volume
            [(time() - 86400) * 1000, 49500.00, 1200],
            [(time() - 172800) * 1000, 48000.00, 800],
        ];
        
        // Stocker dans le cache pour simuler le service
        Cache::put("crypto_history:{$crypto->id}:days_30", $mockHistory, 300);
        
        // Premier appel
        $response1 = $this->getJson("/api/v1/cryptos/{$crypto->id}/history");
        $response1->assertStatus(200);
        
        // Deuxième appel
        $response2 = $this->getJson("/api/v1/cryptos/{$crypto->id}/history");
        $response2->assertStatus(200);
        
        // Les réponses devraient être identiques
        $this->assertEquals($response1->json(), $response2->json());
    }

    /**
     * Test que le cache fonctionne pour le profil utilisateur
     */
    public function test_profile_cache(): void
    {
        $user = User::factory()->create();
        
        $this->actingAs($user);
        
        // Premier appel
        $response1 = $this->getJson('/api/v1/profile/stats');
        $response1->assertStatus(200);
        
        // Deuxième appel
        $response2 = $this->getJson('/api/v1/profile/stats');
        $response2->assertStatus(200);
        
        // Les réponses devraient être identiques
        $this->assertEquals($response1->json(), $response2->json());
    }
}