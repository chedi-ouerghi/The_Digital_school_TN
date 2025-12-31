<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CryptoController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PortefeuilleController;
use App\Http\Controllers\NotificationController;

// Admin Controllers (nouvelle structure)
use App\Http\Controllers\Admin\AdminCryptoController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminTransactionController;
use App\Http\Controllers\Admin\AdminStatsController;
use App\Http\Controllers\Admin\ClientAdminController;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTH (Public)
    |--------------------------------------------------------------------------
    */
    Route::post('login', [AuthController::class, 'login']);
    Route::post('request-account', [AuthController::class, 'requestAccount']);
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
    | Public Blogs
    |--------------------------------------------------------------------------
    */
    Route::get('blogs', [BlogController::class, 'index']);
    Route::get('blogs/{slug}', [BlogController::class, 'show']);

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
        Route::get('wallets/history', [PortefeuilleController::class, 'history']);
        Route::get('wallets/{id}/history', [PortefeuilleController::class, 'history']);
        Route::get('wallets/{id}', [PortefeuilleController::class, 'show']);
        Route::post('wallets/transaction', [PortefeuilleController::class, 'transact']);

        /*
        |--------------------------------------------------------------------------
        | NOTIFICATIONS
        |--------------------------------------------------------------------------
        */
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::put('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    });

    /*
    ============================================================================
    | ADMIN ROUTES
    ============================================================================
    */
    Route::middleware(['auth:sanctum', 'role:ADMIN'])->prefix('admin')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | ADMIN – BLOGS
        |--------------------------------------------------------------------------
        */
        Route::post('blogs', [BlogController::class, 'store']);
        Route::put('blogs/{id}', [BlogController::class, 'update']);
        Route::delete('blogs/{id}', [BlogController::class, 'destroy']);

        /*
        |--------------------------------------------------------------------------
        | ADMIN – CRYPTOS
        |--------------------------------------------------------------------------
        */
        Route::post('cryptos', [CryptoController::class, 'store']);
        Route::put('cryptos/{id}', [AdminCryptoController::class, 'update']);
        Route::delete('cryptos/{id}', [AdminCryptoController::class, 'destroy']);
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
