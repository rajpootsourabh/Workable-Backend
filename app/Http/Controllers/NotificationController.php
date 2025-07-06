<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\NotificationResource;

class NotificationController extends Controller
{
    public function all(Request $request)
    {
        return NotificationResource::collection($request->user()->notifications);
    }

    public function unread(Request $request)
    {
        return NotificationResource::collection($request->user()->unreadNotifications);
    }

    public function markAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }
}
