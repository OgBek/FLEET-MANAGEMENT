<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;

class ServiceRequestCompleted extends BaseNotification
{
    use Queueable;

    protected $serviceRequest;
    protected $oldStatus;
    protected $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(ServiceRequest $serviceRequest, ?string $oldStatus = null, ?string $newStatus = null)
    {
        $this->serviceRequest = $serviceRequest;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus ?? $serviceRequest->status;
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
        // Ensure relationships are loaded
        $this->serviceRequest->load(['vehicle', 'requestedBy', 'assignedTo']);
        
        $message = '';
        $title = 'Service Request ' . ucfirst(str_replace('_', ' ', $this->newStatus));
        
        switch ($this->newStatus) {
            case 'in_progress':
                $message = "Your service request #{$this->serviceRequest->id} has been started.";
                break;
            case 'completed':
                $message = "Your service request #{$this->serviceRequest->id} has been completed.";
                break;
            case 'rejected':
                $message = "Your service request #{$this->serviceRequest->id} has been rejected.";
                break;
            default:
                $message = "Your service request #{$this->serviceRequest->id} status has been updated to " . str_replace('_', ' ', $this->newStatus) . ".";
        }

        return [
            'title' => $title,
            'message' => $message,
            'type' => 'service_request_updated',
            'service_request_id' => $this->serviceRequest->id,
            'status' => $this->newStatus,
            'vehicle_id' => $this->serviceRequest->vehicle_id,
            'assigned_to' => $this->serviceRequest->assigned_to,
            'requested_by' => $this->serviceRequest->requested_by,
        ];
    }
}
