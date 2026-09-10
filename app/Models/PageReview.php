<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageReview extends Model
{
    protected $fillable = ['social_page_id', 'user_id', 'rating', 'body'];

    public function page()  { return $this->belongsTo(SocialPage::class, 'social_page_id'); }
    public function user()  { return $this->belongsTo(User::class); }
}
