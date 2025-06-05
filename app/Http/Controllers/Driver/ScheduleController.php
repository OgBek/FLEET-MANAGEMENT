<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:driver']);
    }

    public function index()
    {
        $user = auth()->user();
        
        // Get all trips assigned to the driver
        $trips = Booking::where('driver_id', $user->id)
            ->with(['vehicle', 'requestedBy'])
            ->latest()
            ->paginate(5);

        // Calculate statistics
        $stats = [
            'total_trips' => Booking::where('driver_id', $user->id)->count(),
            'completed_trips' => Booking::where('driver_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'upcoming_trips' => Booking::where('driver_id', $user->id)
                ->where('status', 'approved')
                ->where('start_time', '>', now())
                ->count(),
            'active_trips' => Booking::where('driver_id', $user->id)
                ->whereIn('status', ['approved', 'pending'])
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->count()
        ];

        return view('driver.trips.index', compact('trips', 'stats'));
    }

    public function show(Booking $booking)
    {
        // Ensure the trip belongs to the authenticated driver
        if ($booking->driver_id !== auth()->id()) {
            abort(403);
        }

        return view('driver.trips.show', compact('booking'));
    }

    public function update(Request $request, Booking $trip)
    {
        // Ensure the trip belongs to the authenticated driver
        if ($trip->driver_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:completed'],
            'actual_distance' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000']
        ]);

        $trip->update($validated);

        return redirect()->route('driver.trips.index')
            ->with('status', 'Trip has been marked as completed.');
    }

    public function schedule(Request $request)
    {
        $user = auth()->user();
        
        // Get the requested month or default to current month
        $monthParam = $request->input('month', now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
        
        // Add debug info to log
        \Log::debug('Driver schedule parameters:', [
            'driver_id' => $user->id,
            'month' => $monthParam,
            'month_start' => $currentMonth->copy()->startOfMonth()->toDateTimeString(),
            'month_end' => $currentMonth->copy()->endOfMonth()->toDateTimeString()
        ]);
        
        // Use DB query to directly check if any trips exist for this driver
        $hasAnyTrips = \DB::table('bookings')
            ->where('driver_id', $user->id)
            ->whereNull('deleted_at')
            ->exists();
            
        \Log::debug('Driver has any trips: ' . ($hasAnyTrips ? 'Yes' : 'No'));
        
        // Get ALL trips for this driver, no status filtering at all
        // This is different from our previous approach - we're including ALL statuses
        $trips = Booking::where('driver_id', $user->id)
            ->with(['vehicle', 'requestedBy', 'department'])
            ->orderBy('start_time')
            ->get();
        
        // Log the trips we found
        \Log::debug('Driver trips found:', [
            'count' => $trips->count(),
            'trip_details' => $trips->map(function($trip) {
                return [
                    'id' => $trip->id,
                    'status' => $trip->status,
                    'start_time' => $trip->start_time ? $trip->start_time->toDateTimeString() : 'null',
                    'end_time' => $trip->end_time ? $trip->end_time->toDateTimeString() : 'null',
                ];
            })->toArray()
        ]);

        // Group trips by date - simplified to just put them in their start date
        // This approach guarantees that every trip will appear at least once
        $groupedTrips = [];
        foreach ($trips as $trip) {
            // Make sure the trip has dates before trying to group
            if ($trip->start_time && $trip->end_time) {
                // Get the start date for this trip
                $dateStr = $trip->start_time->format('Y-m-d');
                
                // Initialize the array for this date if needed
                if (!isset($groupedTrips[$dateStr])) {
                    $groupedTrips[$dateStr] = [];
                }
                
                // Add this trip to the date
                $groupedTrips[$dateStr][] = $trip;
                
                // If it's a multi-day trip, also add it to the end date
                if ($trip->start_time->format('Y-m-d') !== $trip->end_time->format('Y-m-d')) {
                    $endDateStr = $trip->end_time->format('Y-m-d');
                    if (!isset($groupedTrips[$endDateStr])) {
                        $groupedTrips[$endDateStr] = [];
                    }
                    $groupedTrips[$endDateStr][] = $trip;
                }
            }
        }
        
        // Log the grouped trips for debugging
        \Log::debug('Grouped trips:', [
            'dates' => array_keys($groupedTrips),
            'count_by_date' => array_map(function($dateTrips) {
                return count($dateTrips);
            }, $groupedTrips)
        ]);

        return view('driver.schedule', compact('groupedTrips', 'currentMonth'));
    }
} 