<?php

namespace App\Core\Actions;

use App\Core\Models\NotificationActivityLog;
use App\Core\Models\NotificationSubscription;
use App\Models\User;
use File;
use Ramsey\Uuid\Uuid;

class SubscribeUserToNotifications
{
    /**
     * Subscribe user to notification types with default channel preferences
     */
    public function execute(User $user, ?array $notificationIds = null)
    {
        // Get notification configuration
        $config = File::getRequire(
            resource_path('defaults/notification-settings.php')
        );

        // If no specific notification IDs, subscribe to all available
        if (is_null($notificationIds)) {
            $notificationIds = collect($config['subscriptions'])
                ->map(function ($group) {
                    return collect($group['subscriptions'])
                        ->pluck('notif_id')
                        ->toArray();
                })
                ->flatten()
                ->toArray();
        }

        // Build subscription rows
        $rows = array_map(function ($notifId) use ($config, $user) {
            return [
                'id' => Uuid::uuid4()->toString(),
                'notif_id' => $notifId,
                'channels' => json_encode(
                    collect($config['available_channels'] ?? ['browser', 'email'])
                        ->mapWithKeys(fn($channel) => [$channel => true]),
                ),
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $notificationIds);

        // Delete existing subscriptions and insert new ones
        $user->notificationSubscriptions()->delete();
        $user->notificationSubscriptions()->insert($rows);

        // Log activity
        NotificationActivityLog::log(
            $user,
            'subscription',
            'initialized',
            'system',
            true,
            null,
            [
                'notification_ids' => $notificationIds,
                'count' => count($rows),
            ]
        );
    }
}
