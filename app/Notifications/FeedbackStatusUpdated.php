<?php

namespace App\Notifications;

use App\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FeedbackStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public $feedback;
    public $status;
    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Feedback $feedback, string $status, string $message)
    {
        $this->feedback = $feedback;
        $this->status = $status;
        $this->message = $message;
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
        $subject = "Feedback " . ucfirst($this->status);
        
        return (new MailMessage)
            ->subject($subject)
            ->line($this->message)
            ->action('View Feedback', route('client.feedback.show', $this->feedback->id))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'feedback_id' => $this->feedback->id,
            'status' => $this->status,
            'message' => $this->message,
            'link' => route('client.feedback.show', $this->feedback->id),
        ];
    }
}
