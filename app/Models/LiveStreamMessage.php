<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveStreamMessage extends Model
{
    protected $fillable = ['live_stream_id', 'user_id', 'guest_name', 'body'];

    public function user()   { return $this->belongsTo(User::class); }
    public function stream() { return $this->belongsTo(LiveStream::class, 'live_stream_id'); }

    public function displayName(): string
    {
        return $this->user?->name ?? $this->guest_name ?? 'Guest';
    }
}
