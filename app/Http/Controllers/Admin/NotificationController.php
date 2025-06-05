<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->paginate(3);
        $unreadCount = $user->unreadNotifications->count();
        
        Log::info('Fetching notifications for user', [
            'user_id' => $user->id,
            'total_notifications' => $notifications->total(),
            'unread_count' => $unreadCount
        ]);
        
        return view('admin.notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        
        Log::info('Marking notification as read', [
            'user_id' => $user->id,
            'notification_id' => $id
        ]);
        
        $notification->markAsRead();
        
        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        
        Log::info('Marking all notifications as read', [
            'user_id' => $user->id,
            'unread_count' => $user->unreadNotifications->count()
        ]);
        
        $user->unreadNotifications->markAsRead();
        
        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        
        Log::info('Deleting notification', [
            'user_id' => $user->id,
            'notification_id' => $id
        ]);
        
        $notification->delete();
        
        return back()->with('success', 'Notification deleted successfully.');
    }

    public function clearAll()
    {
        $user = Auth::user();
        $count = $user->notifications()->count();
        
        Log::info('Clearing all notifications', [
            'user_id' => $user->id,
            'notification_count' => $count
        ]);
        
        $user->notifications()->delete();
        
        return back()->with('success', "All $count notifications have been cleared.");
    }
} 