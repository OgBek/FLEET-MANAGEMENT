<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;

class NewBookingCreated extends BaseNotification
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
        $vehicle = $this->booking->vehicle;
        $vehicleInfo = $vehicle ? "{$vehicle->brand->name} {$vehicle->model}" : "Unknown vehicle";
        $requester = $this->booking->requestedBy;
        $requesterName = $requester ? $requester->name : "Unknown user";
        $departmentName = $this->booking->department ? $this->booking->department->name : "Unknown department";
        
        $startTime = $this->booking->start_time->format('M d, Y H:i');
        $endTime = $this->booking->end_time->format('M d, Y H:i');
        
        // Determine the appropriate route based on user role
        $route = 'admin.bookings.show';
        if ($notifiable->hasRole('driver')) {
            $route = 'driver.bookings.show';
        } elseif ($notifiable->hasRole('client')) {
            $route = 'client.bookings.show';
        } elseif ($notifiable->hasRole('department_head') || $notifiable->hasRole('department_staff')) {
            $route = 'client.bookings.show';
        }
        
        return [
            'title' => "New Booking Request",
            'message' => "{$requesterName} from {$departmentName} requested {$vehicleInfo} from {$startTime} to {$endTime}",
            'type' => Notification::BOOKING_CREATED,
            'booking_id' => $this->booking->id,
            'vehicle_id' => $this->booking->vehicle_id,
            'user_id' => $this->booking->requested_by,
            'driver_id' => $this->booking->driver_id,
            'link' => route($route, $this->booking->id),
        ];
    }
}
