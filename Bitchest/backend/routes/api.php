<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CryptoController;
use App\Http\Controllers\PortefeuilleController;
use App\Http\Controllers\NotificationController;

// Admin Controllers (nouvelle structure)
use App\Http\Controllers\Admin\AdminCryptoController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminTransactionController;
use App\Http\Controllers\Admin\AdminStatsController;
use App\Http\Controllers\Admin\ClientAdminController;

/**
 * 🔥 CSRF COOKIE ROUTE (HORS du préfixe v1)
 * 
 * Cette route DOIT être accessible sans le préfixe v1
 * Sanctum l'utilise pour initialiser le token XSRF
 * 
 * Frontend : await fetch('http://localhost:8000/sanctum/csrf-cookie', { credentials: 'include' })
 * 
 * IMPORTANT: Le middleware 'web' gère automatiquement :
 * - Les cookies de session
 * - La génération et l'envoi du token XSRF
 * - La validation des tokens CSRF
 */
Route::middleware('web')->group(function () {
    Route::get('/sanctum/csrf-cookie', function () {
        // Le middleware 'web' génère et envoie automatiquement le cookie XSRF-TOKEN
        // Cette route retourne simplement une confirmation
        return response()->json([
            'message' => 'CSRF token initialized',
            'timestamp' => now()->toIso8601String()
        ])->header('Content-Type', 'application/json');
    })->name('csrf-cookie');
});

Route::prefix('v1')
    ->middleware('web')  // 🔥 CRITIQUE : Ajouter le middleware 'web' pour les sessions Sanctum SPA
    ->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTH (Public)
    |--------------------------------------------------------------------------
    */
    Route::post('login', [AuthController::class, 'login']);
    Route::post('request-account', [AuthController::class, 'requestAccount']);
    
    /**
     * 🔥 CSRF Protection: verify-email endpoint
     * IMPORTANT: Ces routes POST doivent avoir le middleware CSRF pour la sécurité
     * Le middleware 'web' (appliqué au préfixe v1) gère automatiquement CSRF
     * Le frontend DOIT appeler /sanctum/csrf-cookie AVANT ces requêtes
     * et envoyer le header X-XSRF-TOKEN avec le token du cookie
     */
    Route::post('verify-email', [AuthController::class, 'verifyEmail']);

    /*
    |--------------------------------------------------------------------------
    | Public Cryptos
    |--------------------------------------------------------------------------
    */
    Route::get('cryptos', [CryptoController::class, 'index']);
    Route::get('cryptos/{id}', [CryptoController::class, 'show']);
    Route::get('cryptos/{id}/history', [CryptoController::class, 'history']);

    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATED ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('profile', [AuthController::class, 'profile']);
        Route::put('profile', [AuthController::class, 'update']);
        Route::put('profile/password', [AuthController::class, 'changePassword']);

        /*
        |--------------------------------------------------------------------------
        | PROFILE MANAGEMENT
        |--------------------------------------------------------------------------
        */
        Route::get('profile/stats', [ProfileController::class, 'getProfileOverview']);

        // Upload / delete pictures & banners
        Route::post('profile/picture/upload', [ProfileController::class, 'uploadProfilePicture']);
        Route::put('profile/picture', [ProfileController::class, 'uploadProfilePicture']);
        Route::delete('profile/picture', [ProfileController::class, 'deleteProfilePicture']);

        Route::post('profile/banner/upload', [ProfileController::class, 'uploadProfileBanner']);
        Route::put('profile/banner', [ProfileController::class, 'uploadProfileBanner']);
        Route::delete('profile/banner', [ProfileController::class, 'deleteProfileBanner']);

        /*
        |--------------------------------------------------------------------------
        | WALLET / PORTEFEUILLE
        |--------------------------------------------------------------------------
        */
        Route::get('wallets', [PortefeuilleController::class, 'index']);
        Route::get('wallets/plus-value', [PortefeuilleController::class, 'plusValue']);
        Route::get('wallets/transactions/history', [PortefeuilleController::class, 'transactionsHistory']);
        Route::get('wallets/history', [PortefeuilleController::class, 'history']);
        Route::get('wallets/{id}/history', [PortefeuilleController::class, 'history']);
        Route::get('wallets/{id}', [PortefeuilleController::class, 'show']);
        // SECURITE - Etape 1 : auth:sanctum garantit qu'un utilisateur authentifie
        // est necessaire avant d'atteindre le traitement d'une transaction.
        Route::post('wallets/transaction', [PortefeuilleController::class, 'transact']);

        /*
        |--------------------------------------------------------------------------
        | NOTIFICATIONS
        |--------------------------------------------------------------------------
        */
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    });

    /*
    ============================================================================
    | ADMIN ROUTES
    ============================================================================
    */
    Route::middleware(['auth:sanctum', 'role:ADMIN'])->prefix('admin')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | ADMIN – CRYPTOS (Synchronisation uniquement, les 10 cryptos sont fixes)
        |--------------------------------------------------------------------------
        */
        Route::post('cryptos/sync-history', [AdminCryptoController::class, 'syncHistory']);

        /*
        |--------------------------------------------------------------------------
        | ADMIN – CLIENTS
        |--------------------------------------------------------------------------
        */
        Route::get('clients', [AdminUserController::class, 'index']);
        Route::post('clients', [AdminUserController::class, 'store']);
        Route::get('clients/{id}', [AdminUserController::class, 'show']);
        Route::put('clients/{id}', [AdminUserController::class, 'update']);
        Route::delete('clients/{id}', [AdminUserController::class, 'destroy']);
        Route::get('clients/{id}/transactions', [AdminUserController::class, 'transactions']);

        /*
        |--------------------------------------------------------------------------
        | ADMIN – ACCOUNT REQUESTS
        |--------------------------------------------------------------------------
        */
        Route::get('account-requests', [AdminUserController::class, 'accountRequests']);
        Route::post('account-requests/{id}/approve', [AdminUserController::class, 'approveRequest']);
        Route::post('account-requests/{id}/reject', [AdminUserController::class, 'rejectRequest']);

        /*
        |--------------------------------------------------------------------------
        | ADMIN – TRANSACTIONS
        |--------------------------------------------------------------------------
        */
        Route::get('transactions', [AdminTransactionController::class, 'index']);
        Route::get('transactions/{id}', [AdminTransactionController::class, 'show']);
        Route::post('transactions/{id}/cancel', [AdminTransactionController::class, 'cancel']);

        /*
        |--------------------------------------------------------------------------
        | ADMIN – STATS
        |--------------------------------------------------------------------------
        */
        Route::get('stats', [AdminStatsController::class, 'index']);

        /*
        |--------------------------------------------------------------------------
        | ADMIN – SPECIAL AUTH
        |--------------------------------------------------------------------------
        */
        Route::put('change-id', [AuthController::class, 'changeId']);
    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN – CLIENT PORTFOLIO ACCESS
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'role:ADMIN'])->prefix('admin/clients')->group(function () {
        Route::get('{id}/wallet', [ClientAdminController::class, 'getPortfolio']);
    });

});
