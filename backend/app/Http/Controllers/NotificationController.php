<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * List notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Simple list: notifications explicitly created for this user
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }

    /**
     * Mark a notification as read for the authenticated user.
     */
    public function markAsRead($id): JsonResponse
    {
        $user = Auth::user();
        $notification = Notification::findOrFail($id);

        if ($notification->user_id !== $user->id) {
            return response()->json(['error' => 'Accès refusé.'], 403);
        }

        $notification->is_read = true;
        $notification->save();

        return response()->json(['message' => 'Notification marquée comme lue.']);
    }
}
