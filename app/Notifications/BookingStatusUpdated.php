<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;

class BookingStatusUpdated extends BaseNotification
{
    use Queueable;

    protected $booking;
    protected $oldStatus;
    protected $newStatus;
    protected $rejectionReason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking, string $oldStatus, ?string $newStatus = null, ?string $rejectionReason = null)
    {
        $this->booking = $booking;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus ?? $booking->status;
        $this->rejectionReason = $rejectionReason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Ensure all relationships are loaded
        $this->booking->load(['vehicle.brand', 'vehicle.type.category']);
        
        // Build vehicle info with null checks
        $vehicleInfo = "Unknown vehicle";
        if ($this->booking->vehicle && $this->booking->vehicle->brand) {
            $vehicleInfo = "{$this->booking->vehicle->brand->name} {$this->booking->vehicle->model} ({$this->booking->vehicle->registration_number})";
        }

        $status = ucfirst($this->newStatus);
        $pickupDate = $this->booking->start_time->format('M d, Y g:i A');
        $returnDate = $this->booking->end_time->format('M d, Y g:i A');
        
        $messages = [
            'approved' => "Your booking request for {$vehicleInfo} has been approved! Pickup: {$pickupDate}, Return: {$returnDate}",
            'rejected' => "Your booking request for {$vehicleInfo} has been rejected." . 
                        ($this->rejectionReason ? " Reason: {$this->rejectionReason}" : " Please contact the admin for more information."),
            'cancelled' => "Your booking for {$vehicleInfo} has been cancelled.",
            'completed' => "Your booking for {$vehicleInfo} has been marked as completed.",
            'in_progress' => "Your booking for {$vehicleInfo} is now in progress.",
        ];

        $notificationType = match($this->newStatus) {
            'approved' => Notification::BOOKING_APPROVED,
            'rejected' => Notification::BOOKING_REJECTED,
            'cancelled' => Notification::BOOKING_REJECTED,
            'completed' => Notification::TRIP_COMPLETED,
            'in_progress' => Notification::TRIP_STARTED,
            default => Notification::BOOKING_CREATED
        };

        // Determine the appropriate route based on user role
        $route = 'admin.bookings.show';
        if ($notifiable->hasRole('driver')) {
            $route = 'driver.bookings.show';
        } elseif ($notifiable->hasRole('department_head') || $notifiable->hasRole('department_staff')) {
            // Client routes need the 'client.' prefix based on the route definitions
            $route = 'client.bookings.show';
        } elseif ($notifiable->hasRole('client')) {
            $route = 'client.bookings.show';
        }
        
        return [
            'title' => "Booking {$status}",
            'message' => $messages[$this->newStatus] ?? "Your booking status has been updated to {$status}.",
            'type' => $notificationType,
            'booking_id' => $this->booking->id,
            'vehicle_id' => $this->booking->vehicle_id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'rejection_reason' => $this->rejectionReason,
            'pickup_date' => $this->booking->start_time,
            'return_date' => $this->booking->end_time,
            'link' => route($route, $this->booking->id),
        ];
    }
}
