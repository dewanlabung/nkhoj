<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SocialPage extends Model
{
    protected $table = 'social_pages';

    protected $fillable = [
        'uuid', 'user_id', 'name', 'slug', 'page_type', 'categories',
        'bio', 'avatar_url', 'cover_url', 'website', 'email', 'phone',
        'location', 'followers_count', 'is_verified', 'is_active',
    ];

    protected $casts = [
        'categories'  => 'array',
        'is_verified' => 'boolean',
        'is_active'   => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'social_page_followers', 'social_page_id', 'user_id')
                    ->withTimestamps('created_at', 'created_at');
    }

    public function isFollowedBy(?User $user): bool
    {
        if (!$user) return false;
        return $this->followers()->where('user_id', $user->id)->exists();
    }

    public function isOwnedBy(?User $user): bool
    {
        if (!$user) return false;
        return $this->user_id === $user->id;
    }

    public function getAvatarAttribute(): string
    {
        return $this->avatar_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=e5e7eb&color=6b7280&size=128';
    }

    public function getFirstCategoryAttribute(): string
    {
        $cats = $this->categories ?? [];
        return $cats[0] ?? '';
    }
}
