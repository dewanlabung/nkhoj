<?php

namespace App\Models\UserEngagement;

use Illuminate\Database\Eloquent\Model;

class ChannelMessageReaction extends Model
{
    public $timestamps = false;
    protected $fillable = ['channel_message_id', 'user_id', 'emoji'];

    public function message()
    {
        return $this->belongsTo(ChannelMessage::class, 'channel_message_id');
    }
}
