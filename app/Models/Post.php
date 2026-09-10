<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid', 'author_id', 'category_id', 'slug',
        'title', 'excerpt', 'body', 'status',
        'is_featured', 'is_pro', 'view_count', 'published_at',
        'seo_title', 'seo_desc', 'thumbnail_url',
        'scheduled_at', 'post_format',
        'event_start_at', 'event_end_at', 'event_organizer', 'event_venue',
        'event_address', 'event_lat', 'event_lng',
        'event_schedule', 'event_highlights', 'event_speakers',
        'event_registration_type', 'event_faq',
        'optional_url', 'sources', 'article_faq',
        'is_sensitive', 'is_promoted', 'promoted_until', 'repost_of_id',
    ];

    protected function casts(): array
    {
        return [
            'body'             => 'array',
            'is_featured'      => 'boolean',
            'is_pro'           => 'boolean',
            'published_at'     => 'datetime',
            'scheduled_at'     => 'datetime',
            'event_start_at'   => 'datetime',
            'event_end_at'     => 'datetime',
            'event_schedule'   => 'array',
            'event_highlights' => 'array',
            'event_speakers'   => 'array',
            'event_faq'        => 'array',
            'sources'          => 'array',
            'article_faq'      => 'array',
            'is_sensitive'     => 'boolean',
            'is_promoted'      => 'boolean',
            'promoted_until'   => 'datetime',
        ];
    }

    public function repost(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Post::class, 'repost_of_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function series()
    {
        return $this->belongsTo(\App\Models\PostSeries::class, 'series_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function reactionCounts(): array
    {
        return $this->reactions()->selectRaw('emoji, count(*) as total')
            ->groupBy('emoji')->pluck('total', 'emoji')->toArray();
    }

    public function readingTimeMinutes(): int
    {
        $words = str_word_count(strip_tags($this->excerpt . ' ' . json_encode($this->body)));
        return max(1, (int) ceil($words / 200));
    }
}
