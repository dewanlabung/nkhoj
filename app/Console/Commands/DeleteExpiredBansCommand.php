<?php

namespace App\Console\Commands;

use App\Models\Memberships\Ban;
use Illuminate\Console\Command;

class DeleteExpiredBansCommand extends Command
{
    protected $signature   = 'bans:delete-expired';
    protected $description = 'Purge ban records whose expiry date has passed';

    public function handle(): int
    {
        $deleted = Ban::whereNotNull('expired_at')->where('expired_at', '<', now())->delete();
        $this->info("Deleted {$deleted} expired ban(s).");
        return self::SUCCESS;
    }
}
