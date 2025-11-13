<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="API Auth v1",
 *     description="Documentation de l'API d'authentification Sanctum v1"
 * )
 *
 * @OA\Server(
 *     url="/api/v1",
 *     description="API v1 server"
 * )
 */
class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');

            // Récupérer l'utilisateur par email
            $user = User::where('email', $credentials['email'])->first();
            if (!$user) {
                \Log::warning('Tentative de connexion échouée pour (email inconnu) : ' . $credentials['email']);
                return response()->json(['message' => 'Identifiants invalides'], 401);
            }

            $providedPassword = (string) $credentials['password'];
            $storedPassword = (string) $user->password;

            // Détecter si le mot de passe stocké est bcrypt ou non
            $isBcrypt = str_starts_with($storedPassword, '$2y$') || str_starts_with($storedPassword, '$2a$') || str_starts_with($storedPassword, '$2b$');

            $valid = false;
            if ($isBcrypt) {
                $valid = Hash::check($providedPassword, $storedPassword);
            } else {
                // Ancien mot de passe en clair: comparer directement, puis rehasher et sauvegarder
                $valid = hash_equals($storedPassword, $providedPassword);
                if ($valid) {
                    $user->password = $providedPassword; // $casts['password' => 'hashed'] rehash auto
                    $user->save();
                }
            }

            if (!$valid) {
                \Log::warning('Tentative de connexion échouée (mot de passe invalide) pour : ' . $credentials['email']);
                return response()->json(['message' => 'Identifiants invalides'], 401);
            }

            // Authentifier et générer un token Sanctum
            Auth::login($user);

            // Assurer remember_token et email_verified_at
            $needsSave = false;
            if (empty($user->remember_token)) {
                $user->remember_token = Str::random(60);
                $needsSave = true;
            }
            // Si le client n'a pas email_verified_at (sécurité: ne pas activer pour ADMIN)
            if ((strtoupper($user->role ?? 'CLIENT') === 'CLIENT') && empty($user->email_verified_at)) {
                $user->email_verified_at = now();
                $needsSave = true;
            }
            if ($needsSave) {
                $user->save();
            }
            $token = $user->createToken('api_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la connexion : ' . $e->getMessage());
            return response()->json([
                'message' => 'Échec de la connexion',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/logout",
     *     summary="Logout user",
     *     tags={"Auth"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Logout successful")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Déconnexion réussie']);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la déconnexion : ' . $e->getMessage());
            return response()->json([
                'message' => 'Échec de la déconnexion',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/profile",
     *     summary="Afficher le profil de l'utilisateur connecté",
     *     tags={"Auth"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Profil utilisateur retourné")
     * )
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'user' => $request->user(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération du profil : ' . $e->getMessage());
            return response()->json([
                'message' => 'Échec de la récupération du profil',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mettre à jour le profil de l'utilisateur connecté (name, email)
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $data = $request->validate([
                'name' => ['nullable', 'string', 'max:255'],
                'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            ]);
            if (isset($data['name'])) $user->name = $data['name'];
            if (isset($data['email'])) $user->email = $data['email'];
            $user->save();
            return response()->json(['user' => $user]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Erreur de validation lors de la mise à jour du profil : ' . json_encode($e->errors()));
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la mise à jour du profil : ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Permet au client connecté de changer son mot de passe.
     * Exige current_password, password, password_confirmation
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validate([
                'current_password' => ['required', 'string'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            // Ici on limite le changement via cette route aux CLIENT (conformément à la spec)
            if (isset($user->role) && strtoupper($user->role) !== 'CLIENT') {
                return response()->json(['error' => 'Seuls les clients peuvent changer leur mot de passe via ce endpoint.'], 403);
            }

            if (!\Illuminate\Support\Facades\Hash::check($validated['current_password'], $user->password)) {
                return response()->json(['error' => 'Mot de passe actuel invalide.'], 400);
            }
            $user->password = $validated['password'];
            $user->save();
            return response()->json(['message' => 'Mot de passe mis à jour.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Erreur de validation lors du changement de mot de passe : ' . json_encode($e->errors()));
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Erreur lors du changement de mot de passe : ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/request-account",
     *     summary="Demander la création d'un compte client",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Demande envoyée avec succès"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function requestAccount(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            ]);

            // Prevent duplicate pending requests for same email
            $existing = \App\Models\AccountRequest::where('email', $validated['email'])
                ->where('status', 'PENDING')
                ->first();
            if ($existing) {
                return response()->json([
                    'error' => 'Une demande est déjà en attente pour cet email.'
                ], 422);
            }

            // Create request (AccountRequest model boot will generate token if absent)
            $accountRequest = \App\Models\AccountRequest::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'status' => 'PENDING',
            ]);

            // Notify admins (email + internal notification). Failures on mail shouldn't block the API reply.
            $admins = User::where('role', 'ADMIN')->get();
            foreach ($admins as $admin) {
                try {
                    \Mail::to($admin->email)->send(new \App\Mail\NewAccountRequestMail($accountRequest));
                } catch (\Exception $mailEx) {
                    \Log::error('Échec envoi mail nouvelle demande compte à ' . $admin->email . ' : ' . $mailEx->getMessage());
                }
                try {
                    \App\Models\Notification::create([
                        'user_id' => $admin->id,
                        'title' => 'Nouvelle demande de compte',
                        'message' => "Demande de {$accountRequest->name} ({$accountRequest->email})",
                        'type' => \App\Models\Notification::TYPE_ACCOUNT_REQUEST ?? 'account_request',
                    ]);
                } catch (\Exception $nEx) {
                    \Log::warning('Impossible de créer notification interne pour admin ' . $admin->id . ' : ' . $nEx->getMessage());
                }
            }

            return response()->json(['message' => 'Votre demande a été envoyée avec succès.'], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Erreur de validation','details' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la demande de compte (AuthController) : ' . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue'], 500);
        }
    }
}
