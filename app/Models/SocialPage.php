<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialPage extends Model
{
    protected $table = 'social_pages';

    protected $fillable = [
        'uuid', 'user_id', 'name', 'slug', 'page_type', 'categories',
        'bio', 'avatar_url', 'cover_url', 'website', 'email', 'phone',
        'location', 'lat', 'lng', 'business_hours',
        'followers_count', 'rating_avg', 'reviews_count', 'views_count',
        'is_verified', 'is_active',
    ];

    protected $casts = [
        'categories'     => 'array',
        'business_hours' => 'array',
        'is_verified'    => 'boolean',
        'is_active'      => 'boolean',
        'lat'            => 'float',
        'lng'            => 'float',
        'rating_avg'     => 'float',
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

    public function posts(): HasMany
    {
        return $this->hasMany(PagePost::class)->latest();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(PageReview::class)->latest();
    }

    public function viewLogs(): HasMany
    {
        return $this->hasMany(PageViewLog::class, 'social_page_id');
    }

    public function verificationRequests(): HasMany
    {
        return $this->hasMany(PageVerificationRequest::class);
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

    public function isOpenNow(): ?bool
    {
        $hours = $this->business_hours;
        if (!$hours) return null;

        $now  = Carbon::now('Asia/Kathmandu');
        $day  = strtolower($now->format('D')); // mon, tue, wed...
        $slot = $hours[$day] ?? null;
        if (!$slot || ($slot['closed'] ?? false)) return false;

        $open  = Carbon::createFromTimeString($slot['open'] ?? '00:00', 'Asia/Kathmandu');
        $close = Carbon::createFromTimeString($slot['close'] ?? '23:59', 'Asia/Kathmandu');

        return $now->between($open, $close);
    }
}
