<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    protected $fillable = ['user_id', 'type', 'in_app', 'email', 'push'];

    protected $casts = [
        'in_app' => 'boolean',
        'email'  => 'boolean',
        'push'   => 'boolean',
    ];

    public static function defaultsFor(int $userId): void
    {
        $types = ['comment', 'follow', 'new_post', 'like', 'mention'];
        foreach ($types as $type) {
            static::firstOrCreate(
                ['user_id' => $userId, 'type' => $type],
                ['in_app' => true, 'email' => false, 'push' => false]
            );
        }
    }
}
