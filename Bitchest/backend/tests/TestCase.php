<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{

    /**
     * Obtenir le token CSRF pour les requêtes
     */
    protected function getCsrfToken(): string
    {
        $response = $this->get('/sanctum/csrf-cookie');
        $response->assertStatus(204); // Le endpoint retourne 204 No Content
        
        // Récupérer le cookie XSRF-TOKEN
        $cookies = $response->headers->getCookies();
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'XSRF-TOKEN') {
                return $cookie->getValue();
            }
        }
        
        return '';
    }

    /**
     * Créer un utilisateur authentifié avec token CSRF
     */
    protected function createAuthenticatedUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        Sanctum::actingAs($user);
        
        // Obtenir le token CSRF
        $csrfToken = $this->getCsrfToken();
        
        // Stocker le token pour les futures requêtes
        $this->withHeader('X-XSRF-TOKEN', $csrfToken);
        
        return $user;
    }

    /**
     * Faire une requête JSON authentifiée
     */
    protected function authenticatedJson(string $method, string $uri, array $data = [], User $user = null)
    {
        if ($user) {
            Sanctum::actingAs($user);
        }
        
        // S'assurer que le token CSRF est inclus
        if (!str_contains($uri, 'csrf-cookie')) {
            $csrfToken = $this->getCsrfToken();
            $this->withHeader('X-XSRF-TOKEN', $csrfToken);
        }
        
        return $this->json($method, $uri, $data);
    }
}
