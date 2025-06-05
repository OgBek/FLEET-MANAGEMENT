<?php

namespace App\Notifications;

use App\Models\MaintenanceSchedule;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;

class MaintenanceTaskAssigned extends BaseNotification
{
    use Queueable;

    protected $maintenanceSchedule;

    /**
     * Create a new notification instance.
     */
    public function __construct(MaintenanceSchedule $maintenanceSchedule)
    {
        $this->maintenanceSchedule = $maintenanceSchedule;
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
        $vehicle = $this->maintenanceSchedule->vehicle;
        $vehicleInfo = $vehicle ? "{$vehicle->brand->name} {$vehicle->model} ({$vehicle->registration_number})" : "Unknown vehicle";
        $maintenanceType = ucwords(str_replace('_', ' ', $this->maintenanceSchedule->maintenance_type));
        $scheduledDate = $this->maintenanceSchedule->scheduled_date->format('M d, Y');
        
        // Determine the appropriate route based on user role
        $route = 'admin.maintenance.schedules.show';
        if ($notifiable->hasRole('maintenance_staff')) {
            $route = 'maintenance.schedules.show';
        }
        
        return [
            'title' => "New Maintenance Task: {$maintenanceType}",
            'message' => "You have been assigned to perform {$maintenanceType} maintenance on {$vehicleInfo} scheduled for {$scheduledDate}",
            'type' => Notification::MAINTENANCE_DUE,
            'schedule_id' => $this->maintenanceSchedule->id,
            'vehicle_id' => $this->maintenanceSchedule->vehicle_id,
            'maintenance_type' => $this->maintenanceSchedule->maintenance_type,
            'scheduled_date' => $this->maintenanceSchedule->scheduled_date,
            'link' => route($route, $this->maintenanceSchedule->id),
        ];
    }
}
