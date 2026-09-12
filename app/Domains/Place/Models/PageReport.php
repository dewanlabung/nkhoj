<?php

namespace App\Domains\Place\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PageReport extends Model
{
    protected $fillable = [
        'social_page_id', 'reportable_type', 'reportable_id',
        'user_id', 'reason', 'details', 'status', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(SocialPage::class, 'social_page_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
