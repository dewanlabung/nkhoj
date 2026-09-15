<?php

namespace App\Console\Commands;

use App\Models\DirectMessage;
use Illuminate\Console\Command;

class PurgeExpiredMessages extends Command
{
    protected $signature   = 'messages:purge-expired';
    protected $description = 'Hard-delete direct messages that have passed their expires_at';

    public function handle(): void
    {
        $count = DirectMessage::withTrashed()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->forceDelete();

        $this->info("Purged {$count} expired messages.");
    }
}
