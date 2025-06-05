<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Department;
use App\Models\Activity;
use App\Services\ExitClearanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Notifications\BookingStatusUpdated;
use App\Notifications\BookingAssignedToDriver;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display a listing of bookings.
     */
    public function index(Request $request)
    {
        $query = Booking::with(['vehicle', 'requestedBy', 'driver', 'department'])
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('start_time', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('end_time', '<=', $request->end_date);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('requestedBy', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('vehicle', function($q) use ($search) {
                    $q->where('registration_number', 'like', "%{$search}%");
                });
            });
        }

        $bookings = $query->paginate(3);
        $departments = Department::all();
        $vehicles = Vehicle::where('status', 'available')->get();
        $drivers = User::role('driver')->get();

        return view('admin.bookings.index', compact('bookings', 'departments', 'vehicles', 'drivers'));
    }

    /**
     * Show the form for creating a new booking.
     */
    public function create()
    {
        // Get all departments except the driver department
        $departments = Department::where('name', '!=', 'Driver')->get();

        // Get only available vehicles (not booked, not in maintenance, not out of service)
        $vehicles = Vehicle::with(['type.category', 'brand'])
            ->where('status', 'available')
            ->get();

        // Get only available drivers (not currently assigned to other bookings)
        $drivers = User::role('driver')
            ->where('status', 'active')
            ->where('is_available', true)
            ->whereDoesntHave('driverBookings', function($query) {
                $query->whereIn('status', ['approved', 'in_progress'])
                    ->where('end_time', '>', now());
            })
            ->get();
        
        return view('admin.bookings.create', compact('departments', 'vehicles', 'drivers'));
    }

    /**
     * Store a newly created booking.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'department_id' => 'required|exists:departments,id',
            'driver_id' => 'required|exists:users,id',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'purpose' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'pickup_location' => 'required|string|max:255',
            'number_of_passengers' => 'required|integer|min:1',
            'notes' => 'nullable|string'
        ]);

        // Check if vehicle is available
        $vehicle = Vehicle::findOrFail($data['vehicle_id']);
        if ($vehicle->status !== 'available') {
            return back()->with('error', 'Vehicle is not available for booking.');
        }

        // Check if driver has any active bookings
        $driver = User::findOrFail($data['driver_id']);
        $hasActiveBookings = Booking::where('driver_id', $driver->id)
            ->whereIn('status', ['approved', 'in_progress'])
            ->where('end_time', '>', now())
            ->exists();

        if ($hasActiveBookings) {
            return back()->with('error', 'Driver has other active bookings during this time period.');
        }

        // Check for booking conflicts
        $hasConflict = Booking::where('vehicle_id', $data['vehicle_id'])
            ->where(function($query) use ($data) {
                $query->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                    ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
                    ->orWhere(function($q) use ($data) {
                        $q->where('start_time', '<=', $data['start_time'])
                            ->where('end_time', '>=', $data['end_time']);
                    });
            })
            ->whereIn('status', ['approved', 'in_progress'])
            ->exists();

        if ($hasConflict) {
            return back()->with('error', 'Vehicle is already booked for this time period.');
        }

        // Check for driver booking conflicts
        $hasDriverConflict = Booking::where('driver_id', $data['driver_id'])
            ->where(function($query) use ($data) {
                $query->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                    ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
                    ->orWhere(function($q) use ($data) {
                        $q->where('start_time', '<=', $data['start_time'])
                            ->where('end_time', '>=', $data['end_time']);
                    });
            })
            ->whereIn('status', ['approved', 'in_progress'])
            ->exists();

        if ($hasDriverConflict) {
            return back()->with('error', 'Driver is already assigned to another booking during this time period.');
        }

        DB::beginTransaction();
        try {
            // Create the booking with approved status since it's created by admin
            $booking = Booking::create(array_merge($data, [
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'requested_by' => auth()->id()
            ]));

            // Update vehicle status
            $vehicle->update(['status' => 'booked']);

            // Update driver availability
            $driver->update(['is_available' => false]);

            // Load relationships needed for notification
            $booking->load(['vehicle', 'requestedBy', 'department']);

            // Notify the driver about the new booking assignment
            $driver->notify(new BookingAssignedToDriver($booking));

            DB::commit();
            return redirect()->route('admin.bookings.index')
                ->with('success', 'Booking created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to create booking: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified booking.
     */
    public function show(Booking $booking)
    {
        $booking->load([
            'vehicle.brand', 
            'vehicle.type.category',
            'requestedBy', 
            'driver', 
            'department', 
            'approvedBy'
        ]);
        
        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified booking.
     */
    public function edit(Booking $booking)
    {
        $booking->load(['vehicle.brand', 'vehicle.type.category', 'driver', 'department']);
        $departments = Department::all();

        // Get available vehicles plus the currently assigned vehicle
        $vehicles = Vehicle::with(['type.category', 'brand'])
            ->where(function($query) use ($booking) {
                $query->where('status', 'available')
                    ->orWhere('id', $booking->vehicle_id);
            })
            ->get();

        // Get available drivers plus the currently assigned driver
        $drivers = User::role('driver')
            ->where(function($query) use ($booking) {
                $query->where(function($q) use ($booking) {
                    $q->where('status', 'active')
                        ->where('is_available', true)
                        ->whereDoesntHave('driverBookings', function($subQ) use ($booking) {
                            $subQ->whereIn('status', ['approved', 'in_progress'])
                                ->where('id', '!=', $booking->id)
                                ->where('end_time', '>', now());
                        });
                })
                ->orWhere('id', $booking->driver_id);
            })
            ->get();

        return view('admin.bookings.edit', compact('booking', 'departments', 'vehicles', 'drivers'));
    }

    /**
     * Update the specified booking.
     */
    public function update(Request $request, Booking $booking)
    {
        $rules = [
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected', 'completed', 'cancelled', 'in_progress'])],
            'notes' => ['nullable', 'string'],
            'driver_id' => ['required', 'exists:users,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'purpose' => ['required', 'string'],
            'pickup_location' => ['required', 'string'],
            'destination' => ['required', 'string'],
            'number_of_passengers' => ['required', 'integer', 'min:1']
        ];

        $validated = $request->validate($rules);
        
        // Debug: Log the incoming request and validated data
        \Log::info('Booking Update Request:', [
            'all_request_data' => $request->all(),
            'validated_data' => $validated,
            'current_booking' => $booking->toArray()
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $booking->status;
            $newStatus = $validated['status'];

            // Prepare update data
            $updateData = [
                'status' => $newStatus,
                'notes' => $validated['notes'] ?? $booking->notes,
                'driver_id' => $validated['driver_id'],
                'department_id' => $validated['department_id'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'purpose' => $validated['purpose'],
                'pickup_location' => $validated['pickup_location'],
                'destination' => $validated['destination'],
                'number_of_passengers' => $validated['number_of_passengers'],
                'approved_by' => $newStatus === 'approved' ? auth()->id() : $booking->approved_by,
                'approved_at' => $newStatus === 'approved' ? now() : $booking->approved_at,
            ];

            // Handle vehicle assignment - always use the vehicle_id from the request if provided
            // If no vehicle_id is provided in the request, keep the existing one
            if (isset($validated['vehicle_id']) && !empty($validated['vehicle_id'])) {
                $updateData['vehicle_id'] = $validated['vehicle_id'];
            } else {
                $updateData['vehicle_id'] = $booking->vehicle_id;
            }

            // Debug: Log the data being used for update
            \Log::info('Updating booking with data:', [
                'update_data' => $updateData,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);

            // Update booking
            $result = $booking->update($updateData);
            
            // Debug: Log the result of the update
            \Log::info('Update result:', [
                'success' => $result,
                'updated_booking' => $booking->fresh()->toArray()
            ]);

            // If status changed to approved
            if ($oldStatus !== 'approved' && $newStatus === 'approved') {
                // Update vehicle status
                if ($booking->vehicle) {
                    $booking->vehicle->update(['status' => 'booked']);
                }

                // Update driver availability
                if ($booking->driver) {
                    $booking->driver->update(['is_available' => false]);
                }

                // Notify the driver
                if ($booking->driver) {
                    $booking->driver->notify(new BookingAssignedToDriver($booking));
                }

                // Notify the requester
                $booking->requestedBy->notify(new BookingStatusUpdated($booking, $oldStatus));
            }
            // If status changed to rejected, cancelled, or completed
            elseif ($newStatus === 'rejected' || $newStatus === 'cancelled' || $newStatus === 'completed') {
                // Free up vehicle
                if ($booking->vehicle) {
                    $booking->vehicle->update(['status' => 'available']);
                }

                // Free up driver
                if ($booking->driver) {
                    $booking->driver->update(['is_available' => true]);
                }

                // Notify the requester
                $booking->requestedBy->notify(new BookingStatusUpdated($booking, $oldStatus));
            }

            DB::commit();
            return redirect()->route('admin.bookings.index')
                ->with('success', 'Booking status updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to update booking status: ' . $e->getMessage());
        }
    }

    /**
     * Cancel the specified booking.
     */
    public function cancel(Booking $booking)
    {
        DB::beginTransaction();
        try {
            // Get the vehicle and driver before cancelling
            $vehicle = $booking->vehicle;
            $driver = $booking->driver;

            // Update the booking status
            $booking->update([
                'status' => 'cancelled'
            ]);

            // Create activity log
            Activity::log(
                auth()->user(), 
                $booking, 
                'booking', 
                'cancelled booking #' . $booking->id . ' for ' . $booking->requestedBy->name
            );

            // Update vehicle status if no other active bookings
            if ($vehicle) {
                $hasOtherBookings = $vehicle->bookings()
                    ->whereIn('status', ['approved', 'in_progress'])
                    ->where('id', '!=', $booking->id)
                    ->where('end_time', '>', now())
                    ->exists();

                if (!$hasOtherBookings) {
                    $vehicle->update(['status' => 'available']);
                }
            }

            // Update driver status if no other active bookings
            if ($driver) {
                $hasOtherBookings = Booking::where('driver_id', $driver->id)
                    ->whereIn('status', ['approved', 'in_progress'])
                    ->where('id', '!=', $booking->id)
                    ->where('end_time', '>', now())
                    ->exists();

                if (!$hasOtherBookings) {
                    $driver->update(['status' => 'available']);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Booking cancelled successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Failed to cancel booking: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified booking.
     */
    public function destroy(Booking $booking)
    {
        DB::beginTransaction();
        try {
            // Get the vehicle and driver before deleting
            $vehicle = $booking->vehicle;
            $driver = $booking->driver;

            // Delete the booking
            $booking->delete();

            // Update vehicle status if no other active bookings
            if ($vehicle) {
                $hasOtherBookings = $vehicle->bookings()
                    ->whereIn('status', ['approved', 'in_progress'])
                    ->where('end_time', '>', now())
                    ->exists();

                if (!$hasOtherBookings) {
                    $vehicle->update(['status' => 'available']);
                }
            }

            // Update driver availability if no other active bookings
            if ($driver) {
                $hasOtherBookings = $driver->bookingsAssigned()
                    ->whereIn('status', ['approved', 'in_progress'])
                    ->where('end_time', '>', now())
                    ->exists();

                if (!$hasOtherBookings) {
                    $driver->update(['is_available' => true]);
                }
            }

            DB::commit();
            return redirect()->route('admin.bookings.index')->with('success', 'Booking deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to delete booking: ' . $e->getMessage());
        }
    }

    /**
     * Approve the specified booking.
     */
    public function approve(Booking $booking, ExitClearanceService $exitClearanceService)
    {
        if ($booking->status !== 'pending') {
            return back()->with('error', 'Only pending bookings can be approved.');
        }

        DB::beginTransaction();
        try {
            // Store old status
            $oldStatus = $booking->status;

            // Load all necessary relationships for notifications
            $booking->load([
                'vehicle.brand',
                'vehicle.type.category',
                'requestedBy',
                'driver',
                'department'
            ]);

            // Update booking status
            $booking->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approval_type' => 'admin',
                'approved_at' => now(),
            ]);

            // Update vehicle status
            $booking->vehicle->update(['status' => 'booked']);

            // Update driver availability
            if ($booking->driver) {
                $booking->driver->update(['is_available' => false]);
                $booking->driver->notify(new BookingAssignedToDriver($booking));

                // Generate exit clearance ticket for the driver
                $exitClearanceTicket = $exitClearanceService->generateForBooking($booking, auth()->id());
                
                // You can also notify the driver here if needed
                // $booking->driver->notify(new ExitClearanceGenerated($exitClearanceTicket));
            }

            // Notify the requester
            if ($booking->requestedBy) {
                $booking->requestedBy->notify(new BookingStatusUpdated(
                    $booking,
                    $oldStatus,
                    'approved'
                ));
            }

            DB::commit();
            return back()->with([
                'success' => 'Booking approved successfully.',
                'exit_ticket_generated' => isset($exitClearanceTicket) ? $exitClearanceTicket->ticket_number : null
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to approve booking: ' . $e->getMessage());
        }
    }

    /**
     * Reject a booking request.
     */
    public function reject(Request $request, Booking $booking)
    {
        if ($booking->status !== 'pending') {
            return back()->with('error', 'Only pending bookings can be rejected.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            // Store old status
            $oldStatus = $booking->status;

            // Load all necessary relationships for notifications
            $booking->load([
                'vehicle.brand',
                'vehicle.type.category',
                'requestedBy',
                'driver',
                'department'
            ]);

            $booking->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason
            ]);

            // Make vehicle available if it was reserved
            if ($booking->vehicle) {
                $booking->vehicle->update(['status' => 'available']);
            }

            // Make driver available if they were assigned
            if ($booking->driver) {
                $booking->driver->update(['is_available' => true]);
            }

            // Notify the requester with rejection reason
            if ($booking->requestedBy) {
                $booking->requestedBy->notify(new BookingStatusUpdated(
                    $booking,
                    $oldStatus,
                    'rejected',
                    $request->rejection_reason
                ));
            }

            DB::commit();
            return back()->with('success', 'Booking rejected successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to reject booking: ' . $e->getMessage());
        }
    }
}
