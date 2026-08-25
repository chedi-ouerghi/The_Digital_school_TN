<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use App\Models\Notification;
use App\Services\NotificationService;
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
        $query = Transaction::with(['cryptoWalletAsset.cryptomoney', 'cryptoWalletAsset.wallet.user'])
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
        $transaction = Transaction::with(['cryptoWalletAsset.cryptomoney', 'cryptoWalletAsset.wallet.user'])
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
public function cancel(Request $request, $id, NotificationService $notificationService): JsonResponse
{
    try {
        $transaction = Transaction::with([
            'cryptoWalletAsset.cryptomoney',
            'cryptoWalletAsset.wallet.user'
        ])->findOrFail($id);

        if ($transaction->cancelled_at) {
            return response()->json(['error' => 'This transaction has already been cancelled.'], 400);
        }

        // Vérifions l'accès au client plus simplement
        $client = null;
        
        // Méthode directe
        $client = User::whereHas('wallet.cryptoWalletAssets.transactions', function($q) use ($id) {
            $q->where('id', $id);
        })->first();

        // Annuler la transaction via le service
        $result = $this->transactionService->cancelTransaction(
            $transaction,
            $request->reason ?? 'Administrative cancellation'
        );

        // Notifier le client
        if ($client) {
            $cryptoSymbole = $transaction->cryptoWalletAsset?->cryptomoney?->symbol ?? 'UNKNOWN';
            
            $clientName = $client->name ?? $client->email ?? "Utilisateur #{$client->id}";
            
            $title = 'Transaction cancelled by an administrator';
            $message = "Good morning {$clientName},\n"
                     . "Your transaction #{$transaction->id} "
                     . "({$transaction->quantity} x {$cryptoSymbole} "
                     . "at {$transaction->price}€/u, "
                     . "total {$transaction->total_eur}€) "
                     . "has been cancelled by an administrator.\n"
                     . "Raison: " . ($request->reason ?? 'Administrative cancellation');

            $notificationService->create([
                'user_id' => $client->id,
                'title' => $title,
                'message' => $message,
                'type' => Notification::TYPE_ADMIN_ACTION,
            ]);
        }

        return response()->json([
            'message' => 'Transaction cancelled successfully.',
            'result' => $result
        ]);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        throw $e;
    } catch (\Exception $e) {
        \Log::error('Error cancelling transaction: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 400);
    }
}
}
