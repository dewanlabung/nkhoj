<?php

namespace App\Domains\Place\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PageBlock extends Model
{
    protected $fillable = ['social_page_id', 'blocked_user_id'];

    public function page()
    {
        return $this->belongsTo(SocialPage::class, 'social_page_id');
    }

    public function blockedUser()
    {
        return $this->belongsTo(User::class, 'blocked_user_id');
    }
}
