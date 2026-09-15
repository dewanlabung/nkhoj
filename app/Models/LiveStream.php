<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveStream extends Model
{
    protected $fillable = ['uuid', 'user_id', 'title', 'description', 'embed_url', 'thumbnail_url', 'status', 'viewer_count', 'peak_viewers', 'started_at', 'ended_at'];

    protected $casts = ['started_at' => 'datetime', 'ended_at' => 'datetime'];

    public function user()    { return $this->belongsTo(User::class); }
    public function messages(){ return $this->hasMany(LiveStreamMessage::class); }

    public function isLive(): bool { return $this->status === 'live'; }

    public function getEmbedHtml(): ?string
    {
        if (!$this->embed_url) return null;
        $url = $this->embed_url;
        // YouTube
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $m)) {
            return "https://www.youtube.com/embed/{$m[1]}?autoplay=1";
        }
        // YouTube live embed already
        if (str_contains($url, 'youtube.com/embed')) return $url;
        return $url;
    }
}
