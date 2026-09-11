<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['uuid'];

    public function participants() { return $this->hasMany(ConversationParticipant::class); }
    public function messages()     { return $this->hasMany(DirectMessage::class)->latest(); }

    public function otherUser(int $myId): ?User
    {
        $p = $this->participants()->where('user_id', '!=', $myId)->with('user')->first();
        return $p?->user;
    }

    public function unreadCount(int $userId): int
    {
        $part = $this->participants()->where('user_id', $userId)->first();
        if (!$part) return 0;
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where(fn($q) => $q->whereNull('last_read_at')->orWhere('created_at', '>', $part->last_read_at))
            ->count();
    }

    public static function between(int $a, int $b): ?self
    {
        return self::whereHas('participants', fn($q) => $q->where('user_id', $a))
            ->whereHas('participants', fn($q) => $q->where('user_id', $b))
            ->first();
    }
}
