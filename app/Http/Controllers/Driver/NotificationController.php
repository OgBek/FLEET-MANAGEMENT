<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:driver');
    }

    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(4);
            
        return view('driver.notifications.index', compact('notifications'));
    }

    public function show(Notification $notification)
    {
        // Check if the notification belongs to the authenticated user
        if ($notification->notifiable_id !== auth()->id()) {
            return abort(403, 'Unauthorized action. This notification does not belong to you.');
        }

        // Mark the notification as read
        $notification->markAsRead();

        // Extract the data from the notification
        $data = $notification->data;

        // Determine where to redirect based on notification type
        if (isset($data['booking_id'])) {
            // For booking-related notifications, redirect to the driver's trip view
            $booking = \App\Models\Booking::find($data['booking_id']);
            
            if ($booking && $booking->driver_id === auth()->id()) {
                return redirect()->route('driver.trips.show', $booking->id);
            }
            
            // If booking not found or driver doesn't have permission, show a message
            return back()->with('warning', 'The related trip is not available or you are not assigned to it.');
        }

        // For other notification types or if no specific redirect is available
        return back()->with('info', 'Notification marked as read.');
    }

    public function markAsRead(Notification $notification)
    {
        $notification->markAsRead();
        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead()
    {
        auth()->user()
            ->unreadNotifications()
            ->get()
            ->each(function($notification) {
                $notification->markAsRead();
            });

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Clear all notifications for the authenticated user
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearAll()
    {
        auth()->user()->notifications()->delete();
        
        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'All notifications have been cleared.']);
        }
        
        return back()->with('success', 'All notifications have been cleared.');
    }
}