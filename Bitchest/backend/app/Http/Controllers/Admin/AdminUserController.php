<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCreateClientRequest;
use App\Http\Requests\AdminUpdateClientRequest;
use App\Models\User;
use App\Models\Transaction;
use App\Models\AccountRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\TempPasswordMail;
use App\Services\TransactionService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Wallet; // added

class AdminUserController extends Controller
{
    /**
     * Lister les utilisateurs (clients) — paginé
     */
public function index(): JsonResponse
{
    // Charger les clients avec leur wallet associé
    $users = User::where('role', 'CLIENT')
        ->with('wallet:id,user_id,balance_eur') // optimisation : ne charger que les champs utiles
        ->paginate(20);

    // Préparation du solde en euros, y compris lorsque le wallet est absent
    $users->getCollection()->transform(function ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'profile_picture' => $user->profile_picture,
            'profile_banner' => $user->profile_banner,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'balance_eur' => $user->wallet?->balance_eur ?? 0, 

        ];
    });

    return response()->json($users);
}


/**
 * Détails d'un client
 */
public function show($id): JsonResponse
{
    $user = User::with([
        'wallet.cryptoWalletAssets.cryptomoney',
        'wallet.transactions.cryptoWalletAsset.cryptomoney',
    ])->findOrFail($id);

    $wallet = $user->wallet;

    if (!$wallet) {
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'profile_picture' => $user->profile_picture,
            'profile_banner' => $user->profile_banner,
            'balance_eur' => 0,
            'account_balance' => 0,
            'positions' => [],
            'transactions' => [],
        ]);
    }

    // 🧮 Calcul du total des positions actuelles
    $positions = $wallet->cryptoWalletAssets->map(function ($asset) {
        $quantity = (float) $asset->quantity;
        $avgPrice = (float) $asset->average_buy_price;
        $currentPrice = (float) ($asset->cryptomoney?->price_eur ?? 0);

        $currentValue = $quantity * $currentPrice;
        $invested = $quantity * $avgPrice;
        $pnl = $currentValue - $invested;

        return [
            'id' => $asset->cryptomoney?->id,
            'name' => $asset->cryptomoney?->name,
            'symbol' => $asset->cryptomoney?->symbol,
            'image' => $asset->cryptomoney?->image,
            'quantity' => $quantity,
            'avg_buy_price' => $avgPrice,
            'current_price' => $currentPrice,
            'current_value' => $currentValue,
            'invested' => $invested,
            'plus_value' => $pnl,
            'first_buy_date' => $asset->created_at,
        ];
    });

    $accountBalance =
        $positions->sum('current_value') + (float) $wallet->balance_eur;

    // 🧾 Transactions récentes
    $transactions = $wallet->transactions
        ->sortByDesc('created_at')
        ->take(10)
        ->map(function ($tx) {
            return [
                'id' => $tx->id,
                'type' => $tx->type,
                'crypto' => $tx->cryptoWalletAsset?->cryptomoney?->symbol,
                'quantity' => (float) $tx->quantity,
                'price' => (float) $tx->price,
                'total_eur' => (float) $tx->total_eur,
                'status' => $tx->cancelled_at ? 'Annulée' : 'Validée',
                'created_at' => $tx->created_at,
            ];
        })
        ->values();

    return response()->json([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role,
        'profile_picture' => $user->profile_picture,
        'profile_banner' => $user->profile_banner,
        'balance_eur' => (float) $wallet->balance_eur,
        'account_balance' => $accountBalance,
        'positions' => $positions,
        'transactions' => $transactions,
    ]);
}



    /**
     * Créer un compte client (génère mot de passe temporaire et solde 500 par défaut)
     */
    public function store(AdminCreateClientRequest $request, TransactionService $transactionService, NotificationService $notificationService): JsonResponse
    {
        $data = $request->validated();
        $data['role'] = $data['role'] ?? 'CLIENT';
        // Génération du mot de passe temporaire du nouveau compte
        $tempPassword = Str::random(10);
        $data['password'] = $tempPassword; // le mutator du modèle User va le hasher
        // Solde initial si client et non fourni
        if ($data['role'] === 'CLIENT') {
            $data['balance_eur'] = $data['balance_eur'] ?? 500.00;
        }
        try {
            $user = User::create($data);
            // Crédit initial via service (centralisé)
            if ($data['role'] === 'CLIENT') {
                $initial = $data['balance_eur'] ?? 500.00;
                $transactionService->creditInitialBalance($user, (string)$initial);
                $notificationService->createWelcome($user);
            }
            // Envoi du mot de passe temporaire par courrier électronique
            try {
                Mail::to($user->email)->send(new TempPasswordMail($user, $tempPassword));
            } catch (\Exception $mailEx) {
                \Log::error('Échec envoi mail mot de passe temporaire à ' . $user->email . ' : ' . $mailEx->getMessage());
                // On continue, mais on informe l'admin dans la réponse
                return response()->json(['user' => $user, 'temp_password' => $tempPassword, 'warning' => 'Créé mais échec envoi email'], 201);
            }
            return response()->json(['user' => $user, 'temp_password' => $tempPassword], 201);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création client par admin : ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mettre à jour un client (admin)
     */
    public function update(AdminUpdateClientRequest $request, $id, NotificationService $notificationService): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }
        $data = $request->validated();
        $oldRole = strtoupper((string) $user->role);
        // Si password est présent et confirmé, appliquer
        if (!empty($data['password'])) {
            $user->password = $data['password'];
        }
        if (isset($data['name'])) $user->name = $data['name'];
        if (isset($data['email'])) $user->email = $data['email'];
        if (isset($data['role'])) $user->role = $data['role'];
        if (isset($data['balance_eur'])) $user->solde = $data['balance_eur'];
        $user->save();
        $newRole = strtoupper((string) $user->role);
        if (isset($data['role']) && $oldRole !== $newRole) {
            $notificationService->createRoleSync($user, $oldRole, $newRole);
        }
        return response()->json($user);
    }

    /**
     * Supprimer un client
     */
    public function destroy($id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }

	public function transactions($id): JsonResponse
	{
		try {
			$user = User::findOrFail($id);
			
			$transactions = Transaction::with(['cryptoWalletAsset.cryptomoney', 'cryptoWalletAsset.wallet.user'])
				->whereHas('cryptoWalletAsset.wallet', function($query) use ($id) {
					$query->where('user_id', $id);
				})
				->orderByDesc('created_at')
				->get();

			return response()->json([
				'user' => [
					'id' => $user->id,
					'name' => $user->name,
					'email' => $user->email
				],
				'transactions' => $transactions->map(function($transaction) {
					return [
						'id' => $transaction->id,
						'type' => $transaction->type,
						'quantity' => $transaction->quantity,
						'price' => $transaction->price,
						'total_eur' => $transaction->total_eur,
						'created_at' => $transaction->created_at,
						'crypto' => [
							'id' => $transaction->cryptoWalletAsset?->cryptomoney?->id,
							'name' => $transaction->cryptoWalletAsset?->cryptomoney?->name,
							'symbol' => $transaction->cryptoWalletAsset?->cryptomoney?->symbol,
							'price_eur' => $transaction->cryptoWalletAsset?->cryptomoney?->price_eur,
						]
					];
				})
			]);
		} catch (\Exception $e) {
			\Log::error('Error retrieving transactions: ' . $e->getMessage());
			return response()->json([
				'error' => 'Error retrieving transactions',
				'details' => $e->getMessage()
			], 500);
		}
	}

	public function portfolio($id): JsonResponse
	{
		$user = \App\Models\User::find($id);
		if (!$user) return response()->json(['error' => 'User not found'], 404);

		$portfolio = \App\Models\Wallet::with([
			'cryptoWalletAssets.cryptomoney',
			'transactions.cryptoWalletAsset.cryptomoney'
		])
			->where('user_id', $id)
			->get();

		return response()->json(['portfolio' => $portfolio]);
	}

    /**
     * Liste des demandes de compte en attente
     */
    public function accountRequests(): JsonResponse 
    {
        $requests = \App\Models\AccountRequest::where('status', 'VERIFIED')
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($requests);
    }

    /**
     * Un visiteur soumet une demande d'ouverture de compte.
     */
    public function requestAccount(Request $request, NotificationService $notificationService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|unique:account_requests,email',
        ]);

        $accountRequest = AccountRequest::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'status' => 'PENDING',
        ]);

        $admins = User::where('role', 'ADMIN')->get();

        foreach ($admins as $admin) {
            try {
                Mail::to($admin->email)->send(new \App\Mail\NewAccountRequestMail($accountRequest));

                $notificationService->create([
                    'user_id' => $admin->id,
                    'title' => 'New account request',
                    'message' => "Request submitted by {$accountRequest->name} ({$accountRequest->email})",
                    'type' => Notification::TYPE_ACCOUNT_REQUEST ?? 'account_request',
                ]);
            } catch (\Throwable $ex) {
                \Log::warning("Notification/email failed for admin {$admin->id}: " . $ex->getMessage());
            }
        }

        return response()->json([
            'message' => 'Your request has been submitted successfully.',
        ]);
    }

    /**
     * Approuver une demande de compte
     * Accepte un mot de passe temporaire fourni par l'admin
     */
    public function approveRequest(Request $request, $id, TransactionService $transactionService, NotificationService $notificationService): JsonResponse
    {
        try {
            // Valider les données reçues
            $validated = $request->validate([
                'temporary_password' => 'required|string|min:8|max:50'
            ]);

            DB::beginTransaction();
            
            $accountRequest = \App\Models\AccountRequest::findOrFail($id);
            
            if ($accountRequest->status !== 'VERIFIED') {
                return response()->json(['error' => 'This request has already been processed'], 400);
            }

            $tempPassword = $validated['temporary_password'];

            // Créer l'utilisateur
            $user = User::create([
                'name' => $accountRequest->name,
                'email' => $accountRequest->email,
                'password' => $tempPassword,
                'role' => 'CLIENT',
                'email_verified_at' => now() // Marquer l'email comme vérifié immédiatement
            ]);

            // Créer le wallet initial avec 500 EUR
            $wallet = Wallet::create([
                'user_id' => $user->id,
                'balance_eur' => 500.00,
            ]);

            $accountRequest->update([
                'status' => 'APPROVED',
                'processed_at' => now(),
                'processed_by' => Auth::id()
            ]);

            $notificationService->createWelcome($user);

            // Envoyer l'email
            try {
                Mail::to($user->email)->send(new TempPasswordMail($user, $tempPassword));
                $mailSent = true;
            } catch (\Exception $mailEx) {
                \Log::error('Failed sending temporary password email to ' . $user->email . ' : ' . $mailEx->getMessage());
                $mailSent = false;
            }

            DB::commit();

            // Rafraîchir l'utilisateur pour obtenir la relation wallets
            $user = $user->fresh(['wallets']);

            $response = [
                'message' => 'Account approved and created successfully',
                'user' => $user,
                'wallet' => $user->wallets->first(),
                'mail_sent' => $mailSent
            ];

            if (!$mailSent) {
                $response['temp_password'] = $tempPassword;
                $response['warning'] = 'Email not sent - keep this temporary password safe';
            }

            return response()->json($response, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Validation error',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error during account request approval: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error during account creation',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    
    /**
     * L'administrateur rejette une demande avec une raison facultative.
     */
    public function rejectRequest(Request $request, string $id)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $accountRequest = AccountRequest::findOrFail($id);
            
            if ($accountRequest->status !== 'VERIFIED') {
                return response()->json(['error' => 'This request has already been processed'], 400);
            }

            $accountRequest->update([
                'status' => 'REJECTED',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
                'rejection_reason' => $validated['reason'] ?? 'No reason provided.',
            ]);

            DB::commit();

            // Notification à l’email du demandeur (si disponible)
            try {
                Mail::raw(
                    "Bonjour {$accountRequest->name},\n\Your account request has been denied. \nReason : " .
                    ($validated['reason'] ?? 'No reason provided.') .
                    "\n\nSincerely,\nTeam BitChest",
                    function ($message) use ($accountRequest) {
                        $message->to($accountRequest->email)
                                ->subject('Account request denied');
                    }
                );
            } catch (\Throwable $mailEx) {
                \Log::error("Email rejection failed to send to {$accountRequest->email}: " . $mailEx->getMessage());
            }

            return response()->json([
                'message' => 'Account request rejected successfully.',
                'account_request' => $accountRequest,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Error rejecting account request: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error during account request rejection.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}