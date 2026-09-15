<?php

namespace App\Events;

use App\Models\UserEngagement\User;
use App\Models\Memberships\Ban;

class UserBanned
{
    public function __construct(public User $user, public Ban $ban)
    {
    }
}
