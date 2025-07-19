<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\NotificationResource;

class NotificationController extends Controller
{
    // Fetch all notifications (latest first, optionally paginated)
    public function all(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->take(20) // Optional: adjust or use pagination
            ->get();

        return NotificationResource::collection($notifications);
    }

    // Fetch only unread notifications (latest first)
    public function unread(Request $request)
    {
        $notifications = $request->user()
            ->unreadNotifications()
            ->latest()
            ->get();

        return NotificationResource::collection($notifications);
    }

    // Mark all unread notifications as read
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    // Mark a specific notification as read
    public function markAsRead($id, Request $request)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }
}
