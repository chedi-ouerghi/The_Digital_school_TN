<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class UploadService
{
    /**
     * Upload profile picture for user
     */
    public function uploadProfilePicture(User $user, UploadedFile $file): string
    {
        try {
            // Log incoming file info
            \Log::info('Starting uploadProfilePicture', [
                'user_id' => $user->id,
                'client_name' => $file->getClientOriginalName(),
                'client_size' => $file->getSize(),
                'client_mime' => $file->getClientMimeType(),
                'client_ext' => $file->getClientOriginalExtension(),
            ]);

            // Delete old picture if exists
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                $deleted = Storage::disk('public')->delete($user->profile_picture);
                \Log::info('Deleted previous profile picture', [
                    'user_id' => $user->id,
                    'previous_path' => $user->profile_picture,
                    'deleted' => $deleted,
                ]);
            }

            // Create user-specific directory
            $directory = 'profile_pictures/' . $user->id;
            
            // Generate unique filename with timestamp
            $filename = Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Store the file
            \Log::info('Storing profile picture to disk', [
                'disk' => 'public',
                'directory' => $directory,
                'filename' => $filename,
            ]);

            $path = Storage::disk('public')->putFileAs(
                $directory,
                $file,
                $filename
            );

            \Log::info('putFileAs returned', ['path' => $path]);

            if (!$path) {
                throw new \Exception('Failed to store profile picture file');
            }

            // Update user record
            $user->update(['profile_picture' => $path]);

            \Log::info('Profile picture uploaded successfully', [
                'user_id' => $user->id,
                'path' => $path,
                'filename' => $filename
            ]);

            return $path;
        } catch (\Exception $e) {
            \Log::error('Error uploading profile picture', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Upload profile banner for user
     */
    public function uploadProfileBanner(User $user, UploadedFile $file): string
    {
        try {
            // Log incoming file info
            \Log::info('Starting uploadProfileBanner', [
                'user_id' => $user->id,
                'client_name' => $file->getClientOriginalName(),
                'client_size' => $file->getSize(),
                'client_mime' => $file->getClientMimeType(),
                'client_ext' => $file->getClientOriginalExtension(),
            ]);

            // Delete old banner if exists
            if ($user->profile_banner && Storage::disk('public')->exists($user->profile_banner)) {
                $deleted = Storage::disk('public')->delete($user->profile_banner);
                \Log::info('Deleted previous profile banner', [
                    'user_id' => $user->id,
                    'previous_path' => $user->profile_banner,
                    'deleted' => $deleted,
                ]);
            }

            // Create user-specific directory
            $directory = 'profile_banners/' . $user->id;
            
            // Generate unique filename with timestamp
            $filename = Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension();

            // Store the file
            \Log::info('Storing profile banner to disk', [
                'disk' => 'public',
                'directory' => $directory,
                'filename' => $filename,
            ]);

            $path = Storage::disk('public')->putFileAs(
                $directory,
                $file,
                $filename
            );

            \Log::info('putFileAs returned', ['path' => $path]);

            if (!$path) {
                throw new \Exception('Failed to store profile banner file');
            }

            // Update user record
            $user->update(['profile_banner' => $path]);

            \Log::info('Profile banner uploaded successfully', [
                'user_id' => $user->id,
                'path' => $path,
                'filename' => $filename
            ]);

            return $path;
        } catch (\Exception $e) {
            \Log::error('Error uploading profile banner', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete profile picture
     */
    public function deleteProfilePicture(User $user): bool
    {
        try {
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
                $user->update(['profile_picture' => null]);
                
                \Log::info('Profile picture deleted successfully', [
                    'user_id' => $user->id
                ]);
                
                return true;
            }
            return false;
        } catch (\Exception $e) {
            \Log::error('Error deleting profile picture', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete profile banner
     */
    public function deleteProfileBanner(User $user): bool
    {
        try {
            if ($user->profile_banner && Storage::disk('public')->exists($user->profile_banner)) {
                Storage::disk('public')->delete($user->profile_banner);
                $user->update(['profile_banner' => null]);
                
                \Log::info('Profile banner deleted successfully', [
                    'user_id' => $user->id
                ]);
                
                return true;
            }
            return false;
        } catch (\Exception $e) {
            \Log::error('Error deleting profile banner', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get profile picture URL
     */
    public function getProfilePictureUrl(User $user): ?string
    {
        if ($user->profile_picture) {
            return url('storage/' . $user->profile_picture);
        }
        return null;
    }

    /**
     * Get profile banner URL
     */
    public function getProfileBannerUrl(User $user): ?string
    {
        if ($user->profile_banner) {
            return url('storage/' . $user->profile_banner);
        }
        return null;
    }
}

