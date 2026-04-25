<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $schoolName;

    /**
     * Create a new notification instance.
     */
    public function __construct($schoolName)
    {
        $this->schoolName = $schoolName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Account Approved - ' . $this->schoolName)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('We are pleased to inform you that your account for ' . $this->schoolName . ' has been approved by the administrator.')
            ->line('You can now log in to the dashboard and access all features.')
            ->action('Login to Dashboard', route('tenant.login'))
            ->line('Welcome to the community!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
