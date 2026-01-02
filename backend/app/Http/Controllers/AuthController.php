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
use Illuminate\Support\Facades\DB;

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
                \Log::warning('Connection attempt failed for (unknown email): ' . $credentials['email']);
                return response()->json(['message' => 'Invalid credentials'], 401);
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
                \Log::warning('Connection attempt failed (invalid password) for : ' . $credentials['email']);
                return response()->json(['message' => 'Invalid credentials'], 401);
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
            \Log::error('Error during login: ' . $e->getMessage());
            return response()->json([
                'message' => 'Login failed',
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
            return response()->json(['message' => 'Logout successful']);
        } catch (\Exception $e) {
            \Log::error('Error during logout: ' . $e->getMessage());
            return response()->json([
                'message' => 'Logout failed',
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
            \Log::error('Error retrieving profile: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve profile',
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
            \Log::warning('Validation error during profile update: ' . json_encode($e->errors()));
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Error during profile update: ' . $e->getMessage());
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
                'password' => ['required', 'string', 'min:8', 'confirmed:password_confirmation'],
            ], [
                'current_password.required' => 'The current password field is required.',
                'password.required' => 'The password field is required.',
                'password.confirmed' => 'The password confirmation does not match.',
            ]);

          // Autoriser uniquement ADMIN et CLIENT
if (
    !isset($user->role) ||
    !in_array(strtoupper($user->role), ['ADMIN', 'CLIENT'])
) {
    return response()->json([
        'error' => 'You are not authorized to change the password via this endpoint.'
    ], 403);
}


            if (!\Illuminate\Support\Facades\Hash::check($validated['current_password'], $user->password)) {
                return response()->json(['error' => 'Current password is invalid.'], 400);
            }
            $user->password = $validated['password'];
            $user->save();
            return response()->json(['message' => 'Password updated successfully.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Validation error during password change: ' . json_encode($e->errors()));
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Error during password change: ' . $e->getMessage());
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
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=400, description="Email validation failed")
     * )
     */
    public function requestAccount(Request $request): JsonResponse
    {
        try {
            // Validation des données
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            ]);

            $email = strtolower(trim($validated['email']));
            $name = trim($validated['name']);

            // 1. Vérifier que le domaine de l'email existe et peut recevoir des emails (MX records)
            if (!$this->isEmailDomainValid($email)) {
                \Log::warning("Attempted account creation with invalid domain : {$email}");
                return response()->json([
                    'error' => 'The domain for this email address does not exist or cannot receive emails.'
                ], 400);
            }

            // 2. Vérifier que l'email n'est pas un domaine temporaire/jetable
            if ($this->isDisposableEmail($email)) {
                \Log::warning("Attempted account creation with disposable email : {$email}");
                return response()->json([
                    'error' => 'Temporary email addresses are not allowed. Please use a valid email address.'
                ], 400);
            }

            // 3. Vérifier s'il existe déjà une demande en attente pour cet email
            $existing = \App\Models\AccountRequest::where('email', $email)
                ->where('status', 'PENDING')
                ->first();
            if ($existing) {
                \Log::info("Account request already pending for email : {$email}");
                return response()->json([
                    'error' => 'A request is already pending for this email. Please check your inbox.'
                ], 422);
            }

            // 4. Créer la demande de compte
            $accountRequest = \App\Models\AccountRequest::create([
                'name' => $name,
                'email' => $email,
                'status' => 'PENDING',
            ]);

            \Log::info("New account request created : {$email} (ID: {$accountRequest->id})");

            // 5. Envoyer un email de confirmation à l'utilisateur
            $emailConfirmationSent = $this->sendAccountRequestConfirmation($accountRequest);
            if (!$emailConfirmationSent) {
                \Log::warning("Unable to send the confirmation email to {$email}");
                // Ne pas bloquer la réponse, mais logger l'erreur
            }

            // 6. Notifier les administrateurs
            $this->notifyAdminsOfNewRequest($accountRequest);

            return response()->json([
                'message' => 'Your request has been sent successfully. Please check your email to confirm your address.'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Validation error during account request: ' . json_encode($e->errors()));
            return response()->json([
                'error' => 'Validation error',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error during account request (AuthController) : ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred during your request. Please try again later.'
            ], 500);
        }
    }

    /**
     * Vérifier que le domaine de l'email a des MX records valides
     */
    private function isEmailDomainValid(string $email): bool
    {
        try {
            // Extraire le domaine de l'email
            $parts = explode('@', $email);
            if (count($parts) !== 2) {
                return false;
            }

            $domain = $parts[1];

            // Vérifier que le domaine existe et a des MX records
            // Fonction PHP native pour vérifier les MX records
            if (function_exists('checkdnsrr')) {
                return checkdnsrr($domain, 'MX');
            }

            // Fallback pour les serveurs sans support DNS
            return gethostbyname($domain) !== $domain;

        } catch (\Exception $e) {
            \Log::warning("Error during email domain verification for {$email} : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier que l'email n'est pas une adresse temporaire/jetable
     */
    private function isDisposableEmail(string $email): bool
    {
        // Liste commune de domaines temporaires
        $disposableDomains = [
            'guerrillamail.com',
            'mailinator.com',
            'tempmail.com',
            '10minutemail.com',
            'throwaway.email',
            'maildrop.cc',
            'sharklasers.com',
            'grr.la',
            'pokemail.net',
            'spam4.me',
            'yopmail.com',
            'temp-mail.org',
            'fakeinbox.com',
            'mailnesia.com',
            'temp mail.io',
        ];

        $domain = substr(strrchr($email, '@'), 1);
        return in_array(strtolower($domain), array_map('strtolower', $disposableDomains), true);
    }

    /**
     * Envoyer un email de confirmation à l'utilisateur
     */
    private function sendAccountRequestConfirmation(\App\Models\AccountRequest $accountRequest): bool
    {
        try {
            \Mail::to($accountRequest->email)->send(
                new \App\Mail\AccountRequestConfirmationMail($accountRequest)
            );
            \Log::info("Confirmation email sent to {$accountRequest->email}");
            return true;
        } catch (\Exception $e) {
            \Log::error("Error sending confirmation email to {$accountRequest->email} : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Notifier les administrateurs d'une nouvelle demande
     */
    private function notifyAdminsOfNewRequest(\App\Models\AccountRequest $accountRequest): void
    {
        try {
            $admins = User::where('role', 'ADMIN')->get();

            foreach ($admins as $admin) {
                // Envoyer un email à l'administrateur
                try {
                    \Mail::to($admin->email)->send(
                        new \App\Mail\NewAccountRequestMail($accountRequest)
                    );
                } catch (\Exception $mailEx) {
                    \Log::error("Error during notification email to {$admin->email} : " . $mailEx->getMessage());
                }

                // Créer une notification interne
                try {
                    \App\Models\Notification::create([
                        'user_id' => $admin->id,
                        'title' => 'New account request',
                        'message' => "Request from {$accountRequest->name} ({$accountRequest->email})",
                        'type' => \App\Models\Notification::TYPE_ACCOUNT_REQUEST ?? 'account_request',
                    ]);
                } catch (\Exception $nEx) {
                    \Log::warning("Unable to create notification for admin {$admin->id} : " . $nEx->getMessage());
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error during notification of administrators : ' . $e->getMessage());
        }
    }

    /**
     * Change administrator ID
     */
public function changeId(Request $request): JsonResponse
{
    try {
        $user = $request->user();
        if (strtoupper($user->role ?? '') !== 'ADMIN') {
            return response()->json(['error' => 'Only administrators can change their ID.'], 403);
        }

        $validated = $request->validate([
            'new_id' => ['required', 'string', 'regex:/^[A-Z0-9]{14}$/'],
            'confirmation' => ['required', 'string'],
        ]);

        if ($validated['confirmation'] !== 'I confirm that I want to change my administrator ID') {
            return response()->json(['error' => 'Invalid confirmation sentence.'], 400);
        }

        if ($user->last_id_change_at && now()->diffInDays($user->last_id_change_at) < 2) {
            return response()->json(['error' => 'ID can only be changed once every 2 days.'], 400);
        }

        $new_id = $validated['new_id'];

        // Check if new_id is unique
        if (\App\Models\User::where('id', $new_id)->exists()) {
            return response()->json(['error' => 'This ID is already in use.'], 400);
        }

        $old_id = $user->id;

        DB::transaction(function () use ($user, $new_id, $old_id) {
            // 1. DÉSACTIVER TEMPORAIREMENT LES CONTRAINTES
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            // 2. METTRE À JOUR L'UTILISATEUR EN PREMIER
            $user->id = $new_id;
            $user->last_id_change_at = now();
            $user->save();
            
            // 3. METTRE À JOUR LES TABLES LIÉES
            \App\Models\Wallet::where('user_id', $old_id)->update(['user_id' => $new_id]);
            \App\Models\Notification::where('user_id', $old_id)->update(['user_id' => $new_id]);
            \App\Models\AccountRequest::where('user_id', $old_id)->update(['user_id' => $new_id]);
            
            // Sanctum tokens
            DB::table('personal_access_tokens')
                ->where('tokenable_id', $old_id)
                ->where('tokenable_type', \App\Models\User::class)
                ->update(['tokenable_id' => $new_id]);
            
            // 4. RÉACTIVER LES CONTRAINTES
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        });

        return response()->json(['message' => 'ID changed successfully.']);
    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::warning('Validation error during ID change: ' . json_encode($e->errors()));
        return response()->json(['error' => $e->errors()], 422);
    } catch (\Exception $e) {
        \Log::error('Error changing ID: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to change ID.'], 500);
    }
}

    /**
     * Vérifier et confirmer l'email de l'utilisateur via un token
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'token' => ['required', 'string', 'size:32'],
            ]);

            // Rechercher la demande de compte avec ce token
            $accountRequest = \App\Models\AccountRequest::where('token', $validated['token'])
                ->where('status', 'PENDING')
                ->first();

            if (!$accountRequest) {
                \Log::warning('Attempted verification with an invalid or expired token: ' . $validated['token']);
                return response()->json([
                    'error' => 'Invalid or expired token. Please submit a new account request.'
                ], 400);
            }

            // Vérifier que le token n'a pas expiré (48 heures)
            if ($accountRequest->created_at->addHours(48)->isPast()) {
                $accountRequest->update(['status' => 'EXPIRED']);
                \Log::warning('Token expired for : ' . $accountRequest->email);
                return response()->json([
                    'error' => 'This confirmation link has expired. Please submit a new request.'
                ], 400);
            }

            // Marquer la demande comme VERIFIED (email confirmé)
            $accountRequest->update([
                'status' => 'VERIFIED',
                'email_verified_at' => now(),
            ]);

            \Log::info('Email verified successfully for : ' . $accountRequest->email);

            return response()->json([
                'success' => true,
                'message' => 'Your email has been confirmed successfully! An administrator will review your request shortly.'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Error during validation of verification : ' . json_encode($e->errors()));
            return response()->json([
                'error' => 'Token missing or invalid.',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error during email verification : ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred during verification.'
            ], 500);
        }
    }
}
