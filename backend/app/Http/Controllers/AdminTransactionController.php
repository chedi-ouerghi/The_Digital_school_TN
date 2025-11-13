<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTransactionController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Liste de toutes les transactions du système
     */
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::with(['cryptoWalletAsset.wallet.user', 'cryptomoney'])
            ->orderBy('created_at', 'desc');

        // Filtres optionnels
        if ($request->has('user_id')) {
            $query->whereHas('cryptoWalletAsset.wallet', fn($q) => $q->where('user_id', $request->user_id));
        }
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $transactions = $query->paginate(20);
        return response()->json($transactions);
    }

    /**
     * Détails d'une transaction
     */
    public function show($id): JsonResponse
    {
        $transaction = Transaction::with(['cryptoWalletAsset.wallet.user', 'cryptomoney'])
    ->findOrFail($id);
        return response()->json($transaction);
    }

    /**
     * Créer une transaction manuelle
     */
    // public function store(Request $request): JsonResponse
    // {
    //     $data = $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //         'symbol' => 'required|exists:cryptomoney,symbole',
    //         'type' => 'required|in:ACHAT,VENTE',
    //         'quantity' => 'required|numeric|min:0.00000001',
    //     ]);

    //     try {
    //         $user = User::findOrFail($data['user_id']);
    //         $message = $this->transactionService->handleTransaction(
    //             $user,
    //             $data['symbol'],
    //             $data['type'],
    //             $data['quantity']
    //         );

    //         return response()->json([
    //             'message' => $message,
    //             'success' => true
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'error' => $e->getMessage()
    //         ], 400);
    //     }
    // }

    /**
     * Annuler une transaction
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        try {
$transaction = Transaction::with(['cryptoWalletAsset.wallet.user', 'cryptomoney'])
    ->findOrFail($id);

            if ($transaction->cancelled_at) {
                return response()->json(['error' => 'Cette transaction est déjà annulée.'], 400);
            }

            // Annuler la transaction via le service dédié
            $result = $this->transactionService->cancelTransaction(
                $transaction,
                $request->reason ?? 'Annulation administrative'
            );

            // Notifier le client concerné
            try {
                $client = $transaction->wallet->user;
                $clientName = $client->name ?? $client->email ?? "Utilisateur #{$client->id}";
                
                // Utiliser cryptomoney directement de la transaction
                $cryptoSymbole = $transaction->cryptomoney->symbol ?? 'UNKNOWN';
                
                $title = 'Transaction annulée par un administrateur';
                $message = "Bonjour {$clientName},\n"
                         . "Votre transaction #{$transaction->id} "
                         . "({$transaction->quantity} x {$cryptoSymbole} "
                         . "à {$transaction->price}€/u, "
                         . "total {$transaction->total_eur}€) "
                         . "a été annulée par un administrateur.\n"
                         . "Raison: " . ($request->reason ?? 'Annulation administrative');

                Notification::create([
                    'user_id' => $client->id,
                    'title' => $title,
                    'message' => $message,
                    'type' => Notification::TYPE_ADMIN_ACTION,
                ]);
            } catch (\Exception $e) {
                \Log::warning('Impossible d\'envoyer la notification d\'annulation: ' . $e->getMessage());
            }

            return response()->json([
                'message' => 'Transaction annulée avec succès.',
                'result' => $result
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Let Laravel handle the 404 response for non-existent models
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Erreur annulation transaction: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

}
