<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->latest()->get();
        
        Log::info('Fetching notifications for API', [
            'user_id' => $user->id,
            'total_notifications' => $notifications->count()
        ]);
        
        return response()->json([
            'notifications' => $notifications->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'read' => !is_null($notification->read_at),
                    'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                    'read_at' => $notification->read_at ? $notification->read_at->format('Y-m-d H:i:s') : null
                ];
            })
        ]);
    }

    public function unreadCount()
    {
        $user = Auth::user();
        $count = $user->unreadNotifications->count();
        
        Log::info('Fetching unread notification count for API', [
            'user_id' => $user->id,
            'unread_count' => $count
        ]);
        
        return response()->json(['count' => $count]);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        
        Log::info('Marking all notifications as read via API', [
            'user_id' => $user->id,
            'unread_count' => $user->unreadNotifications->count()
        ]);
        
        $user->unreadNotifications->markAsRead();
        
        return response()->json(['message' => 'All notifications marked as read']);
    }
} 