<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Traits\NotifiesAdmins;
use App\Notifications\NewBookingCreated;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    use NotifiesAdmins;

    public function __construct()
    {
        $this->middleware('role:department_head|department_staff');
    }

    /**
     * Display a listing of the bookings.
     */
    public function index(Request $request)
    {
        $query = Booking::with(['vehicle', 'driver'])
            ->where('requested_by', Auth::id());

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->whereDate('start_time', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('end_time', '<=', $request->end_date);
        }

        $bookings = $query->orderBy('start_time', 'desc')
            ->paginate(4)
            ->withQueryString();

        // Get booking statistics
        $stats = [
            'total_bookings' => Booking::where('requested_by', Auth::id())->count(),
            'pending_bookings' => Booking::where('requested_by', Auth::id())
                ->where('status', 'pending')
                ->count(),
            'approved_bookings' => Booking::where('requested_by', Auth::id())
                ->where('status', 'approved')
                ->count(),
            'this_month_bookings' => Booking::where('requested_by', Auth::id())
                ->whereMonth('start_time', now()->month)
                ->count(),
        ];

        return view('client.bookings.index', compact('bookings', 'stats'));
    }

    /**
     * Show the form for creating a new booking.
     */
    public function create()
    {
        $userDepartment = Auth::user()->department;
        $isRestrictedDepartment = in_array(strtolower($userDepartment->name), ['leadership', 'admin']);

        // If vehicle ID is provided, get that specific vehicle
        $selectedVehicle = null;
        if (request()->has('vehicle')) {
            $query = Vehicle::with(['brand', 'type', 'type.category'])
                ->whereNotIn('status', ['maintenance', 'out_of_service']);

            $selectedVehicle = $query->where('id', request()->get('vehicle'))->first();

            if (!$selectedVehicle) {
                return redirect()
                    ->route('client.vehicles.index')
                    ->with('error', 'You do not have permission to book this vehicle.');
            }
        }

        // Get only available vehicles (not in maintenance, out of service or currently booked)
        $now = Carbon::now();
        
        // Get vehicles that are not currently booked
        $vehiclesQuery = Vehicle::with(['brand', 'type'])
            ->whereNotIn('status', ['maintenance', 'out_of_service'])
            ->whereDoesntHave('bookings', function($query) use ($now) {
                $query->where('status', 'approved')
                      ->where(function($q) use ($now) {
                          $q->where('start_time', '<=', $now)
                            ->where('end_time', '>=', $now);
                      });
            });

        $vehicles = $vehiclesQuery->get();

        // Get only available drivers
        $drivers = User::role('driver')
            ->where('status', 'active')
            ->where('is_available', true)
            ->whereDoesntHave('driverBookings', function($query) {
                $query->whereIn('status', ['approved', 'in_progress'])
                    ->where('end_time', '>', now());
            })
            ->get();

        return view('client.bookings.create', compact('vehicles', 'drivers', 'selectedVehicle'));
    }

    /**
     * Store a newly created booking.
     */
    public function store(Request $request)
    {
        try {
            // Get system timezone
            $systemTimezone = config('app.timezone');
            
            // Convert input times to system timezone and round to nearest 5 minutes
            $startTime = Carbon::parse($request->start_time)
                ->setTimezone($systemTimezone)
                ->minute(ceil(Carbon::now()->minute / 5) * 5)
                ->second(0);
            
            $endTime = Carbon::parse($request->end_time)
                ->setTimezone($systemTimezone)
                ->minute(ceil(Carbon::now()->minute / 5) * 5)
                ->second(0);
            
            $currentTime = Carbon::now($systemTimezone)
                ->minute(ceil(Carbon::now()->minute / 5) * 5)
                ->second(0);

            // Get the selected vehicle for capacity validation
            $vehicle = Vehicle::findOrFail($request->vehicle_id);

            // Validate the request
            $validated = $request->validate([
                'vehicle_id' => [
                    'required',
                    'exists:vehicles,id',
                    Rule::exists('vehicles', 'id')->where(function ($query) {
                        $query->whereNotIn('status', ['maintenance', 'out_of_service']);
                    }),
                ],
                'driver_id' => [
                    'required',
                    'exists:users,id',
                    function ($attribute, $value, $fail) {
                        $user = User::find($value);
                        if (!$user || $user->status !== 'active' || !$user->hasRole('driver')) {
                            $fail('The selected driver is not available or is not an active driver.');
                        }
                    },
                ],
                'start_time' => [
                    'required',
                    'date',
                    'after_or_equal:' . now()->format('Y-m-d H:i')
                ],
                'end_time' => [
                    'required',
                    'date',
                    'after:start_time'
                ],
                'purpose' => 'required|string|max:500',
                'pickup_location' => 'required|string|max:255',
                'destination' => 'required|string|max:255',
                'number_of_passengers' => [
                    'required',
                    'integer',
                    'min:1',
                    function ($attribute, $value, $fail) use ($vehicle) {
                        if ($value > $vehicle->capacity) {
                            $fail("Number of passengers ({$value}) exceeds vehicle capacity ({$vehicle->capacity}).");
                        }
                    }
                ],
                'notes' => 'nullable|string|max:500'
            ], [
                'vehicle_id.exists' => 'The selected vehicle is not available.',
                'driver_id.exists' => 'The selected driver is not available or is not an active driver.',
                'purpose.required' => 'Please provide the purpose of your trip.',
                'purpose.max' => 'The purpose cannot exceed 500 characters.',
                'destination.required' => 'Please specify your destination.',
                'destination.max' => 'The destination cannot exceed 255 characters.',
                'pickup_location.required' => 'Please specify the pickup location.',
                'pickup_location.max' => 'The pickup location cannot exceed 255 characters.',
                'number_of_passengers.max' => "The number of passengers cannot exceed the vehicle's capacity of ".$vehicle->capacity." persons.",
                'start_time.required' => 'Please select a start time for your booking.',
                'end_time.required' => 'Please select an end time for your booking.',
                'end_time.after' => 'The end time must be after the start time.',
            ]);

            // Check for conflicting bookings
            $conflictingBookings = Booking::where('vehicle_id', $request->vehicle_id)
                ->whereIn('status', ['approved', 'pending'])
                ->where(function($query) use ($startTime, $endTime) {
                    $query->where(function($q) use ($startTime, $endTime) {
                        // New booking starts during an existing booking
                        $q->where('start_time', '<=', $startTime)
                          ->where('end_time', '>', $startTime);
                    })->orWhere(function($q) use ($startTime, $endTime) {
                        // New booking ends during an existing booking
                        $q->where('start_time', '<', $endTime)
                          ->where('end_time', '>=', $endTime);
                    })->orWhere(function($q) use ($startTime, $endTime) {
                        // New booking completely contains an existing booking
                        $q->where('start_time', '>=', $startTime)
                          ->where('end_time', '<=', $endTime);
                    });
                })
                ->get();

            if ($conflictingBookings->isNotEmpty()) {
                // Get next available slots
                $nextAvailableSlots = $this->findNextAvailableSlots($request->vehicle_id, $startTime, 3);
                
                $errorMessage = 'The vehicle is not available for the selected time period. Next available slots:';
                foreach ($nextAvailableSlots as $slot) {
                    $slotStart = Carbon::parse($slot['start'])->setTimezone($systemTimezone);
                    $slotEnd = Carbon::parse($slot['end'])->setTimezone($systemTimezone);
                    $errorMessage .= "\n- " . $slotStart->format('M d, Y H:i') . ' to ' . $slotEnd->format('M d, Y H:i');
                }
                
                return back()
                    ->withInput()
                    ->withErrors(['time_conflict' => $errorMessage]);
            }

            // Check if driver is available
            $driver = User::findOrFail($validated['driver_id']);
            $hasActiveBookings = Booking::where('driver_id', $driver->id)
                ->whereIn('status', ['approved', 'in_progress'])
                ->where('end_time', '>', now())
                ->exists();

            if ($hasActiveBookings) {
                return back()
                    ->withInput()
                    ->withErrors(['driver_id' => 'The selected driver is not available for booking.']);
            }

            // Check if driver has overlapping bookings
            // Check for driver availability
            $driverAvailable = $this->checkDriverAvailability($validated['driver_id'], $startTime, $endTime);
            if (!$driverAvailable) {
                return back()
                    ->withInput()
                    ->withErrors(['driver_id' => 'The selected driver has overlapping bookings during this time period.']);
            }
            
            // Validate booking duration
            $durationValidation = $this->validateBookingDuration($startTime, $endTime);
            if (!$durationValidation['valid']) {
                return back()
                    ->withInput()
                    ->withErrors(['end_time' => $durationValidation['message']]);
            }

            // Get user and check department information thoroughly
            $user = Auth::user();
            $departmentId = $user->department_id;
            
            // First check if department_id is set in the user record
            if (!$departmentId) {
                return back()
                    ->withInput()
                    ->withErrors(['error' => 'Your account is not associated with a department. Please contact an administrator.']);
            }
            
            // Then verify the department actually exists in the database
            $department = \App\Models\Department::find($departmentId);
            if (!$department) {
                return back()
                    ->withInput()
                    ->withErrors(['error' => 'The department associated with your account does not exist. Please contact an administrator.']);
            }
            
            // Make sure the department is not the driver department
            if (strtolower($department->name) === 'driver' || strtolower($department->name) === 'driver') {
                return back()
                    ->withInput()
                    ->withErrors(['error' => 'Drivers cannot create bookings. Please contact your department head or an administrator to create a booking.']);
            }
            
            // Check if this is a weekend booking
            $isWeekendBooking = $startTime->isWeekend() || $endTime->isWeekend();
            
            // Check if user is from admin department
            $user = Auth::user();
            $isAdminDepartment = $user->department && 
                (strtolower($user->department->name) === 'administration' || 
                 strtolower($user->department->name) === 'admin');
                 
            // Determine booking status and notes
            $bookingStatus = 'pending';
            
            // If the user is a department head, mark it for admin approval only
            $needsAdminApprovalOnly = $user->hasRole('department_head');
            
            $bookingNotes = $validated['notes'] ?? '';
            
            // For non-admin users with weekend bookings, add a special note
            if ($isWeekendBooking && !$isAdminDepartment) {
                $bookingNotes = ($bookingNotes ? $bookingNotes . '\n\n' : '') . 
                    '[SYSTEM NOTE: This booking includes weekend dates and requires special approval.]';
            }
            
            // For department head bookings, add a note that they only need admin approval
            if ($needsAdminApprovalOnly) {
                $bookingNotes = ($bookingNotes ? $bookingNotes . '\n\n' : '') . 
                    '[SYSTEM NOTE: This booking was created by a department head and only requires admin approval.]';
            }
            
            // Create the booking
            $booking = Booking::create([
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['driver_id'],
                'requested_by' => Auth::id(),
                'department_id' => Auth::user()->department_id,
                'purpose' => $validated['purpose'],
                'destination' => $validated['destination'],
                'pickup_location' => $validated['pickup_location'],
                'number_of_passengers' => $validated['number_of_passengers'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'notes' => $bookingNotes,
                'status' => $bookingStatus,
            ]);

            // Notify admins and department head about the new booking
            $this->notifyAdmins(new NewBookingCreated($booking), $booking);
            
            // Prepare success message based on booking type
            $successMessage = 'Booking request submitted successfully.';
            
            if ($isWeekendBooking && !$isAdminDepartment) {
                $successMessage .= ' Since your booking includes weekend dates, it will require special approval from an administrator.';
            } else {
                $successMessage .= ' Please wait for approval.';
            }
            
            return redirect()
                ->route('client.bookings.index')
                ->with('success', $successMessage);
                
        } catch (\Exception $e) {
            // Log the full exception for debugging
            Log::error('Booking creation error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Create a user-friendly error message
            $errorMessage = 'An error occurred while creating your booking: ' . $e->getMessage() . ' ';
            
            // Check for specific error types
            if (strpos($e->getMessage(), 'vehicle_id') !== false) {
                $errorMessage = 'The selected vehicle is no longer available. Please select a different vehicle.';
            } else if (strpos($e->getMessage(), 'driver_id') !== false) {
                $errorMessage = 'The selected driver is no longer available. Please select a different driver.';
            } else if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                $errorMessage = 'There was a problem with one of your selections. Please try different options.';
            } else if (strpos($e->getMessage(), 'overlapping') !== false) {
                $errorMessage = 'There is a scheduling conflict. Please select a different time slot.';
            }
            
            // Dump information about the user for debugging
            if (config('app.debug') === true) {
                $userData = [
                    'id' => Auth::id(),
                    'name' => Auth::user()->name,
                    'department_id' => Auth::user()->department_id,
                    'department_name' => Auth::user()->department ? Auth::user()->department->name : 'None',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ];
                
                $errorMessage .= '\n\nUser Data: ' . json_encode($userData);
            }
            
            return back()
                ->withInput()
                ->withErrors(['error' => $errorMessage]);
        }
    }

    /**
     * Display the specified booking.
     */
    public function show(Booking $booking)
    {
        // Ensure user can only view their own bookings
        if ($booking->requested_by !== Auth::id()) {
            return abort(403);
        }

        $booking->load(['vehicle', 'driver', 'requestedBy']);

        return view('client.bookings.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified booking.
     */
    public function edit(Booking $booking)
    {
        // Ensure user can only edit their own pending bookings
        if ($booking->requested_by !== Auth::id() || $booking->status !== 'pending') {
            return abort(403);
        }

        // Get all available vehicles
        $vehicles = Vehicle::where('status', 'available')
            ->orWhere('id', $booking->vehicle_id)
            ->get();

        // Get all drivers
        $drivers = User::role('driver')->get();

        return view('client.bookings.edit', compact('booking', 'vehicles', 'drivers'));
    }

    /**
     * Update the specified booking.
     */
    public function update(Request $request, Booking $booking)
    {
        try {
            // Debug incoming request data
            \Log::debug('Booking update request:', [
                'booking_id' => $booking->id,
                'request_data' => $request->all()
            ]);
            
            // Get system timezone
            $systemTimezone = config('app.timezone');
            
            // Parse dates without rounding minutes
            $startTime = Carbon::parse($request->start_time);
            $endTime = Carbon::parse($request->end_time);
            $currentTime = Carbon::now();

            $request->validate([
                'vehicle_id' => 'required|exists:vehicles,id',
                'driver_id' => [
                    'required',
                    'exists:users,id',
                    function ($attribute, $value, $fail) {
                        $user = User::find($value);
                        if (!$user || $user->status !== 'active' || !$user->hasRole('driver')) {
                            $fail('The selected driver is not available or is not an active driver.');
                        }
                    },
                ],
                'purpose' => 'required|string',
                'destination' => 'required|string',
                'pickup_location' => 'required|string',
                'number_of_passengers' => [
                    'required',
                    'integer',
                    'min:1',
                    function ($attribute, $value, $fail) use ($request) {
                        $vehicle = Vehicle::find($request->vehicle_id);
                        if ($value > $vehicle->capacity) {
                            $fail("Number of passengers ({$value}) exceeds vehicle capacity ({$vehicle->capacity}).");
                        }
                    }
                ],
                'start_time' => [
                    'required',
                    'date'
                ],
                'end_time' => [
                    'required',
                    'date',
                    'after:start_time'
                ],
            ]);

            // Get the vehicle
            $vehicle = Vehicle::findOrFail($request->vehicle_id);

            // Log the update attempt with timezone information
            \Log::info('Attempting to update booking:', [
                'booking_id' => $booking->id,
                'current_vehicle_id' => $booking->vehicle_id,
                'new_vehicle_id' => $request->vehicle_id,
                'current_start_time' => $booking->start_time,
                'new_start_time' => $startTime,
                'current_end_time' => $booking->end_time,
                'new_end_time' => $endTime,
                'client_timezone' => $systemTimezone
            ]);

            // Check for conflicting bookings, excluding the current booking
            $conflictingBookings = Booking::where('vehicle_id', $vehicle->id)
                ->where('id', '!=', $booking->id)
                ->whereIn('status', ['approved', 'pending'])
                ->where(function($query) use ($startTime, $endTime) {
                    $query->where(function($q) use ($startTime, $endTime) {
                        // New booking starts during an existing booking
                        $q->where('start_time', '<=', $startTime)
                          ->where('end_time', '>', $startTime);
                    })->orWhere(function($q) use ($startTime, $endTime) {
                        // New booking ends during an existing booking
                        $q->where('start_time', '<', $endTime)
                          ->where('end_time', '>=', $endTime);
                    })->orWhere(function($q) use ($startTime, $endTime) {
                        // New booking completely contains an existing booking
                        $q->where('start_time', '>=', $startTime)
                          ->where('end_time', '<=', $endTime);
                    });
                })
                ->get();

            if ($conflictingBookings->isNotEmpty()) {
                \Log::info('Conflicting bookings found:', [
                    'conflicts' => $conflictingBookings->map(function($b) use ($request) {
                        $timezone = $systemTimezone;
                        return [
                            'booking_id' => $b->id,
                            'start_time' => $b->start_time->setTimezone($timezone)->format('Y-m-d H:i:s'),
                            'end_time' => $b->end_time->setTimezone($timezone)->format('Y-m-d H:i:s')
                        ];
                    })
                ]);

                // Get next available slots in client's timezone
                $nextAvailableSlots = $this->findNextAvailableSlots($vehicle->id, $startTime, 3);
                
                $errorMessage = 'The selected time period conflicts with other bookings. Available slots:';
                foreach ($nextAvailableSlots as $slot) {
                    $slotStart = Carbon::parse($slot['start'])->setTimezone($systemTimezone);
                    $slotEnd = Carbon::parse($slot['end'])->setTimezone($systemTimezone);
                    $errorMessage .= "\n- " . $slotStart->format('M d, Y H:i') . ' to ' . $slotEnd->format('M d, Y H:i');
                }

                return back()
                    ->withInput()
                    ->with('error', $errorMessage);
            }

            // Validate booking duration
            $durationValidation = $this->validateBookingDuration($startTime, $endTime);
            if (!$durationValidation['valid']) {
                return back()
                    ->withInput()
                    ->withErrors(['end_time' => $durationValidation['message']]);
            }

            // Add DB transaction for safety
            DB::beginTransaction();
            
            try {
                // Update the booking with parsed times
                $booking->update([
                    'vehicle_id' => $request->vehicle_id,
                    'driver_id' => $request->driver_id,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'purpose' => $request->purpose,
                    'destination' => $request->destination,
                    'pickup_location' => $request->pickup_location,
                    'number_of_passengers' => $request->number_of_passengers,
                    // Use the proper status constant from the Booking model
                    'status' => \App\Models\Booking::STATUS_PENDING,
                ]);
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Failed to update booking record: ' . $e->getMessage());
                return back()->withInput()->with('error', 'Database error: ' . $e->getMessage());
            }

            // Update vehicle status
            $vehicle->updateBookingStatus();

            return redirect()
                ->route('client.bookings.show', $booking)
                ->with('success', 'Booking updated successfully.');

        } catch (\Exception $e) {
            Log::error('Booking update error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }
    }

    /**
     * Cancel the specified booking.
     */
    public function cancel(Booking $booking)
    {
        // Ensure user can only cancel their own bookings that are pending or approved
        if ($booking->requested_by !== Auth::id() || 
            !in_array($booking->status, ['pending', 'approved'])) {
            return abort(403);
        }

        // Don't allow cancellation of past bookings
        if ($booking->start_time->isPast()) {
            return back()->with('error', 'Cannot cancel past bookings.');
        }

        DB::beginTransaction();
        try {
            // Get the vehicle and driver before cancelling
            $vehicle = $booking->vehicle;
            $driver = $booking->driver;

            // Update the booking status
            $booking->update([
                'status' => 'cancelled'
            ]);

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
                $hasActiveBookings = Booking::where('driver_id', $driver->id)
                    ->whereIn('status', ['approved', 'in_progress'])
                    ->where('end_time', '>', now())
                    ->exists();

                if (!$hasActiveBookings) {
                    $driver->update(['is_available' => true]);
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
        // Ensure user can only delete their own bookings
        if ($booking->requested_by !== Auth::id()) {
            return abort(403);
        }

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

            // Update driver status if no other active bookings
            if ($driver) {
                $hasActiveBookings = Booking::where('driver_id', $driver->id)
                    ->whereIn('status', ['approved', 'in_progress'])
                    ->where('end_time', '>', now())
                    ->exists();

                if (!$hasActiveBookings) {
                    $driver->update(['is_available' => true]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Booking deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Failed to delete booking: ' . $e->getMessage());
        }
    }

    /**
     * Check if a vehicle is available for booking in a given time period
     */
    public function checkAvailability($vehicleId, Request $request)
    {
        // Validate inputs
        $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $vehicle = Vehicle::findOrFail($vehicleId);
        $startTime = Carbon::parse($request->start_time);
        $endTime = Carbon::parse($request->end_time);

        // Check if vehicle is already booked for the requested time period
        $overlappingBookings = Booking::where('vehicle_id', $vehicleId)
            ->where('status', 'approved')
            ->where(function($query) use ($startTime, $endTime) {
                // Check for any overlap with the requested time period
                $query->where(function($q) use ($startTime, $endTime) {
                    // Booking starts during requested period
                    $q->where('start_time', '>=', $startTime)
                      ->where('start_time', '<', $endTime);
                })->orWhere(function($q) use ($startTime, $endTime) {
                    // Booking ends during requested period
                    $q->where('end_time', '>', $startTime)
                      ->where('end_time', '<=', $endTime);
                })->orWhere(function($q) use ($startTime, $endTime) {
                    // Booking spans entire requested period
                    $q->where('start_time', '<=', $startTime)
                      ->where('end_time', '>=', $endTime);
                });
            })
            ->count();

        $available = $overlappingBookings === 0;

        return response()->json([
            'available' => $available,
            'vehicle_id' => $vehicleId,
            'vehicle_name' => $vehicle->registration_number . ' - ' . $vehicle->model
        ]);
    }

    /**
     * Find the next available time slots for a vehicle
     */
    private function findNextAvailableSlots($vehicleId, $startTime, $limit = 3)
    {
        $existingBookings = Booking::where('vehicle_id', $vehicleId)
            ->whereIn('status', ['approved', 'pending'])
            ->where('end_time', '>', $startTime)
            ->orderBy('start_time')
            ->get();

        if ($existingBookings->isEmpty()) {
            return [['start' => $startTime, 'end' => $startTime->copy()->addHours(4)]];
        }

        $availableSlots = [];
        $currentTime = $startTime->copy();

        foreach ($existingBookings as $booking) {
            if ($currentTime->lt($booking->start_time)) {
                $availableSlots[] = [
                    'start' => $currentTime->copy(),
                    'end' => $booking->start_time->copy()
                ];
            }
            $currentTime = $booking->end_time->copy();

            if (count($availableSlots) >= $limit) {
                break;
            }
        }

        // Add one more slot after the last booking if we haven't reached the limit
        if (count($availableSlots) < $limit) {
            $availableSlots[] = [
                'start' => $currentTime->copy(),
                'end' => $currentTime->copy()->addHours(4)
            ];
        }

        return $availableSlots;
    }

    /**
     * Check if a driver is available for the specified time period
     */
    private function checkDriverAvailability($driverId, $startTime, $endTime)
    {
        // Check if driver has overlapping bookings
        $conflictingBookings = Booking::where('driver_id', $driverId)
            ->whereIn('status', ['approved', 'pending'])
            ->where(function($query) use ($startTime, $endTime) {
                $query->where(function($q) use ($startTime, $endTime) {
                    // New booking starts during an existing booking
                    $q->where('start_time', '<=', $startTime)
                      ->where('end_time', '>', $startTime);
                })->orWhere(function($q) use ($startTime, $endTime) {
                    // New booking ends during an existing booking
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>=', $endTime);
                })->orWhere(function($q) use ($startTime, $endTime) {
                    // New booking completely contains an existing booking
                    $q->where('start_time', '>=', $startTime)
                      ->where('end_time', '<=', $endTime);
                });
            })
            ->exists();
            
        return !$conflictingBookings;
    }

    /**
     * Validate booking duration (max 3 days for regular users, 5 days for admin department)
     * Also enforces a minimum 30-minute duration for same-day bookings
     */
    private function validateBookingDuration($startTime, $endTime)
    {
        // Calculate various duration metrics
        $durationMinutes = $endTime->diffInMinutes($startTime);
        $durationHours = $endTime->diffInHours($startTime);
        $durationDays = $endTime->diffInDays($startTime);
        
        // Log for debugging
        \Log::debug('Validating booking duration:', [
            'minutes' => $durationMinutes,
            'hours' => $durationHours,
            'days' => $durationDays
        ]);
        
        // Check if user is from admin department
        $user = Auth::user();
        $isAdminDepartment = $user->department && 
            (strtolower($user->department->name) === 'administration' || 
             strtolower($user->department->name) === 'admin');
        
        // Check if booking starts and ends on different days
        $differentDays = $startTime->format('Y-m-d') !== $endTime->format('Y-m-d');
        
        // For same-day bookings, ensure a minimum 30 minute duration
        // Skip this check for bookings that span different calendar days
        if (!$differentDays && $durationMinutes < 30) {
            return ['valid' => false, 'message' => 'Booking duration must be at least 30 minutes for same-day bookings.'];
        }
        
        // Set maximum duration based on department
        $maxHours = $isAdminDepartment ? 120 : 72; // 5 days for admin, 3 days for others
        
        if ($durationHours > $maxHours) {
            return ['valid' => false, 'message' => 'Booking duration cannot exceed ' . ($isAdminDepartment ? '5 days' : '3 days') . '.'];
        }
        
        return ['valid' => true];
    }
} 