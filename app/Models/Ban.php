<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Ban extends Model
{
    protected $fillable = ['bannable_type', 'bannable_id', 'comment', 'expired_at', 'created_by_id'];

    protected function casts(): array
    {
        return ['expired_at' => 'datetime'];
    }

    public function bannable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isPermanent(): bool
    {
        return $this->expired_at === null;
    }

    public function isActive(): bool
    {
        return $this->isPermanent() || $this->expired_at->isFuture();
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
        });
    }
}
