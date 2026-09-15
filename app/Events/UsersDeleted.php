<?php

namespace App\Events;

use Illuminate\Database\Eloquent\Collection;

class UsersDeleted
{
    public function __construct(public Collection $users)
    {
    }
}
