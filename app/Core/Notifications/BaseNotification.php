<?php

namespace App\Core\Notifications;

use App\Core\Traits\GetsUserPreferredNotificationChannels;
use App\Core\Traits\TracksNotificationActivity;
use App\Models\UserEngagement\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

abstract class BaseNotification extends Notification
{
    use Queueable, GetsUserPreferredNotificationChannels, TracksNotificationActivity;

    /**
     * Notification type ID (override in subclass)
     */
    public const NOTIF_ID = 'generic';

    /**
     * Get notification type
     */
    abstract public function notificationType(): string;

    /**
     * Get notification subject
     */
    abstract protected function subject(User $notifiable): string;

    /**
     * Get notification body
     */
    abstract protected function body(User $notifiable): string;

    /**
     * Get action URL (optional)
     */
    protected function actionUrl(): ?string
    {
        return null;
    }

    /**
     * Get action label (optional)
     */
    protected function actionLabel(): string
    {
        return 'View';
    }

    /**
     * Get notification icon (optional)
     */
    protected function icon(): ?string
    {
        return null;
    }

    /**
     * Convert to array for database storage
     */
    public function toArray(User $notifiable): array
    {
        return [
            'subject' => $this->subject($notifiable),
            'body' => $this->body($notifiable),
            'action_url' => $this->actionUrl(),
            'action_label' => $this->actionLabel(),
            'icon' => $this->icon(),
            'type' => $this->notificationType(),
        ];
    }
}
