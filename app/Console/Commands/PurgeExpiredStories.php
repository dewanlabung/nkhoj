<?php

namespace App\Console\Commands;

use App\Models\MediaContent\Story;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PurgeExpiredStories extends Command
{
    protected $signature   = 'stories:purge-expired';
    protected $description = 'Delete stories that have passed their 24-hour expiry and clean up media files';

    public function handle(): void
    {
        $expired = Story::where('expires_at', '<', now())->get();

        $deleted = 0;
        foreach ($expired as $story) {
            $this->deleteStoryMedia($story);
            $story->delete();
            $deleted++;
        }

        $this->info("Purged {$deleted} expired stories.");
    }

    private function deleteStoryMedia(Story $story): void
    {
        if (!$story->media_url || $story->post_id) {
            return;
        }

        $path = null;

        // Handle /storage/ URLs (direct file uploads via store())
        if (str_contains($story->media_url, '/storage/')) {
            $path = public_path(str_replace('/storage/', 'storage/', $story->media_url));
        }
        // Handle /uploads/ URLs (direct file uploads)
        elseif (str_starts_with($story->media_url, '/uploads/')) {
            $path = public_path(ltrim($story->media_url, '/'));
        }

        if ($path && File::exists($path)) {
            try {
                File::delete($path);
            } catch (\Exception $e) {
                $this->warn("Failed to delete media file: {$path}");
            }
        }
    }
}
