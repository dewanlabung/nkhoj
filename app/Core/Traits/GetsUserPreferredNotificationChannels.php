<?php

namespace App\Core\Traits;

use App\Models\User;
use NotificationChannels\Fcm\FcmChannel;

trait GetsUserPreferredNotificationChannels
{
    /**
     * Get notification delivery channels based on user preferences
     * Default NOTIF_ID should be set on implementing class
     */
    public function via(User $notifiable): array
    {
        // Get notification ID from implementing class
        $notifId = $this->getNotificationId();

        // Get user's subscription for this notification type
        $subscription = $notifiable->notificationSubscriptions()
            ->where('notif_id', $notifId)
            ->first();

        $channels = [];

        if (!$subscription) {
            // No preference set - use defaults
            return ['database', 'mail'];
        }

        // Map user preferences to delivery channels
        foreach (array_filter($subscription->channels ?? []) as $channel => $isEnabled) {
            if (!$isEnabled) {
                continue;
            }

            if ($channel === 'browser') {
                $channels[] = 'database';
                $channels[] = 'broadcast'; // WebSocket for real-time
            } elseif ($channel === 'email') {
                $channels[] = 'mail';
            } elseif ($channel === 'mobile') {
                $channels[] = FcmChannel::class;
            } else {
                $channels[] = $channel;
            }
        }

        return array_unique($channels);
    }

    /**
     * Get notification ID from the class constant or method
     */
    protected function getNotificationId(): string
    {
        return defined('static::NOTIF_ID')
            ? static::NOTIF_ID
            : class_basename($this);
    }
}
