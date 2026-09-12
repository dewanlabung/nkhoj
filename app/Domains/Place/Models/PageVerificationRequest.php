<?php

namespace App\Domains\Place\Models;

use Illuminate\Database\Eloquent\Model;

class PageVerificationRequest extends Model
{
    protected $fillable = ['social_page_id', 'reason', 'status', 'reviewed_at', 'reviewed_by', 'admin_notes'];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function page() { return $this->belongsTo(SocialPage::class, 'social_page_id'); }
}
