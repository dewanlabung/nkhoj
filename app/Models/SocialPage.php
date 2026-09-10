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

    const CATEGORIES = [
        'Arts & Entertainment', 'Automotive', 'Business', 'Clothing & Fashion',
        'Community', 'Education', 'Finance', 'Food & Restaurant', 'Government',
        'Health & Medical', 'Home & Garden', 'Legal', 'Media & News',
        'Music', 'Non-profit', 'Real Estate', 'Religion', 'Science',
        'Shopping', 'Sports', 'Technology', 'Tourism & Travel',
    ];

    protected $fillable = [
        'uuid', 'user_id', 'name', 'slug', 'username', 'page_type', 'categories',
        'bio', 'announcement', 'highlights',
        'action_button_type', 'action_button_text', 'action_button_url',
        'donation_url', 'donation_label',
        'allow_tagging', 'is_archived',
        'avatar_url', 'cover_url', 'website', 'email', 'phone', 'social_links',
        'location', 'lat', 'lng', 'business_hours',
        'followers_count', 'rating_avg', 'reviews_count', 'views_count',
        'is_verified', 'is_active', 'status', 'disabled_reason', 'pinned_post_id',
    ];

    protected $casts = [
        'categories'     => 'array',
        'business_hours' => 'array',
        'social_links'   => 'array',
        'highlights'     => 'array',
        'is_verified'    => 'boolean',
        'is_active'      => 'boolean',
        'is_archived'    => 'boolean',
        'allow_tagging'  => 'boolean',
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

    public function admins(): HasMany
    {
        return $this->hasMany(PageAdmin::class, 'social_page_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(PageReport::class, 'social_page_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(PageProduct::class)->orderBy('sort_order');
    }

    public function qna(): HasMany
    {
        return $this->hasMany(PageQna::class)->where('is_visible', true)->latest();
    }

    public function pinnedPost(): BelongsTo
    {
        return $this->belongsTo(PagePost::class, 'pinned_post_id');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(PageBlock::class, 'social_page_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(PageActivityLog::class, 'social_page_id')->latest();
    }

    public function stories(): HasMany
    {
        return $this->hasMany(PageStory::class, 'social_page_id')->active()->latest();
    }

    public function notificationPrefs(): HasMany
    {
        return $this->hasMany(PageNotificationPref::class, 'social_page_id');
    }

    public function isBlockedUser(?User $user): bool
    {
        if (! $user) return false;
        return $this->blocks()->where('blocked_user_id', $user->id)->exists();
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

    // true if user is owner OR accepted admin/moderator/editor
    public function isManagedBy(?User $user): bool
    {
        if (!$user) return false;
        if ($this->user_id === $user->id) return true;
        return $this->admins()
                    ->where('user_id', $user->id)
                    ->whereNotNull('accepted_at')
                    ->exists();
    }

    // role for the user: 'owner', 'admin', 'moderator', 'editor', or null
    public function roleFor(?User $user): ?string
    {
        if (!$user) return null;
        if ($this->user_id === $user->id) return 'owner';
        $admin = $this->admins()->where('user_id', $user->id)->whereNotNull('accepted_at')->first();
        return $admin?->role;
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
        $day  = strtolower($now->format('D'));
        $slot = $hours[$day] ?? null;
        if (!$slot || ($slot['closed'] ?? false)) return false;

        $open  = Carbon::createFromTimeString($slot['open'] ?? '00:00', 'Asia/Kathmandu');
        $close = Carbon::createFromTimeString($slot['close'] ?? '23:59', 'Asia/Kathmandu');

        return $now->between($open, $close);
    }
}
