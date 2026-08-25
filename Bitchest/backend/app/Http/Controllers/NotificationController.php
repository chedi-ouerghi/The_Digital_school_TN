<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    /**
     * List notifications for the authenticated user
     * Only show notifications for verified accounts
     */
    public function index(Request $request, NotificationService $notificationService): JsonResponse
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

        $read = $request->has('read') ? filter_var($request->query('read'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;
        $notifications = $notificationService->getUserNotifications($user, (int) $request->query('per_page', 20), $read);
        return response()->json([
            'data' => $notifications->items(),
            'current_page' => $notifications->currentPage(),
            'last_page' => $notifications->lastPage(),
            'per_page' => $notifications->perPage(),
            'total' => $notifications->total(),
            'unread_count' => $notificationService->getUnreadCount($user),
        ]);
    }

    /**
     * Mark a notification as read for the authenticated user
     */
    public function unreadCount(NotificationService $notificationService): JsonResponse
    {
        $user = Auth::user();
        return response()->json(['unread_count' => $notificationService->getUnreadCount($user)]);
    }

    public function markAsRead($id, NotificationService $notificationService): JsonResponse
    {
        $notification = $notificationService->markAsRead(Auth::user(), $id);

        return response()->json([
            'message' => 'Notification marked as read.',
            'notification' => $notification
        ]);
    }

    /**
     * Mark all notifications as read for the authenticated user
     */
    public function markAllAsRead(NotificationService $notificationService): JsonResponse
    {
        $updated = $notificationService->markAllAsRead(Auth::user());

        return response()->json([
            'message' => 'All notifications marked as read.',
            'updated' => $updated,
        ]);
    }
}
