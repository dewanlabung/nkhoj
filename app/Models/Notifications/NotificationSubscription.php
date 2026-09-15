<?php

namespace App\Models\Notifications;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;

class NotificationSubscription extends Model
{
    protected $guarded = ['id'];
    protected $keyType = 'string';
    public $timestamps = true;
    public $incrementing = false;

    protected $casts = [
        'user_id' => 'integer',
        'channels' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user this subscription belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            if (!$model->getAttribute('id')) {
                $model->setAttribute('id', Uuid::uuid4()->toString());
            }
        });
    }

    /**
     * Check if user is subscribed to a specific channel for this notification
     */
    public function isSubscribedToChannel(string $channel): bool
    {
        return (bool)($this->channels[$channel] ?? false);
    }

    /**
     * Subscribe to a channel
     */
    public function subscribeToChannel(string $channel): self
    {
        $channels = $this->channels ?? [];
        $channels[$channel] = true;
        $this->channels = $channels;
        return $this;
    }

    /**
     * Unsubscribe from a channel
     */
    public function unsubscribeFromChannel(string $channel): self
    {
        $channels = $this->channels ?? [];
        $channels[$channel] = false;
        $this->channels = $channels;
        return $this;
    }

    /**
     * Get active channels for this subscription
     */
    public function getActiveChannels(): array
    {
        return array_keys(array_filter($this->channels ?? []));
    }
}
