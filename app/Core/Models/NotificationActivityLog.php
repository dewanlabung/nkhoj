<?php

namespace App\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationActivityLog extends Model
{
    protected $guarded = ['id'];
    protected $table = 'notification_activity_logs';

    protected $casts = [
        'user_id' => 'integer',
        'data' => 'array',
        'success' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user this log belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a notification activity
     */
    public static function log(
        ?User $user,
        string $notifType,
        string $action,
        ?string $channel = null,
        bool $success = true,
        ?string $errorMessage = null,
        ?array $data = null
    ): self {
        return self::create([
            'user_id' => $user?->id,
            'notif_type' => $notifType,
            'action' => $action,
            'channel' => $channel,
            'success' => $success,
            'error_message' => $errorMessage,
            'data' => $data,
            'ip_address' => request()?->ip(),
        ]);
    }

    /**
     * Get logs for a user
     */
    public static function forUser(User $user, $limit = 50)
    {
        return self::where('user_id', $user->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get failed delivery logs
     */
    public static function failedDeliveries($limit = 50)
    {
        return self::where('success', false)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs by notification type
     */
    public static function byNotificationType(string $notifType, $limit = 50)
    {
        return self::where('notif_type', $notifType)
            ->latest()
            ->limit($limit)
            ->get();
    }
}
