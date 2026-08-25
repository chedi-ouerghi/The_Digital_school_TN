<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\AccountRequest;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewAccountRequestMail;

class AccountRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_submit_account_request_successfully()
    {
        // Simuler l'envoi d'emails
        Mail::fake();

        // Créer un administrateur pour recevoir les notifications
        User::factory()->create([
            'role' => 'ADMIN',
            'email' => 'admin@example.com'
        ]);

        // Données de la demande
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com'
        ];

        // Appeler l'endpoint
        $response = $this->postJson('/api/v1/request-account', $requestData);

        // Vérifier la réponse
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Your request has been successfully sent..'
            ]);

        // Vérifier que la demande a été créée en base
        $this->assertDatabaseHas('account_requests', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'status' => 'PENDING'
        ]);

        // Vérifier que les emails ont été envoyés aux admins
        Mail::assertSent(NewAccountRequestMail::class, function ($mail) use ($requestData) {
            // Use reflection to access protected property
            $reflection = new \ReflectionClass($mail);
            $property = $reflection->getProperty('accountRequest');
            $property->setAccessible(true);
            $accountRequest = $property->getValue($mail);
            return $accountRequest->email === $requestData['email'];
        });
    }

    /** @test */
    public function it_returns_validation_error_when_name_is_missing()
    {
        // Données invalides (nom manquant)
        $requestData = [
            'email' => 'john.doe@example.com'
        ];

        // Appeler l'endpoint
        $response = $this->postJson('/api/v1/request-account', $requestData);

        // Vérifier la réponse de validation
        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Erreur de validation',
                'details' => [
                    'name' => ['The name field is required.']
                ]
            ]);
    }

    /** @test */
    public function it_returns_validation_error_when_email_is_invalid()
    {
        // Données invalides (email invalide)
        $requestData = [
            'name' => 'John Doe',
            'email' => 'invalid-email'
        ];

        // Appeler l'endpoint
        $response = $this->postJson('/api/v1/request-account', $requestData);

        // Vérifier la réponse de validation
        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Erreur de validation',
                'details' => [
                    'email' => ['The email field must be a valid email address.']
                ]
            ]);
    }

    /** @test */
    public function it_returns_validation_error_when_email_already_exists_in_users()
    {
        // Créer un utilisateur existant
        $existingUser = User::factory()->create([
            'email' => 'existing.user@example.com'
        ]);

        // Données avec email existant
        $requestData = [
            'name' => 'John Doe',
            'email' => 'existing.user@example.com'
        ];

        // Appeler l'endpoint
        $response = $this->postJson('/api/v1/request-account', $requestData);

        // Vérifier la réponse de validation - should get Laravel validation error for existing user
        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Erreur de validation',
                'details' => [
                    'email' => ['The email has already been taken.']
                ]
            ]);
    }

    /** @test */
    public function it_returns_validation_error_when_email_already_exists_in_pending_requests()
    {
        // Use an email that doesn't exist in users table but exists in pending requests
        $testEmail = 'unique.pending.request.' . uniqid() . '@example.com';
        
        // Créer une demande en attente existante (but no existing user)
        $existingRequest = AccountRequest::factory()->create([
            'email' => $testEmail,
            'status' => 'PENDING'
        ]);

        // Données avec email existant dans les demandes en attente
        $requestData = [
            'name' => 'John Doe',
            'email' => $testEmail
        ];

        // Appeler l'endpoint
        $response = $this->postJson('/api/v1/request-account', $requestData);

        // Vérifier la réponse de validation - should get the pending request error
        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Une demande est déjà en attente pour cet email.'
            ]);
    }

    /** @test */
    public function it_creates_notifications_for_admins()
    {
        // Simuler l'envoi d'emails
        Mail::fake();

        // Créer des administrateurs
        $admin1 = User::factory()->create(['role' => 'ADMIN']);
        $admin2 = User::factory()->create(['role' => 'ADMIN']);

        // Données de la demande
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com'
        ];

        // Appeler l'endpoint
        $response = $this->postJson('/api/v1/request-account', $requestData);

        // Vérifier la réponse
        $response->assertStatus(200);

        // Vérifier que les notifications ont été créées pour les admins
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin1->id,
            'title' => 'Nouvelle demande de compte'
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin2->id,
            'title' => 'Nouvelle demande de compte'
        ]);
    }

    /** @test */
    public function it_handles_email_failures_gracefully()
    {
        // Simuler l'échec d'envoi d'emails
        Mail::shouldReceive('send')->andThrow(new \Exception('Email failed'));

        // Créer des administrateurs
        User::factory()->create(['role' => 'ADMIN']);

        // Données de la demande
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com'
        ];

        // Appeler l'endpoint - devrait quand même réussir malgré l'échec d'email
        $response = $this->postJson('/api/v1/request-account', $requestData);

        // Vérifier que la demande a été créée malgré l'échec d'email
        $response->assertStatus(200);
        $this->assertDatabaseHas('account_requests', [
            'email' => 'john.doe@example.com',
            'status' => 'PENDING'
        ]);
    }
}