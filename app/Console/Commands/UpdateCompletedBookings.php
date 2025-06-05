<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Driver;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UpdateCompletedBookings extends Command
{
    protected $signature = 'bookings:update-completed';
    protected $description = 'Update bookings that have passed their end time to completed status';

    public function handle()
    {
        $now = Carbon::now();
        
        // Find all bookings that have passed their end time and are still active
        $completedBookings = Booking::where('end_time', '<=', $now)
            ->whereIn('status', ['active', 'pending'])
            ->with(['vehicle', 'driver'])
            ->get();

        foreach ($completedBookings as $booking) {
            // Update booking status
            $booking->update(['status' => 'completed']);

            // Make vehicle available
            if ($booking->vehicle) {
                $booking->vehicle->update(['status' => 'available']);
            }

            // Make driver available
            if ($booking->driver) {
                $booking->driver->update(['status' => 'available']);
            }

            $this->info("Updated booking #{$booking->id} to completed status");
        }

        $this->info("Successfully processed " . $completedBookings->count() . " completed bookings");
    }
}
