<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\LoginHistory;
use App\Models\SocialAccount;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'uuid',
        'name',
        'username',
        'email',
        'password',
        'avatar_url',
        'cover_url',
        'role',
        'bio',
        'website',
        'social_links',
        'is_banned',
        'last_seen_at',
        'email_verified_at',
        'ai_credits_used',
        'ai_credits_reset_at',
        'first_name',
        'last_name',
        'balance',
        'reward_system',
        'profile_view_count',
        'extra_permissions',
        'two_factor_secret',
        'two_factor_enabled',
        'two_factor_confirmed_at',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret'];

    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'last_seen_at'       => 'datetime',
            'ai_credits_reset_at'=> 'datetime',
            'is_banned'          => 'boolean',
            'reward_system'      => 'boolean',
            'social_links'       => 'array',
            'extra_permissions'  => 'array',
            'password'           => 'hashed',
            'balance'                  => 'decimal:2',
            'two_factor_enabled'       => 'boolean',
            'two_factor_confirmed_at'  => 'datetime',
        ];
    }

    public function displayName(): string
    {
        if ($this->first_name || $this->last_name) {
            return trim($this->first_name . ' ' . $this->last_name);
        }
        return $this->name;
    }

    public function roleLabel(): string
    {
        return match($this->role) {
            'admin'    => 'Super Admin',
            'editor'   => 'Editor',
            'reporter' => 'Author',
            default    => 'Member',
        };
    }

    public function roleBadgeClass(): string
    {
        return match($this->role) {
            'admin'    => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            'editor'   => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
            'reporter' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            default    => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
        };
    }

    public static function socialPlatforms(): array
    {
        return [
            'twitter'   => 'X (Twitter)',
            'instagram' => 'Instagram',
            'facebook'  => 'Facebook',
            'youtube'   => 'YouTube',
            'whatsapp'  => 'WhatsApp',
            'linkedin'  => 'LinkedIn',
            'tiktok'    => 'TikTok',
            'pinterest' => 'Pinterest',
            'snapchat'  => 'Snapchat',
            'telegram'  => 'Telegram',
            'discord'   => 'Discord',
            'reddit'    => 'Reddit',
            'bluesky'   => 'Bluesky',
            'twitch'    => 'Twitch',
            'website'   => 'Personal Website URL',
        ];
    }

    public function lastSeenLabel(): string
    {
        if (!$this->last_seen_at) return 'Never';
        $diff = now()->diffInMinutes($this->last_seen_at);
        if ($diff < 5)   return 'Just Now';
        if ($diff < 60)  return $diff . 'm ago';
        if ($diff < 1440) return round($diff / 60) . 'h ago';
        return $this->last_seen_at->format('d M Y');
    }

    public function getSocialLink(string $platform): string
    {
        return $this->social_links[$platform] ?? '';
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'following_id', 'follower_id');
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'following_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function hasSaved(string $type, int $id): bool
    {
        return $this->bookmarks()
            ->where('bookmarkable_type', $type)
            ->where('bookmarkable_id', $id)
            ->exists();
    }

    public function nkhojNotifications()
    {
        return $this->hasMany(Notification::class)->latest();
    }

    public function questions()
    {
        return $this->hasMany(\App\Models\Question::class);
    }

    public function answers()
    {
        return $this->hasMany(\App\Models\Answer::class);
    }

    public function recipeRatings()
    {
        return $this->hasMany(\App\Models\RecipeRating::class);
    }

    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class)->latest('created_at');
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function unreadNotificationCount(): int
    {
        return $this->nkhojNotifications()->whereNull('read_at')->count();
    }

    public function isEditor(): bool
    {
        return in_array($this->role, ['editor', 'admin']);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMod(): bool
    {
        return in_array($this->role, ['admin', 'editor']);
    }

    public function subscriptions()
    {
        return $this->hasMany(\App\Models\Subscription::class);
    }

    public function activeSubscription(): ?\App\Models\Subscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->with('plan')
            ->latest()
            ->first();
    }

    public function hasPro(): bool
    {
        if ($this->isAdmin() || $this->isEditor()) return true;
        return $this->activeSubscription() !== null;
    }
}
