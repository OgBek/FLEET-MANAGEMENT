<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:driver|department_head|department_staff']);
    }

    public function start(Booking $booking)
    {
        // Check if user is authorized to start this trip
        if (Auth::user()->hasRole('driver') && $booking->driver_id !== Auth::id()) {
            abort(403, 'You are not authorized to start this trip.');
        }

        if (!$booking->canBeStarted()) {
            return back()->with('error', 'This booking cannot be started at this time.');
        }

        try {
            $booking->startTrip();

            // Update vehicle status to 'in_use'
            $booking->vehicle->update(['status' => 'in_use']);

            // Update driver status to 'on_trip'
            $booking->driver->update(['is_available' => false]);

            return back()->with('success', 'Trip has been started successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to start the trip. Please try again.');
        }
    }

    public function complete(Booking $booking)
    {
        // Check if user is authorized to complete this trip
        if (Auth::user()->hasRole('driver') && $booking->driver_id !== Auth::id()) {
            abort(403, 'You are not authorized to complete this trip.');
        }

        if (!$booking->canBeCompleted()) {
            return back()->with('error', 'This booking cannot be completed at this time.');
        }

        try {
            $booking->completeTrip();

            // Update vehicle status back to 'available'
            $booking->vehicle->update(['status' => 'available']);

            // Update driver status back to 'available'
            $booking->driver->update(['is_available' => true]);

            return back()->with('success', 'Trip has been completed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to complete the trip. Please try again.');
        }
    }
}