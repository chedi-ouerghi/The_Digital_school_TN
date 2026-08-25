<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Models\AccountRequest;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Cryptomoney;
use App\Models\CryptoWalletAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use App\Mail\TempPasswordMail;

class AdminUserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'role' => 'ADMIN',
            'email_verified_at' => now()
        ]);
        
        $this->client = User::factory()->create([
            'role' => 'CLIENT',
            'email_verified_at' => now()
        ]);
        
        Sanctum::actingAs($this->admin);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_list_all_clients_successfully()
    {
        // Créer plusieurs clients
        User::factory()->count(5)->create(['role' => 'CLIENT']);
        
        $response = $this->getJson('/api/v1/admin/clients');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'email', 'role']
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_empty_list_when_no_clients_exist()
    {
        // Supprimer tous les clients
        User::where('role', 'CLIENT')->delete();
        
        $response = $this->getJson('/api/v1/admin/clients');

        $response->assertStatus(200)
            ->assertJson([]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_client_details_successfully()
    {
        // Créer un wallet et transactions pour le client
        $wallet = Wallet::factory()->create(['user_id' => $this->client->id]);
        
        $response = $this->getJson("/api/v1/admin/clients/{$this->client->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id', 'name', 'email', 'role', 'wallet', 'account_balance', 'positions', 'transactions'
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_404_when_client_not_found()
    {
        $response = $this->getJson('/api/v1/admin/clients/99999');

        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_new_client_successfully()
    {
        Mail::fake();

        $clientData = [
            'name' => 'New Client',
            'email' => 'newclient@example.com',
            'role' => 'CLIENT'
        ];

        $response = $this->postJson('/api/v1/admin/clients', $clientData);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'name', 'email', 'role']);

        $this->assertDatabaseHas('users', [
            'email' => 'newclient@example.com',
            'role' => 'CLIENT'
        ]);

        // Vérifier qu'un email avec mot de passe temporaire a été envoyé
        Mail::assertSent(TempPasswordMail::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_client_creation_data()
    {
        $response = $this->postJson('/api/v1/admin/clients', [
            'name' => '',
            'email' => 'invalid-email'
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['error', 'details']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_prevents_duplicate_email_on_client_creation()
    {
        $response = $this->postJson('/api/v1/admin/clients', [
            'name' => 'Duplicate Client',
            'email' => $this->client->email,
            'role' => 'CLIENT'
        ]);

        $response->assertStatus(422);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_update_client_successfully()
    {
        $updateData = [
            'name' => 'Updated Client Name',
            'email' => 'updated@example.com'
        ];

        $response = $this->putJson("/api/v1/admin/clients/{$this->client->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('name', 'Updated Client Name');

        $this->assertDatabaseHas('users', [
            'id' => $this->client->id,
            'name' => 'Updated Client Name'
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_update_client_data()
    {
        $response = $this->putJson("/api/v1/admin/clients/{$this->client->id}", [
            'email' => 'invalid-email'
        ]);

        $response->assertStatus(422);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_delete_client_successfully()
    {
        $clientToDelete = User::factory()->create(['role' => 'CLIENT']);

        $response = $this->deleteJson("/api/v1/admin/clients/{$clientToDelete->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('users', [
            'id' => $clientToDelete->id
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_404_when_deleting_non_existent_client()
    {
        $response = $this->deleteJson('/api/v1/admin/clients/99999');

        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_get_client_transactions()
    {
        // Créer un wallet et des transactions
        $wallet = Wallet::factory()->create(['user_id' => $this->client->id]);
        $crypto = Cryptomoney::factory()->create();
        $asset = CryptoWalletAsset::factory()->create([
            'wallet_id' => $wallet->id,
            'cryptomoney_id' => $crypto->id
        ]);
        Transaction::factory()->count(3)->create([
            'crypto_wallet_asset_id' => $asset->id
        ]);

        $response = $this->getJson("/api/v1/admin/clients/{$this->client->id}/transactions");

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'type', 'quantity', 'created_at']
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_401_for_unauthenticated_users()
    {
        $this->refreshApplication();
        
        $response = $this->getJson('/api/v1/admin/clients');

        $response->assertStatus(401);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_403_for_non_admin_users()
    {
        // Authentifier en tant que client
        Sanctum::actingAs($this->client);
        
        $response = $this->getJson('/api/v1/admin/clients');

        $response->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_list_account_requests()
    {
        // Créer plusieurs demandes de compte
        AccountRequest::factory()->count(3)->create(['status' => 'PENDING']);

        $response = $this->getJson('/api/v1/admin/account-requests');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'email', 'status', 'created_at']
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_approve_account_request()
    {
        Mail::fake();

        $accountRequest = AccountRequest::factory()->create(['status' => 'PENDING']);

        $response = $this->postJson("/api/v1/admin/account-requests/{$accountRequest->id}/approve");

        $response->assertStatus(200);

        $this->assertDatabaseHas('account_requests', [
            'id' => $accountRequest->id,
            'status' => 'APPROVED'
        ]);

        // Vérifier qu'un nouvel utilisateur a été créé
        $this->assertDatabaseHas('users', [
            'email' => $accountRequest->email,
            'role' => 'CLIENT'
        ]);

        // Vérifier qu'un wallet a été créé
        $user = User::where('email', $accountRequest->email)->first();
        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_reject_account_request()
    {
        $accountRequest = AccountRequest::factory()->create(['status' => 'PENDING']);

        $response = $this->postJson("/api/v1/admin/account-requests/{$accountRequest->id}/reject", [
            'rejection_reason' => 'Does not meet requirements'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('account_requests', [
            'id' => $accountRequest->id,
            'status' => 'REJECTED'
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_404_when_account_request_not_found()
    {
        $response = $this->postJson('/api/v1/admin/account-requests/99999/approve');

        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_400_when_approving_already_approved_request()
    {
        $accountRequest = AccountRequest::factory()->create(['status' => 'APPROVED']);

        $response = $this->postJson("/api/v1/admin/account-requests/{$accountRequest->id}/approve");

        $response->assertStatus(400);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Sanctum::actingAs(null);
    }
}
