<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ExitClearanceTicket;
use App\Services\ExitClearanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Display the specified exit clearance ticket.
     *
     * @param  string  $ticketNumber
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showExitClearanceTicket($ticketNumber)
    {
        $ticket = ExitClearanceTicket::with(['booking', 'driver', 'vehicle', 'issuer'])
            ->where('ticket_number', $ticketNumber)
            ->firstOrFail();

        // Ensure the current user is the driver assigned to this ticket
        if (Auth::id() !== $ticket->driver_id && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $exitClearanceService = app(ExitClearanceService::class);
        return $exitClearanceService->getPrintableTicketView($ticket);
    }

    /**
     * Display a listing of the driver's exit clearance tickets.
     *
     * @return \Illuminate\View\View
     */
    public function exitClearanceTickets()
    {
        $tickets = ExitClearanceTicket::with(['vehicle', 'booking'])
            ->where('driver_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('driver.tickets.exit-clearance-index', [
            'tickets' => $tickets
        ]);
    }
}