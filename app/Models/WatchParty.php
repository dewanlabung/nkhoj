<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchParty extends Model
{
    protected $fillable = ['uuid', 'host_id', 'title', 'video_url', 'status', 'join_code', 'playback_seconds', 'sync_at'];

    protected $casts = ['sync_at' => 'datetime'];

    public function host()     { return $this->belongsTo(User::class, 'host_id'); }
    public function members()  { return $this->hasMany(WatchPartyMember::class); }
    public function messages() { return $this->hasMany(WatchPartyMessage::class)->latest(); }

    public function getEmbedUrl(): ?string
    {
        $url = $this->video_url;
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $m)) {
            return "https://www.youtube.com/embed/{$m[1]}?enablejsapi=1&autoplay=1";
        }
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
            return "https://player.vimeo.com/video/{$m[1]}?autoplay=1";
        }
        return null;
    }

    public function isMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }
}
