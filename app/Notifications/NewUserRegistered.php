<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;

class NewUserRegistered extends BaseNotification
{
    use Queueable;

    protected $newUser;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $newUser)
    {
        $this->newUser = $newUser;
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
        $roles = $this->newUser->roles->pluck('name')->implode(', ');
        
        return [
            'title' => 'New User Registration',
            'message' => "New user {$this->newUser->name} ({$this->newUser->email}) has registered and requires approval.",
            'type' => Notification::NEW_USER_REGISTERED,
            'user_id' => $this->newUser->id,
            'roles' => $roles,
            'link' => route('admin.users.show', $this->newUser->id),
        ];
    }
}
