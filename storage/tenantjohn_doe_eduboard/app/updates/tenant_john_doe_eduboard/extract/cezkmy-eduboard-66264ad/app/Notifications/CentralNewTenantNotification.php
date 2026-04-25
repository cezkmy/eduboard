<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CentralNewTenantNotification extends Notification
{
    use Queueable;

    public $tenantName;
    public $domain;

    /**
     * Create a new notification instance.
     */
    public function __construct($tenantName, $domain)
    {
        $this->tenantName = $tenantName;
        $this->domain = $domain;
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
            ->subject('🎉 New School Registered: ' . $this->tenantName)
            ->greeting('Hello Central Admin,')
            ->line('A brand new school has just provisioned an account on EduBoard!')
            ->line('**School Name:** ' . $this->tenantName)
            ->line('**Domain:** ' . $this->domain)
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
            'icon' => 'school',
            'title' => 'New School Registered',
            'desc' => $this->tenantName . ' (' . $this->domain . ')',
        ];
    }
}
