<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DirectMessage extends Model
{
    use SoftDeletes;

    protected $fillable = ['conversation_id', 'sender_id', 'body', 'media_url', 'is_view_once', 'viewed_at', 'expires_at'];

    protected $casts = ['is_view_once' => 'boolean', 'viewed_at' => 'datetime', 'expires_at' => 'datetime'];

    public function sender()       { return $this->belongsTo(User::class, 'sender_id'); }
    public function conversation() { return $this->belongsTo(Conversation::class); }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isViewableBy(int $userId): bool
    {
        if ($this->is_view_once && $this->viewed_at && $this->sender_id !== $userId) {
            return false;
        }
        return !$this->isExpired();
    }

    public function scopeVisible($query)
    {
        return $query->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }
}
