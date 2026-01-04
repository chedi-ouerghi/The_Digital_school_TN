<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration CORS sécurisée pour SPA.
    |
    | IMPORTANT :
    | - allowed_origins : JAMAIS '*' en production
    | - supports_credentials : TOUJOURS true (pour les cookies)
    | - allowed_methods : Restreindre aux méthodes nécessaires
    | - allowed_headers : Restreindre aux headers nécessaires
    |
    | Voir : https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    // Chemins qui acceptent les requêtes CORS
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // Méthodes HTTP autorisées
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    /**
     * SÉCURITÉ : Origines autorisées
     * 
     * Développement :
     *   - http://localhost:5173
     *   - http://127.0.0.1:5173
     * 
     * Production :
     *   - https://votredomaine.com
     * 
     * JAMAIS : ['*'] en production
     * 
     * Pour utiliser des env vars :
     *   explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173'))
     */
    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173,http://127.0.0.1:5173')),

    // Patterns pour les origines dynamiques (si besoin)
    'allowed_origins_patterns' => [
        // Exemple : '/^https?:\/\/(.*\.)?votredomaine\.com:?([0-9]+)?$/',
    ],

    /**
     * Headers autorisés dans la requête
     * 
     * SÉCURITÉ : Restreindre au strict nécessaire
     * - Content-Type
     * - Accept
     * - Authorization (deprecated, more for backward compat)
     * - X-XSRF-TOKEN (Sanctum CSRF)
     */
    'allowed_headers' => [
        'Content-Type',
        'Accept',
        'Accept-Language',
        'X-Requested-With',
        'X-XSRF-TOKEN',
        // Ne pas autoriser Authorization header en SPA (cookies instead)
    ],

    // Headers exposés dans la réponse
    'exposed_headers' => [],

    // Cache de la réponse CORS (en secondes)
    // Mettre à 3600 en production pour réduire les preflight requests
    'max_age' => env('CORS_MAX_AGE', 0),

    /**
     * SÉCURITÉ CRITIQUE : Toujours true pour les cookies
     * 
     * Si false : les navigateurs n'enverront PAS les cookies avec les requêtes cross-origin
     * Si true : les cookies sont inclus (credentials: 'include' côté frontend requis)
     */
    'supports_credentials' => true,

];