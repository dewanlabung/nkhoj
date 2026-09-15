<?php

namespace App\Core\Events;

use App\Models\Memberships\Ban;
use App\Models\UserEngagement\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserBanned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public Ban $ban,
        public bool $isPermanent = false
    ) {}
}
