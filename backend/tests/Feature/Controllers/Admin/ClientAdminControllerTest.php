<?php

namespace Tests\Feature\Controllers\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Cryptomoney;
use App\Models\CryptoWalletAsset;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class ClientAdminControllerTest extends TestCase
{
    use RefreshDatabase;    

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_client_portfolio_successfully()
    {
        // Créer un administrateur authentifié
        $admin = User::factory()->create(['role' => 'ADMIN']);
        Sanctum::actingAs($admin);

        // Créer un client avec un wallet
        $client = User::factory()->create(['role' => 'CLIENT']);
        $wallet = Wallet::factory()->create(['user_id' => $client->id]);

        // Créer une cryptomonnaie
        $crypto = Cryptomoney::factory()->create(['symbol' => 'BTC', 'price_eur' => 50000.00]);

        // Créer une position dans le wallet
        $position = CryptoWalletAsset::factory()->create([
            'wallet_id' => $wallet->id,
            'cryptomoney_id' => $crypto->id,
            'quantity' => 0.5,
            'average_buy_price' => 48000.00
        ]);

        // Créer une transaction
        $transaction = Transaction::factory()->create([
            'crypto_wallet_asset_id' => $position->id,
            'cryptomoney_id' => $crypto->id,
            'type' => 'ACHAT',
            'quantity' => 0.5,
            'price' => 48000.00,
            'total_eur' => 24000.00
        ]);

        // Appeler l'endpoint
        $response = $this->getJson("/api/v1/admin/clients/{$client->id}/wallet");

        // Vérifier la réponse
        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'portfolio' => [
                    'id', 'balance_eur', 'valeur_totale', 'plus_value_totale',
                    'cryptos', 'transactions'
                ]
            ])
            ->assertJson([
                'user' => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email
                ]
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_404_when_client_not_found()
    {
        // Créer un administrateur authentifié
        $admin = User::factory()->create(['role' => 'ADMIN']);
        Sanctum::actingAs($admin);

        // ID de client qui n'existe pas
        $nonExistentClientId = 'NONEXISTENT123';

        // Appeler l'endpoint
        $response = $this->getJson("/api/v1/admin/clients/{$nonExistentClientId}/wallet");

        // Vérifier la réponse
        $response->assertStatus(404)
            ->assertJson(['error' => 'Utilisateur non trouvé.']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_404_when_client_has_no_wallet()
    {
        // Créer un administrateur authentifié
        $admin = User::factory()->create(['role' => 'ADMIN']);
        Sanctum::actingAs($admin);

        // Créer un client sans wallet
        $client = User::factory()->create(['role' => 'CLIENT']);

        // Appeler l'endpoint
        $response = $this->getJson("/api/v1/admin/clients/{$client->id}/wallet");

        // Vérifier la réponse
        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Aucun wallet trouvé pour cet utilisateur.',
                'user' => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email
                ]
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_403_for_non_admin_users()
    {
        // Créer un client authentifié (non admin)
        $client = User::factory()->create(['role' => 'CLIENT']);
        Sanctum::actingAs($client);

        // Créer un autre client avec wallet
        $otherClient = User::factory()->create(['role' => 'CLIENT']);
        $wallet = Wallet::factory()->create(['user_id' => $otherClient->id]);

        // Appeler l'endpoint
        $response = $this->getJson("/api/v1/admin/clients/{$otherClient->id}/wallet");

        // Vérifier que l'accès est refusé
        $response->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_401_for_unauthenticated_users()
    {
        // Créer un client avec wallet
        $client = User::factory()->create(['role' => 'CLIENT']);
        $wallet = Wallet::factory()->create(['user_id' => $client->id]);

        // Appeler l'endpoint sans authentification
        $response = $this->getJson("/api/v1/admin/clients/{$client->id}/wallet");

        // Vérifier que l'accès est non autorisé
        $response->assertStatus(401);
    }
}