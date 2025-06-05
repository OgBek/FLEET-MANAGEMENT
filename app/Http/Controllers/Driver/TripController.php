<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TripController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:driver']);
    }

    /**
     * Display a listing of the driver's trips.
     */
    public function index(Request $request)
    {
        $query = Booking::with(['vehicle.type', 'vehicle.brand', 'requestedBy.department', 'department'])
            ->where('driver_id', Auth::id());

        // Get status filter
        $status = $request->get('status', 'all');

        // Apply status filter
        if ($status === 'in_progress') {
            $query->where('status', 'in_progress');
        } elseif ($status === 'approved') {
            $query->where('status', 'approved');
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        // Get trips
        $trips = $query->latest()->paginate(4);

        // Calculate statistics
        $statistics = [
            'total_trips' => Booking::where('driver_id', Auth::id())->count(),
            'active_trips' => Booking::where('driver_id', Auth::id())
                ->where('status', 'in_progress')
                ->count(),
            'upcoming_trips' => Booking::where('driver_id', Auth::id())
                ->where('status', 'approved')
                ->count(),
            'monthly_trips' => Booking::where('driver_id', Auth::id())
                ->whereMonth('created_at', now()->month)
                ->count()
        ];

        return view('driver.trips.index', compact('trips', 'statistics', 'status'));
    }

    /**
     * Display the specified trip.
     */
    public function show(Booking $trip)
    {
        // Check if the trip is assigned to the authenticated driver
        if ($trip->driver_id !== Auth::id()) {
            return back()->with('error', 'You are not authorized to view this trip.');
        }

        $trip->load(['vehicle.type', 'requestedBy', 'department']);

        return view('driver.trips.show', compact('trip'));
    }

    /**
     * Start a trip.
     */
    public function start(Request $request, Booking $booking)
    {
        // Check if the trip is assigned to the authenticated driver
        if ($booking->driver_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'error' => 'You are not authorized to start this trip.',
                'error_code' => 'unauthorized'
            ], 403);
        }

        // Check if trip can be started - removed time validation
        if ($booking->status !== 'approved') {
            return response()->json([
                'success' => false, 
                'error' => 'This trip cannot be started because it is not approved.',
                'error_code' => 'invalid_status'
            ], 400);
        }

        try {
            // Start the trip with direct database update
            \DB::statement("UPDATE bookings SET status = 'in_progress', actual_start_time = NOW(), updated_at = NOW() WHERE id = ?", [$booking->id]);
            
            // Refresh model from database
            $booking = $booking->fresh();

            // Update vehicle status
            $vehicle = $booking->vehicle;
            if ($vehicle) {
                \DB::statement("UPDATE vehicles SET status = 'booked', updated_at = NOW() WHERE id = ?", [$vehicle->id]);
            }

            // Update driver status
            $driver = $booking->driver;
            if ($driver) {
                \DB::statement("UPDATE users SET is_available = 0, updated_at = NOW() WHERE id = ?", [$driver->id]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Trip started successfully.',
                'booking' => $booking->fresh()->load('vehicle', 'driver'),
                'start_time' => now()->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to start trip:', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'An error occurred while starting the trip: ' . $e->getMessage(),
                'error_code' => 'system_error'
            ], 500);
        }
    }

    /**
     * Complete a trip.
     */
    public function complete(Request $request, Booking $booking)
    {
        // Check if the trip is assigned to the authenticated driver
        if ($booking->driver_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'error' => 'You are not authorized to complete this trip.',
                'error_code' => 'unauthorized'
            ], 403);
        }

        if ($booking->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'error' => 'Only in-progress trips can be completed.',
                'error_code' => 'invalid_status'
            ], 400);
        }

        try {
            \Log::info('Attempting to complete trip:', [
                'booking_id' => $booking->id,
                'driver_id' => Auth::id(),
                'vehicle_id' => $booking->vehicle_id ?? 'null'
            ]);
            
            // Complete the trip with direct database update
            \DB::statement("UPDATE bookings SET status = 'completed', actual_end_time = NOW(), completed_at = NOW(), updated_at = NOW() WHERE id = ?", [$booking->id]);
            
            // Refresh model from database
            $booking = $booking->fresh();

            // Update vehicle status to available
            $vehicle = $booking->vehicle;
            if ($vehicle) {
                \Log::info('Updating vehicle status to available', ['vehicle_id' => $vehicle->id]);
                \DB::statement("UPDATE vehicles SET status = 'available', updated_at = NOW() WHERE id = ?", [$vehicle->id]);
            } else {
                \Log::warning('No vehicle found for booking', ['booking_id' => $booking->id]);
            }

            // Update driver status to available
            $driver = $booking->driver;
            if ($driver) {
                \Log::info('Updating driver status to available', ['driver_id' => $driver->id]);
                \DB::statement("UPDATE users SET is_available = 1, updated_at = NOW() WHERE id = ?", [$driver->id]);
            } else {
                \Log::warning('No driver found for booking', ['booking_id' => $booking->id]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Trip completed successfully.',
                'booking' => $booking->fresh()->load('vehicle', 'driver')
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to complete trip:', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'An error occurred while completing the trip: ' . $e->getMessage(),
                'error_code' => 'system_error'
            ], 500);
        }
    }

    /**
     * Update trip status or details.
     */
    public function update(Request $request, Booking $trip)
    {
        // Check if the trip is assigned to the authenticated driver
        if ($trip->driver_id !== Auth::id()) {
            return back()->with('error', 'You are not authorized to update this trip.');
        }

        // Validate update details
        $request->validate([
            'current_location' => ['nullable', 'string', 'max:255'],
            'delay_reason' => ['nullable', 'string', 'max:255'],
            'estimated_arrival' => ['nullable', 'date']
        ]);

        // Update trip details
        $trip->update([
            'current_location' => $request->current_location,
            'delay_reason' => $request->delay_reason,
            'estimated_arrival' => $request->estimated_arrival
        ]);

        // Notify relevant parties if there's a delay
        if ($request->filled('delay_reason')) {
            // TODO: Implement notification logic
        }

        return redirect()->route('driver.trips.show', $trip)
            ->with('status', 'Trip details updated successfully.');
    }
}