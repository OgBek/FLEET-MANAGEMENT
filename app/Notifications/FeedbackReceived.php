<?php

namespace App\Notifications;

use App\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeedbackReceived extends Notification
{
    use Queueable;

    protected $feedback;

    /**
     * Create a new notification instance.
     */
    public function __construct(Feedback $feedback)
    {
        $this->feedback = $feedback;
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
        $type = ucfirst($this->feedback->type);
        $rating = $this->feedback->rating;
        $stars = str_repeat('⭐', $rating);
        
        // Determine the appropriate route based on user role
        $route = 'admin.feedback.show';
        if ($notifiable->hasRole('driver')) {
            $route = 'driver.feedback.show';
        } elseif ($notifiable->hasRole('client')) {
            $route = 'client.feedback.show';
        }
        
        return [
            'title' => "New {$type} Feedback Received",
            'message' => "You received a {$rating}-star rating ({$stars}) for your trip.",
            'type' => 'feedback_received',
            'feedback_id' => $this->feedback->id,
            'booking_id' => $this->feedback->booking_id,
            'rating' => $rating,
            'is_public' => $this->feedback->is_public,
            'link' => route($route, $this->feedback->id),
        ];
    }
}
