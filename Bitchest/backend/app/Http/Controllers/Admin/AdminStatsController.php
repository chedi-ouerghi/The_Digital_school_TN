<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Cryptomoney;
use App\Models\CryptoWalletAsset;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminStatsController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            // Total transactions
            $totalTransactions = Transaction::whereNull('cancelled_at')
                ->whereNull('deleted_at')
                ->count();

            // Total volume (EUR)
            $totalVolume = Transaction::whereNull('cancelled_at')
                ->whereNull('deleted_at')
                ->sum('total_eur');

            // Total users (CLIENT role only, no deleted_at check)
            $totalUsers = User::where('role', 'CLIENT')->count();

            // TOP 5 CRYPTOS BY VOLUME
            $topCryptos = Transaction::query()
                ->join('crypto_wallet_assets', 'transactions.crypto_wallet_asset_id', '=', 'crypto_wallet_assets.id')
                ->join('cryptomoney', 'crypto_wallet_assets.cryptomoney_id', '=', 'cryptomoney.id')
                ->select('cryptomoney.id', 'cryptomoney.name', 'cryptomoney.symbol', 'cryptomoney.image')
                ->selectRaw('SUM(transactions.quantity) as total_qty')
                ->selectRaw('SUM(transactions.total_eur) as total_volume')
                ->whereNull('transactions.cancelled_at')
                ->whereNull('transactions.deleted_at')
                ->groupBy('cryptomoney.id', 'cryptomoney.name', 'cryptomoney.symbol', 'cryptomoney.image')
                ->orderByDesc('total_volume')
                ->limit(5)
                ->get();

            // Recent transactions
            $recentTransactions = Transaction::with(['cryptoWalletAsset.cryptomoney', 'cryptoWalletAsset.wallet.user'])
                ->whereNull('cancelled_at')
                ->whereNull('deleted_at')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get()
                ->map(fn($t) => [
                    'id' => $t->id,
                    'type' => $t->type,
                    'quantity' => $t->quantity,
                    'price' => $t->price,
                    'total_eur' => $t->total_eur,
                    'crypto_name' => $t->cryptoWalletAsset?->cryptomoney?->name,
                    'crypto_image' => $t->cryptoWalletAsset?->cryptomoney?->image,
                    'user_name' => $t->cryptoWalletAsset?->wallet?->user?->name,
                    'created_at' => $t->created_at,
                ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'total_transactions' => $totalTransactions,
                    'total_volume' => $totalVolume,
                    'total_users' => $totalUsers,
                    'top_cryptos' => $topCryptos,
                    'recent_transactions' => $recentTransactions,
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error retrieving stats admin: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error retrieving statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
