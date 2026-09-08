<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'uuid', 'user_id', 'title', 'slug', 'description',
        'category', 'event_type', 'starts_at', 'ends_at',
        'venue', 'location', 'organizer',
        'registration_url', 'ticket_price', 'thumbnail_url',
        'is_published', 'is_featured',
    ];

    protected $casts = [
        'starts_at'    => 'datetime',
        'ends_at'      => 'datetime',
        'ticket_price' => 'decimal:2',
        'is_published' => 'boolean',
        'is_featured'  => 'boolean',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attendees()
    {
        return $this->hasMany(EventAttendee::class);
    }

    public function getIsFreeAttribute(): bool
    {
        return is_null($this->ticket_price) || $this->ticket_price == 0;
    }

    public function isAttendingBy(?User $user, string $status = 'going'): bool
    {
        if (!$user) return false;
        return $this->attendees()->where('user_id', $user->id)->where('status', $status)->exists();
    }
}
