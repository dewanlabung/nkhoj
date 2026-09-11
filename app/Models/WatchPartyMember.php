<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchPartyMember extends Model
{
    public $timestamps = false;
    protected $fillable = ['watch_party_id', 'user_id', 'joined_at'];
    protected $casts = ['joined_at' => 'datetime'];
    public function user() { return $this->belongsTo(User::class); }
}
