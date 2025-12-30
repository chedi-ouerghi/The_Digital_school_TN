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
     *     summary="Lister les wallets de l'utilisateur connecté",
     *     tags={"wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Liste des wallets"),
     *     @OA\Response(response=500, description="Erreur interne")
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Vérifier si l'utilisateur est un client
            if ($user->role !== 'CLIENT') {
                return response()->json([
                    'error' => 'Seuls les clients peuvent accéder à leur wallet'
                ], 403);
            }

            $wallet = Wallet::with([
                'cryptoWalletAssets.cryptomoney',
                'transactions' => function($query) {
                    $query->with('cryptoWalletAsset.cryptomoney');
                }
            ])
                ->where('user_id', $user->id)
                ->first();
            
            if (!$wallet) {
                return response()->json([
                    'error' => 'wallet non trouvé'
                ], 404);
            }
            
            return response()->json([
                'wallet' => $wallet,
                'solde_eur' => (float) $wallet->balance_eur,
                'stats' => $this->walletService->calculatePortfolioStats($wallet->id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération du wallet',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/wallets/transaction",
     *     summary="Effectuer une transaction (achat/vente)",
     *     tags={"wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"symbol","type","quantity"},
     *             @OA\Property(property="symbol", type="string", description="Symbole de la crypto (ex: BTC)"),
     *             @OA\Property(property="type", type="string", enum={"ACHAT","VENTE"}),
     *             @OA\Property(property="quantity", type="number", format="float", description="Quantité à acheter/vendre")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Transaction effectuée avec succès"),
     *     @OA\Response(response=400, description="Erreur de validation ou transaction"),
     *     @OA\Response(response=422, description="Erreur de validation"),
     *     @OA\Response(response=500, description="Erreur interne")
     * )
     */
    public function transact(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'symbol' => 'required|string|exists:cryptomoney,symbol',
                'type' => 'required|in:ACHAT,VENTE',
                'quantity' => 'required|numeric|min:0.00000001'
            ]);

            $user = Auth::user();
            $crypto = Cryptomoney::where('symbol', $validated['symbol'])->firstOrFail();
            
            $result = $this->transactionService->handleTransaction(
                $user,
                $validated['symbol'],
                $validated['type'],
                (float) $validated['quantity']
            );

            DB::commit();

            $newWallet = $user->fresh()->wallets()->first();

            return response()->json([
                'message' => $result,
                'new_balance' => (float) ($newWallet?->balance_eur ?? 0),
                'transaction_details' => [
                    'type' => $validated['type'],
                    'quantity' => (float) $validated['quantity'],
                    'crypto' => $crypto->symbol,
                    'price' => (float) $crypto->price_eur
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Erreur lors de la transaction',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/wallets/plus-value",
     *     summary="Calculer la plus-value totale du wallet",
     *     tags={"wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Plus-value calculée avec succès"),
     *     @OA\Response(response=500, description="Erreur interne")
     * )
     */
    public function plusValue(): JsonResponse
    {
        try {
            $user = Auth::user();
            $wallet = $user->wallet;

            if (!$wallet) {
                return response()->json([
                    'error' => 'wallet non trouvé'
                ], 404);
            }

            $stats = $this->walletService->calculatePlusValue($wallet->id);

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du calcul de la plus-value',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/wallets/history",
     *     summary="Obtenir l'historique des valeurs du wallet",
     *     tags={"wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Historique récupéré avec succès"),
     *     @OA\Response(response=404, description="wallet non trouvé"),
     *     @OA\Response(response=500, description="Erreur interne")
     * )
     */
    public function history(): JsonResponse
    {
        try {
            $user = Auth::user();
            $wallet = $user->wallet;

            if (!$wallet) {
                return response()->json([
                    'error' => 'wallet non trouvé'
                ], 404);
            }

            $history = $this->walletService->getPortfolioHistory($wallet->id);
            return response()->json($history);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération de l\'historique',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/wallets/{id}",
     *     summary="Obtenir les détails d'un wallet spécifique",
     *     tags={"wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du wallet",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Détails du wallet"),
     *     @OA\Response(response=404, description="wallet non trouvé"),
     *     @OA\Response(response=403, description="Accès non autorisé"),
     *     @OA\Response(response=500, description="Erreur interne")
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $wallet = Wallet::with([
                'cryptoWalletAssets.cryptomoney',
                'transactions' => function($query) {
                    $query->with('cryptoWalletAsset.cryptomoney');
                }
            ])
                ->where('id', $id)
                ->first();

            if (!$wallet) {
                return response()->json(['error' => 'wallet non trouvé'], 404);
            }

            // Vérification que l'utilisateur est propriétaire ou admin
            if ($wallet->user_id !== $user->id && !$user->isAdmin()) {
                return response()->json(['error' => 'Accès non autorisé'], 403);
            }

            // Récupérer les détails via le service
            $details = $this->walletService->getPortfolioDetails($wallet->id);

            return response()->json([
                'wallet' => $details,
                'solde_eur' => (float) $wallet->balance_eur,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des détails du wallet',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
