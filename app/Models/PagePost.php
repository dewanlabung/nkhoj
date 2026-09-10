<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagePost extends Model
{
    protected $fillable = [
        'social_page_id', 'user_id', 'type', 'body', 'image_url', 'video_url',
        'event_title', 'event_start', 'event_end', 'event_venue', 'event_ticket_url',
        'likes_count',
    ];

    protected $casts = [
        'event_start' => 'datetime',
        'event_end'   => 'datetime',
    ];

    public function page()
    {
        return $this->belongsTo(SocialPage::class, 'social_page_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function likes()
    {
        return $this->hasMany(PagePostLike::class);
    }

    public function isLikedBy(?User $user): bool
    {
        if (!$user) return false;
        return $this->likes()->where('user_id', $user->id)->exists();
    }
}
