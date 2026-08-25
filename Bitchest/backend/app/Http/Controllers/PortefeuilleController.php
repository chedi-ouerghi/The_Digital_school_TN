<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\Cryptomoney;
use App\Services\TransactionService;
use App\Services\PortefeuilleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PortefeuilleController extends Controller
{
    protected $transactionService;
    protected $walletService;

    public function __construct(TransactionService $transactionService, PortefeuilleService $walletService)
    {
        $this->transactionService = $transactionService;
        $this->walletService = $walletService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/wallets",
     *     summary="List wallets of the authenticated user",
     *     tags={"wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="List of wallets"),
     *     @OA\Response(response=500, description="Internal error")
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user is a client
            if ($user->role !== 'CLIENT') {
                return response()->json([
                    'error' => 'Only clients can access their wallet'
                ], 403);
            }

            $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

            $portfolioDetails = $this->walletService->getPortfolioDetails($wallet->id);

            return response()->json(array_merge($portfolioDetails, ['balance_eur' => (string) $wallet->balance_eur]));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error retrieving wallet',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/wallets/transaction",
     *     summary="Perform a transaction (buy/sell)",
     *     tags={"wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"symbol","type","quantity"},
     *             @OA\Property(property="symbol", type="string", description="Crypto symbol (e.g., BTC)"),
     *             @OA\Property(property="type", type="string", enum={"ACHAT","VENTE"}),
     *             @OA\Property(property="quantity", type="number", format="float", description="Quantity to buy/sell")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Transaction completed successfully"),
     *     @OA\Response(response=400, description="Validation or transaction error"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="Internal error")
     * )
     */
    public function transact(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // SECURITE - Etape 2 : une session authentifiee ne suffit pas pour
            // trader. Le mot de passe temporaire doit d'abord avoir ete change.
            if (is_null($user->password_changed_at)) {
                return response()->json([
                    'error' => 'You must change your password before making transactions.',
                    'message' => 'Pour des raisons de sécurité, vous devez d\'abord changer votre mot de passe temporaire.'
                ], 403);
            }

            DB::beginTransaction();

            // VERIFIER - Etape 3 : Laravel refuse les donnees incompletes,
            // un type inconnu et une quantite nulle ou negative.
            $validated = $request->validate([
                'symbol' => 'required|string|exists:cryptomoney,symbol',
                'type' => 'required|in:ACHAT,VENTE',
                'quantity' => 'required|numeric|min:0.00000001'
            ]);

            $crypto = Cryptomoney::where('symbol', $validated['symbol'])->firstOrFail();
            
            // SECURISER - Etape 4 : le service effectue les controles metier
            // sous transaction SQL et verrouille les lignes avant modification.
            $result = $this->transactionService->handleTransaction(
                $user,
                $validated['symbol'],
                $validated['type'],
                (string) $validated['quantity']
            );

            DB::commit();

            $newWallet = $user->fresh()->wallets()->first();

            // Clear transaction history cache after successful transaction
            Cache::forget('transactions_history:user_' . $user->id . ':type_all');
            Cache::forget('transactions_history:user_' . $user->id . ':type_ACHAT');
            Cache::forget('transactions_history:user_' . $user->id . ':type_VENTE');

            return response()->json([
                'message' => $result,
                'success' => true,
                'new_balance' => (string) ($newWallet?->balance_eur ?? '0'),
                'transaction_details' => [
                    'type' => $validated['type'],
                    'quantity' => (string) $validated['quantity'],
                    'crypto' => $crypto->symbol,
                    'price' => (string) $crypto->price_eur,
                    'total' => bcmul((string)$validated['quantity'], (string)$crypto->price_eur, 18)
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Transaction error',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/wallets/plus-value",
     *     summary="Calculate total capital gains of the wallet",
     *     tags={"wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Capital gains calculated successfully"),
     *     @OA\Response(response=500, description="Internal error")
     * )
     */
    public function plusValue(): JsonResponse
    {
        try {
            $user = Auth::user();
            $wallet = $user->wallet;

            if (!$wallet) {
                return response()->json([
                    'error' => 'Wallet not found'
                ], 404);
            }

            $summary = $this->walletService->getPortfolioPlusValue($wallet->id);

            return response()->json($summary);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error calculating capital gains',
                'details' => $e->getMessage()
            ], 500);
        }
    }



    /**
     * @OA\Get(
     *     path="/api/v1/wallets/history",
     *     summary="Get wallet value history",
     *     tags={"wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="days",
     *         in="query",
     *         description="Number of days to retrieve history for (7, 30, 90, or 365)",
     *         required=false,
     *         @OA\Schema(type="integer", default=30)
     *     ),
     *     @OA\Response(response=200, description="History retrieved successfully"),
     *     @OA\Response(response=404, description="Wallet not found"),
     *     @OA\Response(response=500, description="Internal error")
     * )
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $wallet = $user->wallet;

            if (!$wallet) {
                return response()->json([
                    'error' => 'Wallet not found'
                ], 404);
            }

            $days = (int) $request->query('days', 30);
            
            // Validate days parameter
            $allowedDays = [7, 30, 90, 365];
            if (!in_array($days, $allowedDays)) {
                $days = 30;
            }

            $history = $this->walletService->getPortfolioHistory($wallet->id, $days);
            return response()->json($history);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error retrieving history',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/wallets/transactions/history",
     *     summary="Get transaction history of the wallet with optional type filter",
     *     tags={"wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter transactions by type (ACHAT or VENTE)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"ACHAT", "VENTE"})
     *     ),
     *     @OA\Response(response=200, description="Transaction history retrieved successfully"),
     *     @OA\Response(response=403, description="Only clients can access their wallet"),
     *     @OA\Response(response=404, description="Wallet not found"),
     *     @OA\Response(response=500, description="Internal error")
     * )
     */
    public function transactionsHistory(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user is a client
            if ($user->role !== 'CLIENT') {
                return response()->json([
                    'error' => 'Only clients can access their wallet'
                ], 403);
            }

            $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

            $type = $request->query('type');
            
            // Cache Redis pour l'historique des transactions - 2 minutes TTL
            $cacheKey = 'transactions_history:user_' . $user->id . ':type_' . ($type ?? 'all');
            $ttl = 60 * 2; // 2 minutes
            
            $transactions = Cache::remember($cacheKey, $ttl, function () use ($wallet, $type) {
                return $this->walletService->getTransactionsHistory($wallet->id, $type);
            });

            return response()->json([
                'transactions' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error retrieving transaction history',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/wallets/{id}",
     *     summary="Get details of a specific wallet",
     *     tags={"wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Wallet ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Wallet details"),
     *     @OA\Response(response=404, description="Wallet not found"),
     *     @OA\Response(response=403, description="Unauthorized access"),
     *     @OA\Response(response=500, description="Internal error")
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $wallet = Wallet::with([
                'cryptoWalletAssets.cryptomoney',
                'cryptoWalletAssets.transactions' => function($query) {
                    $query->with('cryptoWalletAsset.cryptomoney');
                }
            ])
                ->where('id', $id)
                ->first();

            if (!$wallet) {
                return response()->json(['error' => 'Wallet not found'], 404);
            }

            // Check that user is owner or admin
            if ($wallet->user_id !== $user->id && !$user->isAdmin()) {
                return response()->json(['error' => 'Unauthorized access'], 403);
            }

            // Get details via service
            $details = $this->walletService->getPortfolioDetails($wallet->id);

            return response()->json($details);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error retrieving wallet details',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
