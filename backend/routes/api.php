<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CryptoController;
use App\Http\Controllers\PortefeuilleController;
use App\Http\Controllers\AdminTransactionController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\ClientAdminController;

Route::prefix('v1')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('/request-account', [AuthController::class, 'requestAccount']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('profile', [AuthController::class, 'profile']);
        Route::put('profile', [AuthController::class, 'update']);
        Route::post('profile/password', [AuthController::class, 'changePassword']);

        Route::get('/wallets', [PortefeuilleController::class, 'index']);
        Route::get('/wallets/plus-value', [PortefeuilleController::class, 'plusValue']);
        Route::get('/wallets/history', [PortefeuilleController::class, 'history']);
        Route::get('/wallets/{id}/history', [PortefeuilleController::class, 'history']);
        Route::get('/wallets/{id}', [PortefeuilleController::class, 'show']);

        Route::post('/wallets/transaction', [PortefeuilleController::class, 'transact']);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::put('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    });

    Route::get('cryptos', [CryptoController::class, 'index']);
    Route::get('cryptos/{id}', [CryptoController::class, 'show']);
    Route::get('cryptos/{id}/history', [CryptoController::class, 'history']);

    Route::middleware(['auth:sanctum', 'role:ADMIN'])->group(function () {
        Route::post('cryptos', [CryptoController::class, 'store']);
        Route::get('admin/cryptos/{id}/edit', [\App\Http\Controllers\AdminCryptoController::class, 'edit']);
        Route::put('admin/cryptos/{id}', [\App\Http\Controllers\AdminCryptoController::class, 'update']);
        Route::delete('admin/cryptos/{id}', [\App\Http\Controllers\AdminCryptoController::class, 'destroy']);
        Route::post('admin/cryptos/sync', [\App\Http\Controllers\AdminCryptoController::class, 'sync']);

        Route::get('admin/clients', [AdminUserController::class, 'index']);
        Route::post('admin/clients', [AdminUserController::class, 'store']);
        Route::get('admin/clients/{id}', [AdminUserController::class, 'show']);
        Route::put('admin/clients/{id}', [AdminUserController::class, 'update']);
        Route::delete('admin/clients/{id}', [AdminUserController::class, 'destroy']);

        Route::get('admin/transactions', [AdminTransactionController::class, 'index']);
        Route::get('admin/transactions/{id}', [AdminTransactionController::class, 'show']);
        Route::post('admin/transactions/{id}/cancel', [AdminTransactionController::class, 'cancel']);

        Route::get('admin/stats', [\App\Http\Controllers\AdminStatsController::class, 'index']);
        Route::get('admin/clients/{id}/transactions', [AdminUserController::class, 'transactions']);

        Route::get('/admin/account-requests', [AdminUserController::class, 'accountRequests']);
        Route::post('/admin/account-requests/{id}/approve', [AdminUserController::class, 'approveRequest']);
        Route::post('/admin/account-requests/{id}/reject', [AdminUserController::class, 'rejectRequest']);
    });
});

Route::middleware(['auth:sanctum', 'role:ADMIN'])->prefix('v1')->group(function () {
    Route::prefix('admin/clients')->group(function () {
        Route::get('{id}/wallet', [ClientAdminController::class, 'getPortfolio']);
    });
});
