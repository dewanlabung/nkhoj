<?php

namespace App\Domains\Events\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class EventAttendee extends Model
{
    protected $fillable = ['event_id', 'user_id', 'status'];

    public function event() { return $this->belongsTo(Event::class); }
    public function user()  { return $this->belongsTo(User::class); }
}
