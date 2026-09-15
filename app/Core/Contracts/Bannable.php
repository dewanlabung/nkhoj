<?php

namespace App\Core\Contracts;

interface Bannable
{
    public function isBanned(): bool;
    public function activeBan();
    public function bans();
}
