<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantSystemUpdateNotification extends Notification
{
    use Queueable;

    public $version;

    /**
     * Create a new notification instance.
     */
    public function __construct($version)
    {
        $this->version = $version;
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
            ->subject('⚡ System Update Available: ' . $this->version)
            ->greeting('Hello Admin,')
            ->line('A new system patch (' . $this->version . ') is now available for your EduBoard instance.')
            ->line('You can opt-in to this update immediately without any downtime.')
            ->action('View Patch Notes', url('/admin/settings?tab=system_updates'))
            ->line('Thank you for using EduBoard!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'icon' => 'system',
            'title' => 'System Update Available',
            'desc' => 'Version ' . $this->version . ' can now be installed.',
        ];
    }
}
