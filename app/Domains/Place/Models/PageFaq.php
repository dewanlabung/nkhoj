<?php

namespace App\Domains\Place\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageFaq extends Model
{
    protected $table = 'page_faqs';

    protected $fillable = ['social_page_id', 'question', 'answer', 'display_order'];

    public function page(): BelongsTo
    {
        return $this->belongsTo(SocialPage::class, 'social_page_id');
    }
}
