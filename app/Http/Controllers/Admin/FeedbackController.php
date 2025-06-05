<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Booking;
use App\Notifications\FeedbackReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index()
    {
        $feedbacks = Feedback::with(['user', 'booking.vehicle'])
            ->latest()
            ->paginate(3);

        return view('admin.feedback.index', compact('feedbacks'));
    }

    public function create()
    {
        // Get completed bookings that don't have admin feedback yet and have valid vehicle and driver
        $bookings = Booking::where('status', 'completed')
            ->whereDoesntHave('feedback', function($query) {
                $query->where('user_id', auth()->id());
            })
            ->with(['vehicle', 'driver', 'department', 'requestedBy'])
            ->whereHas('vehicle')  // Only include bookings with an existing vehicle
            ->whereHas('driver')   // Only include bookings with an existing driver
            ->latest()
            ->take(100)
            ->get()
            ->filter(function($booking) {
                // Additional check to ensure relationships are loaded
                return $booking->vehicle && $booking->driver;
            });

        return view('admin.feedback.create', compact('bookings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'type' => 'required|in:service,driver,vehicle,general',
            'content' => 'required|string|min:5',
            'rating' => 'required|integer|min:1|max:5',
            'is_public' => 'boolean',
        ]);

        // Use a database transaction to ensure data consistency
        return DB::transaction(function() use ($validated, $request) {
            // Check if feedback already exists for this booking and admin
            $existingFeedback = Feedback::where('booking_id', $validated['booking_id'])
                ->where('user_id', auth()->id())
                ->first();

            if ($existingFeedback) {
                return redirect()->back()
                    ->with('error', 'You have already submitted feedback for this booking.');
            }

            $feedback = Feedback::create([
                'user_id' => auth()->id(),
                'booking_id' => $validated['booking_id'],
                'type' => $validated['type'],
                'content' => $validated['content'],
                'rating' => $validated['rating'],
                'is_public' => $request->boolean('is_public'),
                'is_approved' => true, // Auto-approve admin feedback
            ]);

            // Load the booking relationship and notify the driver
            $feedback->load('booking.driver');
            if ($feedback->booking && $feedback->booking->driver) {
                // Check if a similar notification already exists
                $existingNotification = $feedback->booking->driver
                    ->notifications()
                    ->where('type', FeedbackReceived::class)
                    ->where('data->feedback_id', $feedback->id)
                    ->exists();

                if (!$existingNotification) {
                    $feedback->booking->driver->notify(new FeedbackReceived($feedback));
                }
            }

            return redirect()->route('admin.feedback.index')
                ->with('success', 'Feedback has been submitted successfully.');
        });
    }

    public function show(Feedback $feedback)
    {
        try {
            // Log the feedback details before loading relationships
            \Log::info('Loading feedback:', [
                'feedback_id' => $feedback->id,
                'booking_id' => $feedback->booking_id,
                'user_id' => $feedback->user_id
            ]);

            // Eager load all necessary relationships with their nested relationships
            $feedback->load([
                'user',
                'booking' => function($query) {
                    $query->withTrashed(); // Load even soft deleted bookings
                },
                'booking.vehicle',
                'booking.driver'
            ]);

            // Log the loaded relationships
            \Log::info('Loaded feedback relationships:', [
                'has_booking' => $feedback->booking ? 'yes' : 'no',
                'booking_details' => $feedback->booking ? [
                    'id' => $feedback->booking->id,
                    'has_vehicle' => $feedback->booking->vehicle ? 'yes' : 'no',
                    'vehicle_id' => $feedback->booking->vehicle ? $feedback->booking->vehicle->id : null
                ] : null
            ]);

            return view('admin.feedback.show', compact('feedback'));
        } catch (\Exception $e) {
            \Log::error('Error showing feedback:', [
                'feedback_id' => $feedback->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Error loading feedback details: ' . $e->getMessage());
        }
    }

    public function approve(Feedback $feedback)
    {
        DB::beginTransaction();
        try {
            $feedback->update([
                'is_approved' => true,
                'approver_type' => 'admin',
                'status' => 'approved'
            ]);

            // Notify the user who submitted the feedback
            if ($feedback->user) {
                $feedback->user->notify(new \App\Notifications\FeedbackStatusUpdated(
                    $feedback,
                    'approved',
                    'Your feedback has been approved by the administrator.'
                ));
            }

            DB::commit();
            return back()->with('success', 'Feedback has been approved.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to approve feedback: ' . $e->getMessage());
        }
    }

    public function reject(Feedback $feedback)
    {
        DB::beginTransaction();
        try {
            $feedback->update([
                'is_approved' => false,
                'approver_type' => 'admin',
                'status' => 'rejected'
            ]);

            // Notify the user who submitted the feedback
            if ($feedback->user) {
                $feedback->user->notify(new \App\Notifications\FeedbackStatusUpdated(
                    $feedback,
                    'rejected',
                    'Your feedback has been rejected by the administrator.'
                ));
            }

            DB::commit();
            return back()->with('success', 'Feedback has been rejected.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to reject feedback: ' . $e->getMessage());
        }
    }

    public function destroy(Feedback $feedback)
    {
        $feedback->delete();
        return redirect()->route('admin.feedback.index')
            ->with('success', 'Feedback has been deleted.');
    }
}
