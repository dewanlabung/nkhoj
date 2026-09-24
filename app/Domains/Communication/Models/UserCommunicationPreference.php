<?php

namespace App\Domains\Communication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCommunicationPreference extends Model
{
    protected $table = 'user_communication_preferences';

    protected $fillable = [
        'user_id',
        'email_newsletters', 'email_announcements',
        'in_app_notifications', 'push_notifications',
        'email_frequency', 'preferred_send_time', 'preferred_timezone',
        'category_preferences',
    ];

    protected $casts = [
        'email_newsletters' => 'boolean',
        'email_announcements' => 'boolean',
        'in_app_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'category_preferences' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public static function getOrCreateForUser($userId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'email_newsletters' => true,
                'email_announcements' => true,
                'in_app_notifications' => true,
                'push_notifications' => false,
                'email_frequency' => 'weekly',
                'preferred_send_time' => '09:00',
                'preferred_timezone' => 'UTC',
            ]
        );
    }

    public function canReceiveChannel(string $channel): bool
    {
        $prefKey = match($channel) {
            'email' => 'email_newsletters',
            'announcement' => 'email_announcements',
            'in_app' => 'in_app_notifications',
            'push' => 'push_notifications',
            default => null,
        };

        return $prefKey ? $this->{$prefKey} : false;
    }

    public function canReceiveCategory(string $category): bool
    {
        $prefs = $this->category_preferences ?? [];
        return $prefs[$category] ?? true; // Default: allow
    }
}
