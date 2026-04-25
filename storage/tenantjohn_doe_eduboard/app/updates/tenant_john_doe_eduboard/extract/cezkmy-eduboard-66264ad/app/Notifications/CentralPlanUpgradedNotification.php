<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CentralPlanUpgradedNotification extends Notification
{
    use Queueable;

    public $tenantName;
    public $newPlan;

    /**
     * Create a new notification instance.
     */
    public function __construct($tenantName, $newPlan)
    {
        $this->tenantName = $tenantName;
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
            ->subject('🚀 Plan Upgrade: ' . $this->tenantName)
            ->greeting('Hello Central Admin,')
            ->line('Great news! A tenant has just upgraded their subscription.')
            ->line('**School Name:** ' . $this->tenantName)
            ->line('**New Plan:** ' . $this->newPlan)
            ->action('View Tenant Details', config('app.url') . '/admin/tenants')
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
            'icon' => 'upgrade',
            'title' => 'Tenant Plan Upgraded',
            'desc' => $this->tenantName . ' upgraded to ' . $this->newPlan,
        ];
    }
}
