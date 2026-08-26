<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{

    /**
     * Obtenir le token CSRF pour les requêtes
     * Route réelle: GET /sanctum/csrf-cookie -> 200 JSON {message,timestamp} + cookie XSRF-TOKEN (routes/api.php:30-39)
     */
    protected function getCsrfToken(): string
    {
        $response = $this->get('/sanctum/csrf-cookie');
        // Accepte 200 (notre implémentation) ou 204 (Sanctum par défaut)
        $this->assertTrue(in_array($response->status(), [200, 204]), 'CSRF cookie should return 200 or 204, got '.$response->status());
        
        // Récupérer le cookie XSRF-TOKEN
        $cookies = $response->headers->getCookies();
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'XSRF-TOKEN') {
                return urldecode($cookie->getValue());
            }
        }
        // Fallback: header Set-Cookie brut (certains drivers test)
        $setCookie = $response->headers->get('Set-Cookie');
        if ($setCookie && preg_match('/XSRF-TOKEN=([^;]+)/', $setCookie, $m)) {
            return urldecode($m[1]);
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
