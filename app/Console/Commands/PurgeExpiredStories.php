<?php

namespace App\Console\Commands;

use App\Models\MediaContent\Story;
use Illuminate\Console\Command;

class PurgeExpiredStories extends Command
{
    protected $signature   = 'stories:purge-expired';
    protected $description = 'Delete stories that have passed their 24-hour expiry';

    public function handle(): void
    {
        $expired = Story::where('expires_at', '<', now())->get();

        $deleted = 0;
        foreach ($expired as $story) {
            // Only delete files we uploaded (not post thumbnail URLs)
            if ($story->media_url && str_starts_with($story->media_url, '/uploads/')) {
                $path = public_path(ltrim($story->media_url, '/'));
                if (file_exists($path)) {
                    @unlink($path);
                }
            }
            $story->delete();
            $deleted++;
        }

        $this->info("Purged {$deleted} expired stories.");
    }
}
