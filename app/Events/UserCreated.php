<?php

namespace App\Events;

use App\Models\UserEngagement\User;

class UserCreated
{
    public function __construct(public User $user, public array $data = [])
    {
    }
}
