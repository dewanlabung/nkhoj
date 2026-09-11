<?php

namespace App\Console\Commands;

use App\Models\Story;
use Illuminate\Console\Command;

class PurgeExpiredStories extends Command
{
    protected $signature   = 'stories:purge-expired';
    protected $description = 'Delete stories that have passed their 24-hour expiry';

    public function handle(): void
    {
        $count = Story::where('expires_at', '<', now())->delete();
        $this->info("Purged {$count} expired stories.");
    }
}
