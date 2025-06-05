<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:department_head|department_staff');
    }

    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(4);
            
        return view('client.notifications.index', compact('notifications'));
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

        // Add debug logging
        Log::info('Notification data:', [
            'notification_id' => $notification->id,
            'data' => $data,
            'type' => $notification->type
        ]);

        // Determine where to redirect based on notification type
        if (isset($data['booking_id'])) {
            // For booking-related notifications
            $booking = \App\Models\Booking::find($data['booking_id']);
            
            if ($booking && $booking->requested_by === auth()->id()) {
                return redirect()->route('client.bookings.show', $booking->id);
            }
            
            return back()->with('warning', 'The related booking is not available or you do not have permission to view it.');
        }
        
        // Handle vehicle notifications
        if (isset($data['vehicle_id'])) {
            $vehicle = Vehicle::find($data['vehicle_id']);
            
            if ($vehicle) {
                Log::info('Redirecting to vehicle show page:', [
                    'vehicle_id' => $vehicle->id,
                    'user_id' => auth()->id(),
                    'route' => 'client.vehicles.show'
                ]);
                return redirect()->route('client.vehicles.show', $vehicle);
            }
            
            return back()->with('warning', 'The related vehicle is not available.');
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