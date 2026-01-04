<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    /**
     * List notifications for the authenticated user
     * Only show notifications for verified accounts
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est vérifié (email_verified_at est défini)
        if (!$user->email_verified_at) {
            // Pour les comptes en attente, retourner un tableau vide ou un message
            return response()->json([
                'message' => 'Your account is pending verification',
                'data' => []
            ]);
        }

        // Cache Redis pour les notifications - 1 minute TTL
        $cacheKey = 'notifications:user_' . $user->id . ':page_' . ($request->query('page', 1));
        $ttl = 60; // 1 minute
        
        $notifications = Cache::remember($cacheKey, $ttl, function () use ($user) {
            return Notification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        });

        return response()->json($notifications);
    }

    /**
     * Mark a notification as read for the authenticated user
     */
    public function markAsRead($id): JsonResponse
    {
        $user = Auth::user();
        $notification = Notification::findOrFail($id);

        if ($notification->user_id !== $user->id) {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        $notification->is_read = true;
        $notification->save();

        return response()->json([
            'message' => 'Notification marked as read.',
            'notification' => $notification
        ]);
    }

    /**
     * Mark all notifications as read for the authenticated user
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = Auth::user();

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'message' => 'All notifications marked as read.'
        ]);
    }
}
