<?php

namespace App\Notifications;

use App\Models\VehicleReport;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;

class VehicleReportStatusUpdated extends BaseNotification
{
    use Queueable;

    /**
     * The vehicle report instance.
     *
     * @var \App\Models\VehicleReport
     */
    public $vehicleReport;

    /**
     * The old status of the report.
     *
     * @var string
     */
    public $oldStatus;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\VehicleReport  $vehicleReport
     * @param  string  $oldStatus
     * @return void
     */
    public function __construct(VehicleReport $vehicleReport, string $oldStatus)
    {
        $this->vehicleReport = $vehicleReport;
        $this->oldStatus = $oldStatus;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }



    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $statusText = ucfirst(str_replace('_', ' ', $this->vehicleReport->status));
        $oldStatusText = ucfirst(str_replace('_', ' ', $this->oldStatus));
        
        // Different title and message for maintenance staff vs others
        if ($notifiable->hasRole('maintenance_staff') && $this->vehicleReport->status === 'in_progress') {
            $title = 'New Maintenance Task Assigned';
            $message = "New maintenance task assigned: Vehicle report {$this->vehicleReport->id} has been assigned to you.";
        } elseif ($notifiable->hasRole('maintenance_staff')) {
            $title = 'Vehicle Report Status Updated';
            $message = "Vehicle report {$this->vehicleReport->id} status changed from {$oldStatusText} to {$statusText}.";
        } else {
            $title = 'Vehicle Report Status Updated';
            $message = $this->vehicleReport->status === 'in_progress' 
                ? "Your report {$this->vehicleReport->id} has been accepted and is now in progress. Our maintenance team is working on it."
                : "The status of report {$this->vehicleReport->id} has been changed from {$oldStatusText} to {$statusText}.";
        }
        
        // Determine the correct route based on user role
        $route = 'driver.vehicle-reports.show';
        if ($notifiable->hasRole('maintenance_staff')) {
            $route = 'maintenance.vehicle-reports.show';
        } elseif ($notifiable->hasRole('admin')) {
            $route = 'admin.vehicle-reports.show';
        }
        
        return [
            'title' => $title,
            'message' => $message,
            'type' => Notification::VEHICLE_STATUS_UPDATED,
            'report_id' => $this->vehicleReport->id,
            'vehicle_id' => $this->vehicleReport->vehicle_id,
            'severity' => $this->vehicleReport->severity ?? 'medium',
            'user_id' => $this->vehicleReport->user_id,
            'status' => $this->vehicleReport->status,
            'link' => route($route, $this->vehicleReport->id)
        ];
    }
}
