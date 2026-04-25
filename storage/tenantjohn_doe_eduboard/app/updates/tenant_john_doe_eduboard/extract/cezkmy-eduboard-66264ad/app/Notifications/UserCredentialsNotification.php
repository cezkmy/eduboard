<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserCredentialsNotification extends Notification
{
    use Queueable;

    public $name;
    public $email;
    public $password;
    public $schoolName;

    /**
     * Create a new notification instance.
     */
    public function __construct($name, $email, $password, $schoolName)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
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
            ->subject('Welcome to ' . $this->schoolName)
            ->greeting('Hello ' . $this->name . ',')
            ->line('An account has been created for you at ' . $this->schoolName . '.')
            ->line('You can now log in using the credentials below:')
            ->line('**Email:** ' . $this->email)
            ->line('**Password:** ' . $this->password)
            ->action('Login Now', url('/login'))
            ->line('Please change your password after logging in for security reasons.')
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
            'icon' => 'user-plus',
            'title' => 'Account Created',
            'desc' => 'Your account has been created successfully.',
        ];
    }
}
