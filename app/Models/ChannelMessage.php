<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelMessage extends Model
{
    protected $fillable = ['channel_id', 'body', 'media_url', 'media_type'];

    public function channel()
    {
        return $this->belongsTo(BroadcastChannel::class, 'channel_id');
    }

    public function reactions()
    {
        return $this->hasMany(ChannelMessageReaction::class);
    }
}
