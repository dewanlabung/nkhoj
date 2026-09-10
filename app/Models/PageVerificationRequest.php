<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageVerificationRequest extends Model
{
    protected $fillable = ['social_page_id', 'reason', 'status'];

    public function page() { return $this->belongsTo(SocialPage::class, 'social_page_id'); }
}
