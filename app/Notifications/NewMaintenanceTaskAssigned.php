<?php

namespace App\Notifications;

use App\Models\MaintenanceTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMaintenanceTaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    protected $task;

    /**
     * Create a new notification instance.
     */
    public function __construct(MaintenanceTask $task)
    {
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $vehicleInfo = $this->task->vehicle ? 
            "{$this->task->vehicle->brand->name} {$this->task->vehicle->model} ({$this->task->vehicle->registration_number})" : 
            "Unknown vehicle";
            
        return (new MailMessage)
                    ->subject('New Maintenance Task Assigned')
                    ->line('A new maintenance task has been assigned to you.')
                    ->line('Vehicle: ' . $vehicleInfo)
                    ->line('Task: ' . $this->task->title)
                    ->line('Priority: ' . ucfirst($this->task->priority))
                    ->line('Due Date: ' . $this->task->due_date->format('M d, Y'))
                    ->action('View Task', route('maintenance.tasks.show', $this->task->id))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $vehicle = $this->task->vehicle;
        $vehicleInfo = $vehicle ? "{$vehicle->brand->name} {$vehicle->model} ({$vehicle->registration_number})" : "Unknown vehicle";
        
        return [
            'task_id' => $this->task->id,
            'title' => 'New Maintenance Task Assigned',
            'message' => "You have been assigned a new maintenance task for vehicle: {$vehicleInfo}",
            'type' => 'maintenance_task_assigned',
            'url' => route('maintenance.tasks.show', $this->task->id),
            'vehicle_id' => $this->task->vehicle_id,
            'vehicle_info' => $vehicleInfo,
        ];
    }
}
