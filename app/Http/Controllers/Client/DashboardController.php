<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Department;
use App\Models\Feedback;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $department = $user->department;
        $now = Carbon::now();
        $department_id = $request->user()->department_id;

        // Get recent bookings
        $recentBookings = Booking::where('requested_by', $user->id)
            ->with(['vehicle'])
            ->latest()
            ->take(3)
            ->get();

        // Get active bookings (in_progress or currently active approved bookings)
        $activeBookings = Booking::where('requested_by', $user->id)
            ->where(function($query) use ($now) {
                $query->where('status', 'in_progress')
                      ->orWhere(function($q) use ($now) {
                          $q->where('status', 'approved')
                            ->where('start_time', '<=', $now)
                            ->where('end_time', '>=', $now);
                      });
            })
            ->with(['vehicle', 'driver'])
            ->get();

        // Get department's active bookings
        $departmentActiveBookings = Booking::where('department_id', $department->id)
            ->where(function($query) use ($now) {
                $query->where('status', 'in_progress')
                      ->orWhere(function($q) use ($now) {
                          $q->where('status', 'approved')
                            ->where('start_time', '<=', $now)
                            ->where('end_time', '>=', $now);
                      });
            })
            ->with(['vehicle', 'requestedBy', 'driver'])
            ->get();

        // Get available vehicles (not in maintenance, not in_use, and not currently booked)
        $availableVehicles = Vehicle::whereNotIn('id', function($query) use ($now) {
                $query->select('vehicle_id')
                    ->from('bookings')
                    ->where(function($q) use ($now) {
                        $q->where('status', 'in_progress')
                          ->orWhere(function($subQ) use ($now) {
                              $subQ->where('status', 'approved')
                                  ->where('start_time', '<=', $now)
                                  ->where('end_time', '>=', $now);
                          });
                    });
            })
            ->whereIn('status', ['available'])
            ->take(3)
            ->get();

        // User's personal statistics
        $stats = [
            'total_bookings' => Booking::where('requested_by', $user->id)->count(),
            'pending_bookings' => Booking::where('requested_by', $user->id)
                ->where('status', 'pending')
                ->count(),
            'completed_bookings' => Booking::where('requested_by', $user->id)
                ->where('status', 'completed')
                ->count(),
            'active_bookings' => $activeBookings->count(),
            'available_vehicles' => $availableVehicles->count()
        ];

        // Get pending approvals for department head
        $pendingApprovals = 0;
        if ($user->hasRole('department_head')) {
            $pendingApprovals = Booking::where('department_id', $department->id)
                ->where('status', 'pending')
                ->count();
        }

        // Department statistics
        $departmentStats = [
            'total_bookings' => Booking::where('department_id', $department->id)->count(),
            'active_bookings' => $departmentActiveBookings->count(),
            'pending_bookings' => Booking::where('department_id', $department->id)
                ->where('status', 'pending')
                ->count(),
            'completed_bookings' => Booking::where('department_id', $department->id)
                ->where('status', 'completed')
                ->count(),
            'rejected_bookings' => Booking::where('department_id', $department->id)
                ->where('status', 'rejected')
                ->count(),
            'total_feedback' => Feedback::whereHas('booking', function($query) use ($department) {
                $query->where('department_id', $department->id);
            })->count(),
            'pending_approvals' => $pendingApprovals
        ];

        // Get upcoming bookings
        $upcomingBookings = Booking::where('department_id', $department_id)
            ->where('status', 'approved')
            ->where('start_time', '>', $now)
            ->with(['vehicle', 'driver', 'requestedBy'])
            ->orderBy('start_time')
            ->take(5)
            ->get();

        // Get recent feedback based on user role
        if ($user->hasRole('driver')) {
            // For drivers, get feedback for trips they drove
            $recentFeedback = Feedback::whereHas('booking', function($query) use ($user) {
                $query->where('driver_id', $user->id);
            })
            ->with(['booking.vehicle', 'booking.requestedBy'])
            ->latest()
            ->take(5)
            ->get();
        } else {
            // For regular users, get feedback for their bookings
            $recentFeedback = Feedback::whereHas('booking', function($query) use ($user) {
                $query->where('requested_by', $user->id);
            })
            ->with(['booking.vehicle'])
            ->latest()
            ->take(5)
            ->get();
        }

        // Get recent activities for the user and their department
        $recentActivities = Activity::where(function($query) use ($user, $department) {
            $query->where('user_id', $user->id)
                  ->orWhere('department_id', $department->id);
        })
        ->latest()
        ->take(5)
        ->get();

        return view('client.dashboard', compact(
            'recentBookings',
            'activeBookings',
            'departmentActiveBookings',
            'availableVehicles',
            'upcomingBookings',
            'recentFeedback',
            'stats',
            'departmentStats',
            'recentActivities'
        ));
    }
}
