<?php

namespace App\Domains\Communication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationUnsubscribe extends Model
{
    protected $table = 'communication_unsubscribes';

    protected $fillable = [
        'user_id', 'unsubscribe_type', 'reason', 'unsubscribe_token', 'unsubscribed_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public static function createFromToken(string $token): ?self
    {
        return static::where('unsubscribe_token', $token)->first();
    }

    public static function generateToken(): string
    {
        return \Illuminate\Support\Str::random(64);
    }

    public function isValidToken(): bool
    {
        return $this->unsubscribe_token && strlen($this->unsubscribe_token) === 64;
    }

    public function updateUserPreferences(): void
    {
        $user = $this->user;
        if (!$user) {
            return;
        }

        $prefs = UserCommunicationPreference::getOrCreateForUser($user->id);

        match($this->unsubscribe_type) {
            'email' => $prefs->update(['email_newsletters' => false]),
            'in_app' => $prefs->update(['in_app_notifications' => false]),
            'all' => $prefs->update([
                'email_newsletters' => false,
                'email_announcements' => false,
                'in_app_notifications' => false,
                'push_notifications' => false,
            ]),
            default => null,
        };
    }
}
