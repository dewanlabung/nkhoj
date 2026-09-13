<?php

namespace App\Events;

use App\Models\User;
use App\Models\Ban;

class UserBanned
{
    public function __construct(public User $user, public Ban $ban)
    {
    }
}
