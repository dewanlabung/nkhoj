<?php

namespace App\Models\UserEngagement;

use App\Core\Traits\HasBans;
use App\Core\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\LoginHistory;
use App\Models\SocialAccount;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasBans, HasRoles;

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
        'two_factor_recovery_codes',
        'deletion_requested_at',
        'data_export_token',
        'data_export_requested_at',
        'data_export_ready_at',
        'recovery_email',
        'recovery_email_verified_at',
        'recovery_token',
        'recovery_token_expires_at',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

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
            'two_factor_enabled'          => 'boolean',
            'two_factor_confirmed_at'    => 'datetime',
            'two_factor_recovery_codes'  => 'array',
            'deletion_requested_at'        => 'datetime',
            'data_export_requested_at'     => 'datetime',
            'data_export_ready_at'         => 'datetime',
            'recovery_email_verified_at'   => 'datetime',
            'recovery_token_expires_at'    => 'datetime',
        ];
    }

    public function displayName(): string
    {
        if ($this->username) return $this->username;
        $full = trim($this->first_name . ' ' . $this->last_name);
        if ($full) return $full;
        return $this->name;
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

    public function userSessions()
    {
        return $this->hasMany(\App\Models\UserSession::class)->orderByDesc('last_active_at');
    }

    public function deviceTokens()
    {
        return $this->hasMany(\App\Models\DeviceToken::class);
    }

    public function notificationPreferences()
    {
        return $this->hasMany(\App\Models\NotificationPreference::class);
    }

    public function otpCodes()
    {
        return $this->hasMany(\App\Models\OtpCode::class);
    }

    public function emailVerificationOtpIsValid(string $code): bool
    {
        $otp = $this->otpCodes()
            ->where('type', 'email_verification')
            ->first();

        return $otp && $otp->code === $code && !$otp->isExpired();
    }

    public function sendEmailVerificationNotification(): void
    {
        $otp = \App\Models\OtpCode::createForEmailVerification($this);
        $this->notify(new \App\Notifications\VerifyEmailWithOtpNotification($otp->code));
    }


    public function isPendingDeletion(): bool
    {
        return $this->deletion_requested_at !== null;
    }

    public function completionScore(): array
    {
        $steps = [
            'avatar'   => ['label' => 'Profile photo',        'done' => (bool) $this->avatar_url,          'url' => '/account/personal-info'],
            'bio'      => ['label' => 'Bio',                   'done' => (bool) $this->bio,                 'url' => '/account/personal-info'],
            'verified' => ['label' => 'Email verified',        'done' => (bool) $this->email_verified_at,   'url' => '/account/personal-info'],
            'twofa'    => ['label' => 'Two-factor auth',       'done' => (bool) $this->two_factor_enabled,  'url' => '/account/security'],
            'post'     => ['label' => 'Published first post',  'done' => $this->posts()->published()->exists(), 'url' => '/dashboard/new'],
            'website'  => ['label' => 'Website / social link', 'done' => (bool) $this->website,             'url' => '/account/personal-info'],
        ];
        $done    = collect($steps)->where('done', true)->count();
        $percent = (int) round($done / count($steps) * 100);
        return ['percent' => $percent, 'steps' => $steps, 'done' => $done, 'total' => count($steps)];
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

    /**
     * Scope: users who have opted into at least one channel for a given notification type.
     * Mirrors BeDesk's whereNeedsNotificationFor() pattern.
     */
    public function scopeWantsNotification($query, string $type, string $channel = 'in_app')
    {
        return $query->whereHas('notificationPreferences', function ($q) use ($type, $channel) {
            $q->where('type', $type)->where($channel, true);
        })->orWhereDoesntHave('notificationPreferences', function ($q) use ($type) {
            $q->where('type', $type);
        });
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

    /**
     * Get user's notification subscription preferences
     */
    public function notificationSubscriptions()
    {
        return $this->hasMany(\App\Models\Notifications\NotificationSubscription::class);
    }

}
