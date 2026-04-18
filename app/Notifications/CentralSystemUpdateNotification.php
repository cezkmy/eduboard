<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CentralSystemUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $version;
    private $releaseNotes;
    private $releaseUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $releaseData)
    {
        $this->version = $releaseData['tag_name'] ?? 'v2.0.0';
        $this->releaseNotes = $releaseData['body'] ?? 'No release notes provided.';
        $this->releaseUrl = $releaseData['html_url'] ?? '#';
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
        return [
            'type' => 'system_update_available',
            'title' => "New Update Available: {$this->version}",
            'message' => "A new system update ({$this->version}) has been detected on GitHub and is ready to be deployed.",
            'version' => $this->version,
            'release_notes' => $this->releaseNotes,
            'url' => $this->releaseUrl,
            'icon' => 'bi-github',
            'color' => 'text-primary',
            'bg' => 'bg-primary bg-opacity-10'
        ];
    }
}
