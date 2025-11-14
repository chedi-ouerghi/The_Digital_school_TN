<?php

namespace App\Http\Controllers;

use App\Services\ProfileService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    protected ProfileService $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Retourne toutes les données du profil (stats + growth + distribution)
     */
    public function getProfileOverview(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Utilisateur non authentifié'
                ], 401);
            }

            $data = $this->profileService->getFullProfileOverview($user->id);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur profile overview: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Erreur interne serveur',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
