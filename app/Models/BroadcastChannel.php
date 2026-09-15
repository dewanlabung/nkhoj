<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BroadcastChannel extends Model
{
    protected $fillable = ['user_id', 'name', 'slug', 'description', 'cover_url'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function subscribers()
    {
        return $this->belongsToMany(User::class, 'channel_subscribers', 'channel_id', 'user_id')
                    ->withPivot('subscribed_at');
    }

    public function messages()
    {
        return $this->hasMany(ChannelMessage::class, 'channel_id')->latest();
    }

    public function isSubscribedBy(User $user): bool
    {
        return $this->subscribers()->where('user_id', $user->id)->exists();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
