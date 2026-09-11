<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VirtualGift extends Model
{
    protected $fillable = ['sender_id', 'recipient_id', 'gift_type', 'coins', 'context_type', 'context_id', 'message'];

    public function sender()    { return $this->belongsTo(User::class, 'sender_id'); }
    public function recipient() { return $this->belongsTo(User::class, 'recipient_id'); }

    public static array $types = [
        'rose'    => ['emoji' => '🌹', 'coins' => 10,  'label' => 'Rose'],
        'star'    => ['emoji' => '⭐', 'coins' => 25,  'label' => 'Star'],
        'crown'   => ['emoji' => '👑', 'coins' => 50,  'label' => 'Crown'],
        'trophy'  => ['emoji' => '🏆', 'coins' => 100, 'label' => 'Trophy'],
        'diamond' => ['emoji' => '💎', 'coins' => 250, 'label' => 'Diamond'],
    ];
}
