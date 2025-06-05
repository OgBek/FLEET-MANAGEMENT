<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:department_head|department_staff']);
    }

    public function index()
    {
        $user = Auth::user();
        
        if ($user->hasRole('department_head')) {
            // Department heads see feedback from their department AND their own feedback
            $feedbacks = Feedback::with(['booking', 'user'])
                ->where(function($query) use ($user) {
                    $query->whereHas('booking', function($q) use ($user) {
                        $q->where('department_id', $user->department_id);
                    })
                    ->orWhere('user_id', $user->id);
                })
                ->latest()
                ->paginate(5);
        } else {
            // Regular staff only see their own feedback
            $feedbacks = Feedback::with(['booking', 'user'])
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(5);
        }
        
        return view('client.feedback.index', compact('feedbacks'));
    }

    public function create()
    {
        $bookings = Booking::where('requested_by', Auth::id())
            ->whereNotIn('id', Feedback::where('user_id', Auth::id())->pluck('booking_id'))
            ->get();

        return view('client.feedback.create', compact('bookings'));
    }

    public function store(Request $request)
    {
        // Custom validation rules based on feedback type
        $rules = [
            'type' => 'required|in:booking,service,general',
            'content' => 'required|string|min:10',
            'rating' => 'required|integer|min:1|max:5',
            'is_public' => 'boolean'
        ];
        
        // Make booking_id required if feedback type is 'booking'
        if ($request->input('type') === 'booking') {
            $rules['booking_id'] = 'required|exists:bookings,id';
        } else {
            $rules['booking_id'] = 'nullable|exists:bookings,id';
        }
        
        $validated = $request->validate($rules, [
            'booking_id.required' => 'Please select a booking when providing feedback on a booking experience.'
        ]);

        $feedback = new Feedback($validated);
        $feedback->user_id = Auth::id();
        $feedback->is_approved = false;
        $feedback->save();

        // Redirect to the first page of the index to ensure the new feedback is visible
        return redirect()->route('client.feedback.index', ['page' => 1])
            ->with('success', 'Thank you for your feedback!');
    }

    public function edit(Feedback $feedback)
    {
        // Eager load the booking relationship to ensure department_id is available for policy checks
        $feedback->load('booking');
        
        $this->authorize('update', $feedback);
        $bookings = Booking::where('requested_by', Auth::id())->get();
        
        return view('client.feedback.edit', compact('feedback', 'bookings'));
    }

    public function update(Request $request, Feedback $feedback)
    {
        // Eager load the booking relationship to ensure department_id is available for policy checks
        $feedback->load('booking');
        
        $this->authorize('update', $feedback);

        $validated = $request->validate([
            'booking_id' => 'nullable|exists:bookings,id',
            'type' => 'required|in:booking,service,general',
            'content' => 'required|string|min:10',
            'rating' => 'required|integer|min:1|max:5',
            'is_public' => 'boolean'
        ]);

        $feedback->update($validated);

        return redirect()->route('client.feedback.index')
            ->with('success', 'Feedback updated successfully.');
    }

    public function destroy(Feedback $feedback)
    {
        // Eager load the booking relationship to ensure department_id is available for policy checks
        $feedback->load('booking');
        
        $this->authorize('delete', $feedback);
        
        $feedback->delete();

        return redirect()->route('client.feedback.index')
            ->with('success', 'Feedback deleted successfully.');
    }
}
