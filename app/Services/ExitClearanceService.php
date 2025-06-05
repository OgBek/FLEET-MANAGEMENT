<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ExitClearanceTicket;
use Illuminate\Support\Str;

class ExitClearanceService
{
    /**
     * Generate an exit clearance ticket for an approved booking.
     *
     * @param Booking $booking The booking to generate the ticket for.
     * @param int $issuedById The ID of the admin who approved the booking.
     * @return ExitClearanceTicket The created exit clearance ticket.
     */
    public function generateForBooking(Booking $booking, int $issuedById): ExitClearanceTicket
    {
        // Ensure the booking is loaded with necessary relationships
        $booking->load(['driver', 'vehicle']);

        // Generate a unique ticket number (e.g., ECT-20250603-XXXX)
        $ticketNumber = 'ECT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));

        // Create and return the exit clearance ticket
        return ExitClearanceTicket::create([
            'booking_id' => $booking->id,
            'ticket_number' => $ticketNumber,
            'driver_id' => $booking->driver_id,
            'vehicle_id' => $booking->vehicle_id,
            'issued_at' => now(),
            'valid_until' => $booking->end_time, // Ticket valid until the booking end time
            'gate_number' => null, // Can be set by security personnel when the vehicle exits
            'issued_by' => $issuedById,
            'remarks' => 'Generated upon booking approval.',
        ]);
    }

    /**
     * Get the printable view for an exit clearance ticket.
     *
     * @param ExitClearanceTicket $ticket The exit clearance ticket.
     * @return \Illuminate\View\View The view instance.
     */
    public function getPrintableTicketView(ExitClearanceTicket $ticket)
    {
        // Load the ticket with all necessary relationships
        $ticket->load([
            'booking',
            'driver',
            'vehicle.type',
            'issuer'
        ]);

        return view('tickets.exit-clearance-print', [
            'ticket' => $ticket
        ]);
    }
}
