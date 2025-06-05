<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\VehicleReport;
use App\Models\MaintenanceSchedule;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:maintenance_staff');
    }

    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(4);
            
        return view('maintenance.notifications.index', compact('notifications'));
    }

    public function show(Notification $notification)
    {
        // Check if the notification belongs to the authenticated user
        if ($notification->notifiable_id !== auth()->id()) {
            return abort(403, 'Unauthorized action. This notification does not belong to you.');
        }

        // Extract the data from the notification
        $data = $notification->data;
        
        // Log the notification data for debugging
        Log::info('Notification data:', $data);
        
        // Direct handling for schedule_id if it exists
        if (isset($data['schedule_id'])) {
            Log::info('Direct schedule_id found:', ['schedule_id' => $data['schedule_id']]);
            $schedule = MaintenanceSchedule::find($data['schedule_id']);
            if ($schedule) {
                Log::info('Schedule found, redirecting to schedule:', ['schedule_id' => $schedule->id]);
                // Mark as read only after we confirm we can redirect
                $notification->markAsRead();
                return redirect()->route('maintenance.schedules.show', $schedule->id);
            }
        }

        // Special handling for maintenance task notifications
        if (isset($data['type']) && $data['type'] === 'maintenance_due') {
            Log::info('Handling maintenance task notification');
            
            // Try to find by vehicle_id if provided
            if (isset($data['vehicle_id'])) {
                Log::info('Looking for schedule by vehicle_id:', ['vehicle_id' => $data['vehicle_id']]);
                $schedule = MaintenanceSchedule::where('vehicle_id', $data['vehicle_id'])
                    ->where('assigned_to', auth()->id())
                    ->where('status', '!=', 'completed')
                    ->latest()
                    ->first();
                    
                if ($schedule) {
                    Log::info('Found schedule by vehicle_id:', ['schedule_id' => $schedule->id]);
                    // Mark as read only after we confirm we can redirect
                    $notification->markAsRead();
                    return redirect()->route('maintenance.schedules.show', $schedule->id);
                }
            }
            
            // If we couldn't find a specific schedule, redirect to schedules list
            Log::info('No specific schedule found, redirecting to schedules list');
            // Mark as read since we're redirecting to a useful page
            $notification->markAsRead();
            return redirect()->route('maintenance.schedules.index')
                ->with('info', 'The specific maintenance schedule could not be found. Here are all your assigned schedules.');
        }
        
        // Special handling for maintenance assignment notifications
        if (isset($data['type']) && $data['type'] === 'maintenance_assigned') {
            Log::info('Handling maintenance assignment notification');
            
            // Try to find by vehicle_id if provided
            if (isset($data['vehicle_id'])) {
                Log::info('Looking for schedule by vehicle_id:', ['vehicle_id' => $data['vehicle_id']]);
                $schedule = MaintenanceSchedule::where('vehicle_id', $data['vehicle_id'])
                    ->where('assigned_to', auth()->id())
                    ->where('status', '!=', 'completed')
                    ->latest()
                    ->first();
                    
                if ($schedule) {
                    Log::info('Found schedule by vehicle_id:', ['schedule_id' => $schedule->id]);
                    // Mark as read only after we confirm we can redirect
                    $notification->markAsRead();
                    return redirect()->route('maintenance.schedules.show', $schedule->id);
                }
            }
            
            // If we couldn't find a specific schedule, redirect to schedules list
            Log::info('No specific schedule found, redirecting to schedules list');
            // Mark as read since we're redirecting to a useful page
            $notification->markAsRead();
            return redirect()->route('maintenance.schedules.index')
                ->with('info', 'The specific maintenance schedule could not be found. Here are all your assigned schedules.');
        }

        // Determine where to redirect based on notification type and data
        if (isset($data['report_id'])) {
            // For vehicle issue reports - find associated maintenance schedule
            $report = VehicleReport::find($data['report_id']);
            Log::info('Found vehicle report:', ['report_id' => $data['report_id'], 'report_exists' => (bool)$report]);
            
            if ($report) {
                Log::info('Vehicle report details:', [
                    'vehicle_id' => $report->vehicle_id,
                    'user_id' => auth()->id(),
                    'report_status' => $report->status
                ]);
                
                // Check if there's a maintenance schedule associated with this report
                $schedule = MaintenanceSchedule::where('vehicle_id', $report->vehicle_id)
                    ->where('assigned_to', auth()->id())
                    ->where('status', '!=', 'completed')
                    ->latest()
                    ->first();
                
                Log::info('Maintenance schedule query result:', ['schedule_exists' => (bool)$schedule]);
                
                if ($schedule) {
                    Log::info('Redirecting to maintenance schedule:', ['schedule_id' => $schedule->id]);
                    // Mark as read only after we confirm we can redirect
                    $notification->markAsRead();
                    return redirect()->route('maintenance.schedules.show', $schedule->id);
                }
                
                // If no active schedule found, try to find any schedule for this vehicle
                $anySchedule = MaintenanceSchedule::where('vehicle_id', $report->vehicle_id)
                    ->latest()
                    ->first();
                
                Log::info('Any maintenance schedule query result:', ['any_schedule_exists' => (bool)$anySchedule]);
                
                if ($anySchedule) {
                    Log::info('Redirecting to any maintenance schedule:', ['schedule_id' => $anySchedule->id]);
                    // Mark as read only after we confirm we can redirect
                    $notification->markAsRead();
                    return redirect()->route('maintenance.schedules.show', $anySchedule->id);
                }
                
                // If still no schedule found, redirect to the vehicle report itself
                Log::info('No maintenance schedule found, redirecting to vehicle report');
                // Mark as read only after we confirm we can redirect
                $notification->markAsRead();
                return redirect()->route('maintenance.vehicle-reports.show', $report->id);
            }
        } elseif (isset($data['vehicle_report_id'])) {
            // Alternative field name for vehicle reports
            $report = VehicleReport::find($data['vehicle_report_id']);
            Log::info('Found vehicle report (alt field):', ['vehicle_report_id' => $data['vehicle_report_id'], 'report_exists' => (bool)$report]);
            
            if ($report) {
                Log::info('Vehicle report details (alt field):', [
                    'vehicle_id' => $report->vehicle_id,
                    'user_id' => auth()->id(),
                    'report_status' => $report->status
                ]);
                
                // Check if there's a maintenance schedule associated with this report
                $schedule = MaintenanceSchedule::where('vehicle_id', $report->vehicle_id)
                    ->where('assigned_to', auth()->id())
                    ->where('status', '!=', 'completed')
                    ->latest()
                    ->first();
                
                Log::info('Maintenance schedule query result (alt field):', ['schedule_exists' => (bool)$schedule]);
                
                if ($schedule) {
                    Log::info('Redirecting to maintenance schedule (alt field):', ['schedule_id' => $schedule->id]);
                    // Mark as read only after we confirm we can redirect
                    $notification->markAsRead();
                    return redirect()->route('maintenance.schedules.show', $schedule->id);
                }
                
                // If no active schedule found, try to find any schedule for this vehicle
                $anySchedule = MaintenanceSchedule::where('vehicle_id', $report->vehicle_id)
                    ->latest()
                    ->first();
                
                Log::info('Any maintenance schedule query result (alt field):', ['any_schedule_exists' => (bool)$anySchedule]);
                
                if ($anySchedule) {
                    Log::info('Redirecting to any maintenance schedule (alt field):', ['schedule_id' => $anySchedule->id]);
                    // Mark as read only after we confirm we can redirect
                    $notification->markAsRead();
                    return redirect()->route('maintenance.schedules.show', $anySchedule->id);
                }
                
                // If still no schedule found, redirect to the vehicle report itself
                Log::info('No maintenance schedule found, redirecting to vehicle report (alt field)');
                // Mark as read only after we confirm we can redirect
                $notification->markAsRead();
                return redirect()->route('maintenance.vehicle-reports.show', $report->id);
            }
        } elseif (isset($data['service_request_id'])) {
            // For service request notifications
            Log::info('Redirecting to service request:', ['service_request_id' => $data['service_request_id']]);
            // Mark as read only after we confirm we can redirect
            $notification->markAsRead();
            return redirect()->route('maintenance.service-requests.show', $data['service_request_id']);
        } elseif (isset($data['maintenance_schedule_id'])) {
            // For maintenance schedule notifications
            Log::info('Redirecting to maintenance schedule directly:', ['schedule_id' => $data['maintenance_schedule_id']]);
            // Mark as read only after we confirm we can redirect
            $notification->markAsRead();
            return redirect()->route('maintenance.schedules.show', $data['maintenance_schedule_id']);
        } elseif (isset($data['booking_id']) && isset($data['maintenance_related']) && $data['maintenance_related']) {
            // For booking-related maintenance notifications
            $booking = \App\Models\Booking::find($data['booking_id']);
            Log::info('Found booking:', ['booking_id' => $data['booking_id'], 'booking_exists' => (bool)$booking]);
            
            if ($booking && $booking->maintenanceSchedule) {
                Log::info('Redirecting to booking maintenance schedule:', ['schedule_id' => $booking->maintenanceSchedule->id]);
                // Mark as read only after we confirm we can redirect
                $notification->markAsRead();
                return redirect()->route('maintenance.schedules.show', $booking->maintenanceSchedule->id);
            }
        }

        // If we reach here, no redirect was performed
        Log::warning('No redirect condition met, returning to previous page with warning');
        // Don't mark as read if we couldn't find the related content
        return back()->with('info', 'The notification details could not be found. To view all your maintenance tasks, please go to the Schedules page.');
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