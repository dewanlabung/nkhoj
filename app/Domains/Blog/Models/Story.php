<?php

namespace App\Domains\Blog\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    protected $fillable = ['user_id', 'media_url', 'media_type', 'caption', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function highlights()
    {
        return $this->belongsToMany(StoryHighlight::class, 'story_highlight_items', 'story_id', 'highlight_id')
                    ->withPivot('order');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('expires_at', '>', now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
