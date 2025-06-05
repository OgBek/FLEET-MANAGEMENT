<?php

namespace App\Notifications;

use App\Models\VehicleReport;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification as BaseNotification;

class VehicleIssueApproved extends BaseNotification
{
    use Queueable;

    protected $vehicleReport;

    /**
     * Create a new notification instance.
     */
    public function __construct(VehicleReport $vehicleReport)
    {
        $this->vehicleReport = $vehicleReport;
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
        $vehicle = $this->vehicleReport->vehicle;
        $vehicleInfo = $vehicle ? "{$vehicle->brand->name} {$vehicle->model} ({$vehicle->registration_number})" : "Unknown vehicle";
        $severity = ucfirst($this->vehicleReport->severity);
        $driver = $this->vehicleReport->driver;
        $driverName = $driver ? $driver->name : "Unknown driver";
        
        // Determine the appropriate route based on user role
        $route = 'admin.vehicle-reports.show';
        if ($notifiable->hasRole('maintenance_staff')) {
            $route = 'maintenance.vehicle-reports.show';
        } elseif ($notifiable->hasRole('driver')) {
            $route = 'driver.vehicle-reports.show';
        }
        
        return [
            'title' => "Vehicle Issue Requires Attention: {$severity}",
            'message' => "A {$severity} issue reported by {$driverName} for {$vehicleInfo} has been approved for maintenance: {$this->vehicleReport->title}",
            'type' => Notification::VEHICLE_REPORT,
            'report_id' => $this->vehicleReport->id,
            'vehicle_id' => $this->vehicleReport->vehicle_id,
            'severity' => $this->vehicleReport->severity,
            'user_id' => $this->vehicleReport->user_id,
            'status' => $this->vehicleReport->status,
            'link' => route($route, $this->vehicleReport->id),
        ];
    }
}
