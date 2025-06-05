<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;

class BookingAssignedToDriver extends BaseNotification
{
    use Queueable;

    protected $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
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
        $requester = $this->booking->requestedBy;
        $vehicle = $this->booking->vehicle;
        $pickupDate = $this->booking->start_time->format('M d, Y g:i A');
        $returnDate = $this->booking->end_time->format('M d, Y g:i A');
        $pickupLocation = $this->booking->pickup_location;
        $destination = $this->booking->destination;
        
        // Get brand name safely with null check
        $brandName = $vehicle->brand ? $vehicle->brand->name : 'Unknown Brand';
        
        return [
            'title' => 'New Booking Assignment',
            'message' => "You have been assigned to drive {$requester->name} from {$pickupLocation} to {$destination} in {$brandName} {$vehicle->model} ({$vehicle->registration_number}). Pickup: {$pickupDate}, Return: {$returnDate}",
            'type' => Notification::BOOKING_ASSIGNED,
            'booking_id' => $this->booking->id,
            'vehicle_id' => $this->booking->vehicle_id,
            'requester_id' => $this->booking->requested_by,
            'pickup_date' => $this->booking->start_time,
            'return_date' => $this->booking->end_time,
            'pickup_location' => $pickupLocation,
            'destination' => $destination,
            // Remove link to non-existent route
            'link' => null,
        ];
    }
}
