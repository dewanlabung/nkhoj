<?php

namespace App\Domains\Place\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageAdmin extends Model
{
    protected $fillable = ['social_page_id', 'user_id', 'invited_by', 'role', 'accepted_at'];

    protected $casts = ['accepted_at' => 'datetime'];

    public function page(): BelongsTo
    {
        return $this->belongsTo(SocialPage::class, 'social_page_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isPending(): bool
    {
        return is_null($this->accepted_at);
    }
}
