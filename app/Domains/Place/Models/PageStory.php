<?php

namespace App\Domains\Place\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PageStory extends Model
{
    protected $fillable = ['social_page_id', 'user_id', 'image_url', 'caption', 'bg_color', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];

    public function page()
    {
        return $this->belongsTo(SocialPage::class, 'social_page_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }
}
