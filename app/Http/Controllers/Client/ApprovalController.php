<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\User;
use App\Models\Activity;
use App\Notifications\BookingAssignedToDriver;
use App\Services\ExitClearanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ApprovalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:department_head']);
    }

    /**
     * Display a listing of pending approvals.
     */
    public function index(Request $request)
    {
        $query = Booking::with(['vehicle.type.category', 'requestedBy', 'driver'])
            ->where('department_id', Auth::user()->department_id)
            ->where('status', 'pending')
            ->where('requested_by', '!=', Auth::id()); // Exclude department head's own requests

        // Filter by date range if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('start_time', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        // Filter by vehicle if provided
        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        // Filter by requester if provided
        if ($request->has('requester_id')) {
            $query->where('requested_by', $request->requester_id);
        }

        $bookings = $query->orderBy('start_time')
            ->paginate(5)
            ->withQueryString();

        // Get department vehicles for filtering
        $vehicles = Auth::user()->department->getAvailableVehicles();

        // Get department staff for filtering (excluding the department head)
        $departmentStaff = User::where('department_id', Auth::user()->department_id)
            ->where('id', '!=', Auth::id())
            ->whereHas('roles', function($query) {
                $query->where('name', 'department_staff');
            })
            ->get();

        return view('client.approvals.index', compact(
            'bookings',
            'vehicles',
            'departmentStaff'
        ));
    }

    /**
     * Approve a booking request.
     */
    public function approve(Request $request, Booking $booking)
    {
        // Check if booking belongs to the department head's department
        if ($booking->department_id !== Auth::user()->department_id) {
            return back()->with('error', 'You can only approve bookings for your department.');
        }

        // Check if the requester belongs to the same department
        if ($booking->requestedBy->department_id !== Auth::user()->department_id) {
            return back()->with('error', 'You can only approve bookings from staff in your department.');
        }

        // Check if booking is still pending
        if ($booking->status !== 'pending') {
            return back()->with('error', 'This booking can no longer be approved.');
        }

        // Check if vehicle is still available for the requested time slot
        if (!$booking->vehicle->isAvailableForBooking($booking->start_time, $booking->end_time)) {
            return back()->with('error', 'The vehicle is no longer available for the requested time slot.');
        }

        DB::beginTransaction();
        try {
            // Store the old status before updating
            $oldStatus = $booking->status;

            // Update booking status
            $booking->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approval_type' => 'department_head',
                'approved_at' => now()
            ]);

            // Update vehicle status
            $booking->vehicle->update(['status' => 'booked']);

            // Generate exit clearance ticket if a driver is assigned
            $exitClearanceTicket = null;
            if ($booking->driver) {
                $exitClearanceService = app(\App\Services\ExitClearanceService::class);
                $exitClearanceTicket = $exitClearanceService->generateForBooking($booking, Auth::id());
                
                // Notify the driver
                $booking->driver->notify(new \App\Notifications\BookingAssignedToDriver($booking));
            }

            // Log notification dispatch attempt
            \Log::info('Attempting to notify user about booking approval', [
                'booking_id' => $booking->id, 
                'user_id' => $booking->requestedBy->id
            ]);

            // Notify the requester
            $booking->requestedBy->notify(new \App\Notifications\BookingStatusUpdated(
                $booking,
                $oldStatus,
                'approved',
                null,
                isset($exitClearanceTicket) ? $exitClearanceTicket->ticket_number : null
            ));

            DB::commit();
            return redirect()->route('client.approvals.index')
                ->with('success', 'Booking approved successfully.')
                ->with('exit_ticket_generated', isset($exitClearanceTicket) ? $exitClearanceTicket->ticket_number : null);
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
        // Validate the request
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:255']
        ]);

        // Check if booking belongs to the department head's department
        if ($booking->department_id !== Auth::user()->department_id) {
            return back()->with('error', 'You can only reject bookings for your department.');
        }

        // Check if the requester belongs to the same department
        if ($booking->requestedBy->department_id !== Auth::user()->department_id) {
            return back()->with('error', 'You can only reject bookings from staff in your department.');
        }

        // Check if booking is still pending
        if ($booking->status !== 'pending') {
            return back()->with('error', 'This booking can no longer be rejected.');
        }

        // Store the old status before updating
        $oldStatus = $booking->status;

        // Update booking status
        $booking->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        // Notify the requester
        $booking->requestedBy->notify(new \App\Notifications\BookingStatusUpdated(
            $booking,
            $oldStatus,
            'rejected',
            $request->rejection_reason
        ));

        return redirect()->route('client.approvals.index')
            ->with('status', 'Booking request rejected successfully.');
    }
} 