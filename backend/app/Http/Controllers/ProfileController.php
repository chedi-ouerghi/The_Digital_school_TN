<?php

namespace App\Http\Controllers;

use App\Services\ProfileService;
use App\Services\UploadService;
use App\Http\Requests\UploadProfilePictureRequest;
use App\Http\Requests\UploadProfileBannerRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    protected ProfileService $profileService;
    protected UploadService $uploadService;

    public function __construct(ProfileService $profileService, UploadService $uploadService)
    {
        $this->profileService = $profileService;
        $this->uploadService = $uploadService;
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

    /**
     * Upload profile picture
     * Allowed for CLIENT and ADMIN roles
     */
public function uploadProfilePicture(UploadProfilePictureRequest $request): JsonResponse
{
    try {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Utilisateur non authentifié'
            ], 401);
        }

        if (!$request->hasFile('profile_picture')) {
            return response()->json([
                'success' => false,
                'error' => 'Aucun fichier reçu',
                'message' => 'profile_picture field is required'
            ], 422);
        }

        $file = $request->file('profile_picture');
        if (!$file->isValid()) {
            return response()->json([
                'success' => false,
                'error' => 'Fichier invalide',
            ], 422);
        }

        \Log::info('Uploading profile picture (controller)', [
            'user_id' => $user->id,
            'client_name' => $file->getClientOriginalName(),
            'client_size' => $file->getSize(),
            'client_mime' => $file->getClientMimeType(),
            'client_ext' => $file->getClientOriginalExtension(),
        ]);

        // Use the injected service from constructor
        $path = $this->uploadService->uploadProfilePicture($user, $file);

        $url = Storage::disk('public')->url($path);

        return response()->json([
            'success' => true,
            'message' => 'Profile picture uploaded successfully',
            'data' => [
                'path' => $path,
                'url' => $url,
                'user' => $user->fresh()
            ]
        ], 200);
    } catch (\Exception $e) {
        \Log::error('Erreur upload profile picture: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'error' => 'Erreur lors du téléchargement de la photo de profil',
            'message' => $e->getMessage()
        ], 500);
    }
}


    /**
     * Upload profile banner
     * Allowed for CLIENT and ADMIN roles
     */
    public function uploadProfileBanner(UploadProfileBannerRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Utilisateur non authentifié'
                ], 401);
            }

            if (!$request->hasFile('profile_banner')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Aucun fichier reçu',
                    'message' => 'profile_banner field is required'
                ], 422);
            }

            $file = $request->file('profile_banner');
            if (!$file->isValid()) {
                \Log::warning('Invalid profile banner file received', [
                    'user_id' => $user->id,
                    'client_name' => $file ? $file->getClientOriginalName() : null,
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Fichier invalide',
                ], 422);
            }

            \Log::info('Uploading profile banner (controller)', [
                'user_id' => $user->id,
                'client_name' => $file->getClientOriginalName(),
                'client_size' => $file->getSize(),
                'client_mime' => $file->getClientMimeType(),
                'client_ext' => $file->getClientOriginalExtension(),
            ]);

            // Delete previous banner if exists
            if ($user->profile_banner) {
                try {
                    $deleted = Storage::disk('public')->delete($user->profile_banner);
                    \Log::info('Deleted previous banner (controller)', [
                        'user_id' => $user->id,
                        'previous_path' => $user->profile_banner,
                        'deleted' => $deleted,
                    ]);
                } catch (\Throwable $e) {
                    \Log::error('Error deleting previous banner (controller)', [
                        'user_id' => $user->id,
                        'previous_path' => $user->profile_banner,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            // Store new banner in public disk
            $path = $file->storePublicly('profile_banners', 'public');

            \Log::info('Profile banner stored (controller)', [
                'user_id' => $user->id,
                'path' => $path,
            ]);

            // Update user and refresh
            $user->profile_banner = $path;
            $user->save();
            $user = $user->fresh();

            $url = Storage::disk('public')->url($path);

            return response()->json([
                'success' => true,
                'message' => 'Profile banner uploaded successfully',
                'data' => [
                    'path' => $path,
                    'url' => $url,
                    'user' => $user
                ]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Erreur upload profile banner: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du téléchargement de la bannière de profil',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete profile picture
     */
    public function deleteProfilePicture(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Utilisateur non authentifié'
                ], 401);
            }

            $deleted = $this->uploadService->deleteProfilePicture($user);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'error' => 'Aucune photo de profil à supprimer'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile picture deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur delete profile picture: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression de la photo de profil',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete profile banner
     */
    public function deleteProfileBanner(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Utilisateur non authentifié'
                ], 401);
            }

            $deleted = $this->uploadService->deleteProfileBanner($user);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'error' => 'Aucune bannière de profil à supprimer'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile banner deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur delete profile banner: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression de la bannière de profil',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
