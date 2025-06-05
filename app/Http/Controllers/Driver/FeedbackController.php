<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:driver']);
    }

    public function index()
    {
        $user = auth()->user();
        
        // Get all feedback for trips where the user was the driver
        $feedbacks = Feedback::whereHas('booking', function($query) use ($user) {
            $query->where('driver_id', $user->id);
        })
        ->with(['user', 'booking.vehicle', 'booking.requestedBy', 'booking.department'])
        ->latest()
        ->paginate(5);

        return view('driver.feedback.index', compact('feedbacks'));
    }

    public function show(Feedback $feedback)
    {
        // Security check: Make sure the feedback is for a booking assigned to this driver
        $user = auth()->user();
        
        if (!$feedback->booking || $feedback->booking->driver_id !== $user->id) {
            return redirect()->route('driver.feedback.index')
                ->with('error', 'You do not have permission to view this feedback.');
        }

        // Eager load all necessary relationships
        $feedback->load([
            'user',
            'booking.vehicle',
            'booking.requestedBy',
            'booking.department',
            'booking.driver'
        ]);

        return view('driver.feedback.show', compact('feedback'));
    }
}
