<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Booking;
use App\Models\MaintenanceRecord;
use App\Models\Feedback;
use App\Models\Activity;
use App\Models\VehicleReport;
use App\Models\ServiceRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $now = Carbon::now();

        // Get counts for different statuses
        $userCount = User::count();
        $vehicleCount = Vehicle::count();
        $pendingBookingCount = Booking::where('status', 'pending')->count();
        
        // Get active bookings (both in_progress and currently active approved bookings)
        $activeBookings = Booking::where(function($query) use ($now) {
            $query->where('status', 'in_progress')
                  ->orWhere(function($q) use ($now) {
                      $q->where('status', 'approved')
                        ->where('start_time', '<=', $now)
                        ->where('end_time', '>=', $now);
                  });
        })->count();

        $maintenanceVehicles = Vehicle::where('status', 'maintenance')->count();
        $totalBookings = Booking::count();
        
        // Get maintenance tasks from vehicle reports
        $pendingMaintenanceTasks = \App\Models\VehicleReport::where('status', 'pending')
            ->orWhere('status', 'in_progress')
            ->count();
            
        $completedMaintenanceTasks = \App\models\VehicleReport::where('status', 'completed')
            ->where('updated_at', '>=', now()->subDays(30))
            ->count();
            
        // Get maintenance schedules
        $pendingSchedules = \App\Models\MaintenanceSchedule::where('status', 'pending')
            ->orWhere('status', 'in_progress')
            ->count();
            
        $completedSchedules = \App\Models\MaintenanceSchedule::where('status', 'completed')
            ->where('updated_at', '>=', now()->subDays(30))
            ->count();

        // Create stats array for dashboard
        $stats = [
            'total_vehicles' => $vehicleCount,
            'total_users' => $userCount,
            'total_bookings' => $totalBookings,
            'pending_bookings' => $pendingBookingCount,
            'active_bookings' => $activeBookings,
            'maintenance_vehicles' => $maintenanceVehicles,
            'pending_maintenance' => $pendingMaintenanceTasks + $pendingSchedules,
            'completed_maintenance' => $completedMaintenanceTasks + $completedSchedules,
            'recent_completed_maintenance' => $completedMaintenanceTasks + $completedSchedules,
            'pending_feedback' => Feedback::where('status', 'pending')->count(),
            'total_feedback' => Feedback::count(),
            'pending_vehicle_reports' => VehicleReport::where('status', 'pending')->count()
        ];
        
        // Get pending bookings with relationships
        $pendingBookings = Booking::where('status', 'pending')
            ->with(['vehicle.brand', 'requestedBy'])
            ->latest()
            ->take(5)
            ->get();
        
        // Create alias for the view
        $pending_approvals = $pendingBookings;
        
        // Get pending service requests that need admin approval
        $pendingServiceRequests = ServiceRequest::where('status', 'pending')
            ->with(['vehicle', 'requestedBy'])
            ->latest()
            ->take(5)
            ->get();

        // Get pending vehicle reports that need admin approval
        $pendingVehicleReports = VehicleReport::where('status', 'pending')
            ->with(['vehicle', 'driver'])
            ->latest()
            ->take(5)
            ->get();
            
        // Get recent activities
        $recentActivities = Activity::latest()->take(5)->get();
        $recent_activities = $recentActivities; // Alias for the view
            
        // Get vehicles requiring maintenance
        $vehiclesNeedingMaintenance = Vehicle::where('status', 'maintenance')
            ->orWhereHas('maintenanceSchedules', function ($query) {
                $query->where('status', 'pending')
                    ->where('scheduled_date', '<=', now()->addDays(7));
            })
            ->with('maintenanceSchedules')
            ->take(5)
            ->get();
            
        // Get recent vehicle reports
        $recentReports = VehicleReport::with('vehicle')
            ->latest()
            ->take(5)
            ->get();
            
        // Get feedback needing review
        $pendingFeedback = Feedback::where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();
            
        // Create alias for the view - get most recent feedback regardless of status
        $recent_feedback = Feedback::with('user')->latest()->take(5)->get();
            
        // Get statistics for today
        $today = Carbon::today();
        $todayStats = [
            'bookings' => Booking::whereDate('created_at', $today)->count(),
            'users' => User::whereDate('created_at', $today)->count(),
            'maintenance' => MaintenanceRecord::whereDate('created_at', $today)->count(),
        ];
        
        return view('admin.dashboard', compact(
            'stats',
            'userCount', 
            'vehicleCount', 
            'pendingBookingCount', 
            'pendingBookings',
            'pending_approvals',
            'activeBookings',
            'maintenanceVehicles',
            'recent_activities',
            'vehiclesNeedingMaintenance',
            'recentReports',
            'pendingFeedback',
            'recent_feedback',
            'todayStats',
            'pendingServiceRequests',
            'pendingVehicleReports'
        ));
    }
}
