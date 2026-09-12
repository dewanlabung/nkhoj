<?php

namespace App\Domains\Place\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PageNotificationPref extends Model
{
    protected $fillable = ['user_id', 'social_page_id', 'notify_posts', 'notify_events', 'notify_announcements'];

    protected $casts = [
        'notify_posts'         => 'boolean',
        'notify_events'        => 'boolean',
        'notify_announcements' => 'boolean',
    ];

    public function page()
    {
        return $this->belongsTo(SocialPage::class, 'social_page_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
