<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchPartyMessage extends Model
{
    protected $fillable = ['watch_party_id', 'user_id', 'body', 'at_seconds'];
    public function user() { return $this->belongsTo(User::class); }
}
