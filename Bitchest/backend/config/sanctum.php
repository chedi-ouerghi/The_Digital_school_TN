<?php

use Laravel\Sanctum\Sanctum;

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains (SPA Mode)
    |--------------------------------------------------------------------------
    |
    | Domaines qui utiliseront l'authentification STATEFUL (cookies session)
    | Les requêtes depuis ces domaines recevront des tokens de session
    | au lieu de bearer tokens.
    |
    | SÉCURITÉ : Ajouter votre domaine frontend (production et dev)
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,localhost:5173,127.0.0.1,127.0.0.1:8000,127.0.0.1:5173,::1',
        Sanctum::currentApplicationUrlWithPort(),
    ))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    |
    | 'web' = Mode SPA (Sanctum utilise les cookies de session)
    | 'sanctum' = Mode API (requêtes avec Bearer tokens)
    |
    | Pour un SPA avec authentification par cookies : use 'web'
    |
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Token Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | Cette valeur contrôle l'expiration du token Sanctum.
    | null = pas d'expiration (recommandé pour SPA avec session)
    | Pour les API mobiles : définir entre 24*60 et 30*24*60 minutes
    |
    */

    'expiration' => env('SANCTUM_TOKEN_EXPIRATION', null),

    /*
    |--------------------------------------------------------------------------
    | Token Prefix
    |--------------------------------------------------------------------------
    |
    | Préfixe des tokens Sanctum pour éviter les collisions avec d'autres tokens.
    | Exemple : 'bitchest_'
    |
    */

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware utilisés par Sanctum pour gérer l'authentification SPA.
    |
    | SÉCURITÉ :
    | - AuthenticateSession : valide que la session est toujours valide
    | - EncryptCookies : chiffre les cookies (incluant XSRF-TOKEN)
    | - ValidateCsrfToken : protection CSRF automatique
    |
    */

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],

];
