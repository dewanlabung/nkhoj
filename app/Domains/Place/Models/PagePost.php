<?php

namespace App\Domains\Place\Models;

use Illuminate\Database\Eloquent\Model;

class PagePost extends Model
{
    protected $fillable = [
        'social_page_id', 'user_id', 'type', 'post_format', 'article_title',
        'body', 'image_url', 'video_url',
        'event_title', 'event_start', 'event_end', 'event_venue', 'event_ticket_url',
        'likes_count', 'comments_count',
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

    public function comments()
    {
        return $this->hasMany(PagePostComment::class);
    }

    public function isLikedBy(?User $user): bool
    {
        if (!$user) return false;
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function reactionBy(?User $user): ?string
    {
        if (!$user) return null;
        $like = $this->likes()->where('user_id', $user->id)->first();
        return $like?->reaction;
    }

    public function pollOptions()
    {
        return $this->hasMany(PagePollOption::class)->orderBy('sort_order');
    }

    public function pollVotes()
    {
        return $this->hasMany(PagePollVote::class);
    }

    public function userVote(?User $user): ?PagePollVote
    {
        if (!$user) return null;
        return $this->pollVotes()->where('user_id', $user->id)->first();
    }

    public function topReactions(int $limit = 3): array
    {
        return $this->likes()
            ->selectRaw('reaction, COUNT(*) as cnt')
            ->groupBy('reaction')
            ->orderByDesc('cnt')
            ->limit($limit)
            ->pluck('cnt', 'reaction')
            ->toArray();
    }
}
