<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantNewUserNotification extends Notification
{
    use Queueable;

    public $userName;
    public $userRole;
    public $userEmail;

    /**
     * Create a new notification instance.
     */
    public function __construct($userName, $userRole, $userEmail)
    {
        $this->userName = $userName;
        $this->userRole = $userRole;
        $this->userEmail = $userEmail;
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
            ->subject('👤 New Member Registered: ' . $this->userName)
            ->greeting('Hello,')
            ->line('A new user has just registered under your EduBoard domain.')
            ->line('**Name:** ' . $this->userName)
            ->line('**Email:** ' . $this->userEmail)
            ->line('**Role:** ' . ucfirst($this->userRole))
            ->action('Manage Users', url('/admin/users'))
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
            'icon' => 'user',
            'title' => 'New User Registered',
            'desc' => $this->userName . ' joined as ' . $this->userRole,
        ];
    }
}
