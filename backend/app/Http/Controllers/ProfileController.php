<?php

namespace App\Http\Controllers;

use App\Services\ProfileService;
use App\Services\UploadService;
use App\Http\Requests\UploadProfilePictureRequest;
use App\Http\Requests\UploadProfileBannerRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

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
     * Returns all profile data (stats + growth + distribution)
     */
    public function getProfileOverview(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated'
                ], 401);
            }

            // Cache Redis pour les stats de profil - 3 minutes TTL
            $cacheKey = 'profile_overview:user_' . $user->id;
            $ttl = 60 * 3; // 3 minutes
            
            $data = Cache::remember($cacheKey, $ttl, function () use ($user) {
                return $this->profileService->getFullProfileOverview($user->id);
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            \Log::error('Profile overview error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
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
                \Log::warning('Upload attempt without authentication');
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated'
                ], 401);
            }

            \Log::info('📸 Request received', [
                'method' => $request->method(),
                'path' => $request->path(),
                'user_id' => $user->id,
                'has_file' => $request->hasFile('profile_picture'),
                'content_type' => $request->header('Content-Type'),
            ]);

            // Check if file exists
            if (!$request->hasFile('profile_picture')) {
                \Log::warning('Upload picture attempt without file', ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'error' => 'No file received',
                    'message' => 'Une image est requise.'
                ], 422);
            }

            $file = $request->file('profile_picture');
            
            // Validate file is actually valid
            if (!$file || !$file->isValid()) {
                \Log::warning('Invalid profile picture file', ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid file',
                    'message' => 'Le fichier est invalide ou corrompu.'
                ], 422);
            }

            \Log::info('📸 Uploading profile picture', [
                'user_id' => $user->id,
                'client_name' => $file->getClientOriginalName(),
                'client_size' => $file->getSize(),
                'client_mime' => $file->getClientMimeType(),
            ]);

            // Use the injected service from constructor
            $path = $this->uploadService->uploadProfilePicture($user, $file);

            $url = Storage::disk('public')->url($path);

            \Log::info('✅ Profile picture uploaded successfully', [
                'user_id' => $user->id,
                'path' => $path,
                'url' => $url,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Photo de profil téléchargée avec succès',
                'data' => [
                    'path' => $path,
                    'url' => $url,
                    'user' => $user->fresh()
                ]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('❌ Profile picture upload error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du téléchargement de la photo',
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
                \Log::warning('Upload attempt without authentication');
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated'
                ], 401);
            }

            if (!$request->hasFile('profile_banner')) {
                \Log::warning('Upload banner attempt without file', ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'error' => 'No file received',
                    'message' => 'Une bannière est requise.'
                ], 422);
            }

            $file = $request->file('profile_banner');
            
            if (!$file || !$file->isValid()) {
                \Log::warning('Invalid profile banner file', ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid file',
                    'message' => 'Le fichier est invalide ou corrompu.'
                ], 422);
            }

            \Log::info('🖼️ Uploading profile banner', [
                'user_id' => $user->id,
                'client_name' => $file->getClientOriginalName(),
                'client_size' => $file->getSize(),
                'client_mime' => $file->getClientMimeType(),
            ]);

            // Delete previous banner if exists
            if ($user->profile_banner) {
                try {
                    Storage::disk('public')->delete($user->profile_banner);
                    \Log::info('Deleted previous banner', [
                        'user_id' => $user->id,
                        'previous_path' => $user->profile_banner,
                    ]);
                } catch (\Throwable $e) {
                    \Log::warning('Error deleting previous banner', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Store new banner
            $path = $this->uploadService->uploadProfileBanner($user, $file);
            $url = Storage::disk('public')->url($path);

            \Log::info('✅ Profile banner uploaded successfully', [
                'user_id' => $user->id,
                'path' => $path,
                'url' => $url,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bannière de profil téléchargée avec succès',
                'data' => [
                    'path' => $path,
                    'url' => $url,
                    'user' => $user->fresh()
                ]
            ], 200);
        } catch (\Exception $e) {
            \Log::error('❌ Profile banner upload error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du téléchargement de la bannière',
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
                    'error' => 'User not authenticated'
                ], 401);
            }

            $deleted = $this->uploadService->deleteProfilePicture($user);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'error' => 'No profile picture to delete'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile picture deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Profile picture deletion error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error deleting profile picture',
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
                    'error' => 'User not authenticated'
                ], 401);
            }

            $deleted = $this->uploadService->deleteProfileBanner($user);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'error' => 'No profile banner to delete'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile banner deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Profile banner deletion error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error deleting profile banner',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}