<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantPlanUpgradedNotification extends Notification
{
    use Queueable;

    public $newPlan;

    /**
     * Create a new notification instance.
     */
    public function __construct($newPlan)
    {
        $this->newPlan = $newPlan;
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
            ->subject('🎉 Thank You For Upgrading to ' . $this->newPlan . '!')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your payment was successful and your EduBoard subscription has been instantly upgraded.')
            ->line('You are now officially on the **' . $this->newPlan . '** Plan.')
            ->line('Enjoy all your newly unlocked premium features and tools.')
            ->action('View My Dashboard', url('/dashboard'))
            ->line('Thank you for trusting EduBoard!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'icon' => 'upgrade',
            'title' => 'Plan Upgraded',
            'desc' => 'Welcome to the ' . $this->newPlan . ' plan!',
        ];
    }
}
