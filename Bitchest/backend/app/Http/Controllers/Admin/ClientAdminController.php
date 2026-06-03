<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClientAdminController extends Controller
{
    /**
     * Récupère le wallet d'un client par son ID
     */
    public function getPortfolio($id): JsonResponse
    {
        try {
            // Vérifier que l'utilisateur existe
            $user = User::findOrFail($id);

            // Charger le wallet et ses relations (cryptos, transactions, etc.)
            $portfolio = Wallet::with([
                'user:id,name,email',
                'transactions.cryptoWalletAsset.cryptomoney',
                'cryptoWalletAssets.cryptomoney'
            ])
            ->where('user_id', $user->id)
            ->first();

            // Si aucun wallet trouvé
            if (!$portfolio) {
                return response()->json([
                    'error' => "No wallet was found for this user.",
                    'user' => $user->only(['id', 'name', 'email'])
                ], 404);
            }

            // Retourner les informations du wallet
            return response()->json([
                'user' => $user->only(['id', 'name', 'email']),
                'portfolio' => [
                    'id' => $portfolio->id,
                    'balance_eur' => $portfolio->balance_eur,
                    'valeur_totale' => $portfolio->getTotalValue(),
                    'plus_value_totale' => $portfolio->getTotalPlusValue(),
                    'cryptos' => $portfolio->cryptoWalletAssets->map(function ($position) {
                        return [
                            'symbole' => $position->cryptomoney->symbol ?? 'N/A',
                            'nom' => $position->cryptomoney->name ?? '',
                            'quantite' => $position->quantity,
                            'valeur_actuelle' => $position->getCurrentValue(),
                        ];
                    }),
                    'transactions' => $portfolio->transactions->map(function ($transaction) {
                        return [
                            'id' => $transaction->id,
                            'type' => $transaction->type,
                            'quantity' => $transaction->quantity,
                            'price' => $transaction->price,
                            'total_eur' => $transaction->total_eur,
                            'crypto' => $transaction->cryptoWalletAsset?->cryptomoney?->symbol ?? '',
                            'date' => $transaction->created_at->format('Y-m-d H:i:s'),
                        ];
                    }),
                ],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found.'], 404);

        } catch (\Exception $e) {
            Log::error("Error retrieving wallet: " . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while retrieving the wallet.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
