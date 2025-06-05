<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Feedback;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:driver']);
    }

    public function index()
    {
        $user = auth()->user();
        $now = Carbon::now();

        // Get active trips (where user is assigned as driver)
        $activeTrips = Booking::where('driver_id', $user->id)
            ->where('status', 'in_progress')
            ->with(['vehicle', 'requestedBy', 'department'])
            ->get();

        // Get upcoming trips
        $upcomingTrips = Booking::where('driver_id', $user->id)
            ->where('status', 'approved')
            ->where('start_time', '>', $now)
            ->with(['vehicle', 'requestedBy', 'department'])
            ->orderBy('start_time')
            ->take(3)
            ->get();

        // Get completed trips
        $completedTrips = Booking::where('driver_id', $user->id)
            ->where('status', 'completed')
            ->with(['vehicle', 'requestedBy', 'department'])
            ->latest()
            ->take(5)
            ->get();

        // Get recent feedback
        $recentFeedback = Feedback::whereHas('booking', function($query) use ($user) {
            $query->where('driver_id', $user->id);
        })
        ->with(['booking.vehicle', 'booking.requestedBy', 'booking.department', 'user'])
        ->latest()
        ->take(5)
        ->get();

        // Get recent activities
        $recentActivities = collect();
        
        // Add completed trips to activities
        $recentCompletedTrips = Booking::where('driver_id', $user->id)
            ->where('status', 'completed')
            ->with(['vehicle', 'requestedBy', 'department'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function($trip) {
                return [
                    'type' => 'trip_completed',
                    'timestamp' => $trip->actual_end_time ?? $trip->updated_at,
                    'data' => $trip,
                    'message' => 'Completed trip' . ($trip->vehicle ? ' with ' . $trip->vehicle->registration_number : '')
                ];
            });
        $recentActivities = $recentActivities->concat($recentCompletedTrips);
        
        // Add in-progress trips to activities
        $recentInProgressTrips = Booking::where('driver_id', $user->id)
            ->where('status', 'in_progress')
            ->with(['vehicle', 'requestedBy', 'department'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function($trip) {
                return [
                    'type' => 'trip_started',
                    'timestamp' => $trip->actual_start_time ?? $trip->updated_at,
                    'data' => $trip,
                    'message' => 'Started trip' . ($trip->vehicle ? ' with ' . $trip->vehicle->registration_number : '')
                ];
            });
        $recentActivities = $recentActivities->concat($recentInProgressTrips);
        
        // Add newly assigned trips to activities
        $recentAssignedTrips = Booking::where('driver_id', $user->id)
            ->where('status', 'approved')
            ->with(['vehicle', 'requestedBy', 'department'])
            ->latest('updated_at')
            ->take(10)
            ->get()
            ->map(function($trip) {
                return [
                    'type' => 'trip_assigned',
                    'timestamp' => $trip->approved_at ?? $trip->updated_at,
                    'data' => $trip,
                    'message' => 'Assigned to trip with ' . $trip->vehicle->registration_number
                ];
            });
        $recentActivities = $recentActivities->concat($recentAssignedTrips);
        
        // Add recent feedback to activities
        $feedbackActivities = $recentFeedback->map(function($feedback) {
            return [
                'type' => 'feedback_received',
                'timestamp' => $feedback->created_at,
                'data' => $feedback,
                'message' => 'Received ' . $feedback->rating . '/5 rating from ' . $feedback->user->name
            ];
        });
        $recentActivities = $recentActivities->concat($feedbackActivities);
        
        // Sort by timestamp descending and take the 15 most recent
        $recentActivities = $recentActivities->sortByDesc('timestamp')->take(15);

        // Get vehicle reports
        $vehicleReports = collect();
        
        // Get vehicles used by the driver
        $driverVehicles = Vehicle::whereHas('bookings', function($query) use ($user) {
            $query->where('driver_id', $user->id);
        })->get();
        
        foreach ($driverVehicles as $vehicle) {
            // Get latest trip with this vehicle
            $latestTrip = Booking::where('driver_id', $user->id)
                ->where('vehicle_id', $vehicle->id)
                ->latest()
                ->first();

            // Get total trips with this vehicle
            $totalTrips = Booking::where('driver_id', $user->id)
                ->where('vehicle_id', $vehicle->id)
                ->count();

            // Get total hours with this vehicle
            $totalHours = Booking::where('driver_id', $user->id)
                ->where('vehicle_id', $vehicle->id)
                ->where('status', 'completed')
                ->sum(DB::raw('TIMESTAMPDIFF(HOUR, start_time, end_time)'));

            // Get average rating for trips with this vehicle
            $avgRating = Feedback::whereHas('booking', function($query) use ($user, $vehicle) {
                $query->where('driver_id', $user->id)
                    ->where('vehicle_id', $vehicle->id);
            })->avg('rating');

            $vehicleReports->push([
                'vehicle' => $vehicle,
                'latest_trip' => $latestTrip,
                'total_trips' => $totalTrips,
                'total_hours' => $totalHours,
                'avg_rating' => number_format($avgRating ?? 0, 1),
                'status' => $vehicle->status,
                'last_maintenance' => $vehicle->last_maintenance_date,
                'next_maintenance' => $vehicle->next_maintenance_date,
            ]);
        }

        // Sort vehicles by most recently used
        $vehicleReports = $vehicleReports->sortByDesc(function($report) {
            return $report['latest_trip']->created_at ?? Carbon::parse('1970-01-01');
        });

        // Calculate statistics
        $stats = [
            'total_trips' => Booking::where('driver_id', $user->id)->count(),
            'completed_trips' => Booking::where('driver_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'upcoming_trips' => Booking::where('driver_id', $user->id)
                ->where('status', 'approved')
                ->count(),
            'active_trips' => Booking::where('driver_id', $user->id)
                ->where('status', 'in_progress')
                ->count(),
            'total_feedback' => Feedback::whereHas('booking', function($query) use ($user) {
                $query->where('driver_id', $user->id);
            })->count(),
            'average_rating' => number_format(Feedback::whereHas('booking', function($query) use ($user) {
                $query->where('driver_id', $user->id);
            })->avg('rating') ?? 0, 1),
            'trips_this_month' => Booking::where('driver_id', $user->id)
                ->whereMonth('start_time', $now->month)
                ->count(),
            'total_hours' => number_format(Booking::where('driver_id', $user->id)
                ->where('status', 'completed')
                ->sum(DB::raw('TIMESTAMPDIFF(HOUR, start_time, end_time)')), 0)
        ];

        return view('driver.dashboard', compact(
            'activeTrips',
            'upcomingTrips',
            'completedTrips',
            'recentFeedback',
            'recentActivities',
            'vehicleReports',
            'stats'
        ));
    }
}
