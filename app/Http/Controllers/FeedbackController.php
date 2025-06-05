<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = Feedback::with(['user', 'booking'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(5);

        return view('feedback.index', compact('feedbacks'));
    }

    public function create()
    {
        $bookings = Booking::where('requested_by', Auth::id())
            ->whereNotIn('id', Feedback::where('user_id', Auth::id())->pluck('booking_id'))
            ->get();

        return view('feedback.create', compact('bookings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'nullable|exists:bookings,id',
            'type' => 'required|in:booking,service,general',
            'content' => 'required|string|min:10',
            'rating' => 'required|integer|min:1|max:5',
            'is_public' => 'boolean'
        ]);

        $feedback = new Feedback($validated);
        $feedback->user_id = Auth::id();
        $feedback->is_approved = false;
        $feedback->save();

        return redirect()->route('feedback.index')
            ->with('success', 'Thank you for your feedback!');
    }

    public function edit(Feedback $feedback)
    {
        $this->authorize('update', $feedback);
        $bookings = Booking::where('requested_by', Auth::id())->get();
        
        return view('feedback.edit', compact('feedback', 'bookings'));
    }

    public function update(Request $request, Feedback $feedback)
    {
        $this->authorize('update', $feedback);

        $validated = $request->validate([
            'booking_id' => 'nullable|exists:bookings,id',
            'type' => 'required|in:booking,service,general',
            'content' => 'required|string|min:10',
            'rating' => 'required|integer|min:1|max:5',
            'is_public' => 'boolean'
        ]);

        $feedback->update($validated);

        return redirect()->route('feedback.index')
            ->with('success', 'Feedback updated successfully.');
    }

    public function destroy(Feedback $feedback)
    {
        $this->authorize('delete', $feedback);
        
        $feedback->delete();

        return redirect()->route('feedback.index')
            ->with('success', 'Feedback deleted successfully.');
    }
}
