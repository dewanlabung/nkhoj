<?php

namespace App\Core\Traits;

use App\Models\Notifications\NotificationActivityLog;
use App\Models\UserEngagement\User;

trait TracksNotificationActivity
{
    /**
     * Log notification activity with context
     */
    public function logActivity(
        ?User $user,
        string $action,
        ?string $channel = null,
        bool $success = true,
        ?string $errorMessage = null,
        ?array $data = null
    ): NotificationActivityLog {
        return NotificationActivityLog::log(
            $user,
            $this->getNotificationType(),
            $action,
            $channel,
            $success,
            $errorMessage,
            $data
        );
    }

    /**
     * Get notification type identifier
     */
    protected function getNotificationType(): string
    {
        return defined('static::NOTIF_ID')
            ? static::NOTIF_ID
            : class_basename($this);
    }

    /**
     * Log successful send
     */
    public function logSent(User $user, string $channel): void
    {
        $this->logActivity($user, 'sent', $channel, true);
    }

    /**
     * Log failed delivery
     */
    public function logFailed(User $user, string $channel, string $errorMessage): void
    {
        $this->logActivity($user, 'delivery_failed', $channel, false, $errorMessage);
    }

    /**
     * Log read/interaction
     */
    public function logRead(User $user): void
    {
        $this->logActivity($user, 'read', 'database', true);
    }

    /**
     * Log deletion
     */
    public function logDeleted(User $user): void
    {
        $this->logActivity($user, 'deleted', null, true);
    }
}
