<?php

namespace App\Notifications;

use App\Models\Vehicle;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Facades\Log;

class NewVehicleAdded extends BaseNotification
{
    use Queueable;

    protected $vehicle;

    /**
     * Create a new notification instance.
     */
    public function __construct(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;
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
        $vehicle = $this->vehicle->load(['brand', 'type.category']);
        $features = $vehicle->features ? implode(', ', array_map('ucfirst', $vehicle->features)) : 'None';
        
        // Determine the appropriate route based on user role
        $route = 'admin.vehicles.show';
        if ($notifiable->hasRole('driver')) {
            $route = 'driver.vehicles.show';
        } elseif ($notifiable->hasRole(['department_head', 'department_staff'])) {
            $route = 'client.vehicles.show'; // Department heads and staff use the client routes
        } elseif ($notifiable->hasRole('maintenance_staff')) {
            $route = 'maintenance.vehicles.show';
        }

        // Log notification creation for debugging
        Log::info('Creating new vehicle notification', [
            'user_id' => $notifiable->id,
            'user_roles' => $notifiable->getRoleNames(),
            'route' => $route,
            'vehicle_id' => $vehicle->id
        ]);
        
        return [
            'title' => 'New Vehicle Available',
            'message' => "A new {$vehicle->brand->name} {$vehicle->model} ({$vehicle->registration_number}) is now available for booking! " .
                        "Type: {$vehicle->type->name}, Category: {$vehicle->type->category->name}, " .
                        "Capacity: {$vehicle->passenger_capacity} passengers, " .
                        "Features: {$features}",
            'type' => Notification::NEW_VEHICLE_ADDED,
            'vehicle_id' => $vehicle->id,
            'brand' => $vehicle->brand->name,
            'model' => $vehicle->model,
            'registration_number' => $vehicle->registration_number,
            'vehicle_type' => $vehicle->type->name,
            'category' => $vehicle->type->category->name,
            'passenger_capacity' => $vehicle->passenger_capacity,
            'features' => $vehicle->features,
            'link' => route($route, $vehicle->id),
        ];
    }
}
