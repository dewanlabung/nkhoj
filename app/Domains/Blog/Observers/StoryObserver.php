<?php

namespace App\Domains\Blog\Observers;

use App\Domains\Blog\Models\Story;
use Illuminate\Support\Facades\File;

class StoryObserver
{
    public function deleting(Story $story): void
    {
        // Delete physical media file when story is deleted (via any method, not just controller)
        if ($story->media_url && !$story->post_id) {
            $this->deleteStoryMedia($story->media_url);
        }
    }

    private function deleteStoryMedia(string $mediaUrl): void
    {
        // Handle /uploads/ URLs (images processed via saveOptimizedThumbnail)
        if (str_starts_with($mediaUrl, '/uploads/')) {
            $path = public_path(ltrim($mediaUrl, '/'));
            if (File::exists($path)) {
                try {
                    File::delete($path);
                } catch (\Exception $e) {
                    \Log::warning("Failed to delete story media: {$path}", ['error' => $e->getMessage()]);
                }
            }
        }
        // Handle /storage/ URLs (videos uploaded via store())
        elseif (str_contains($mediaUrl, '/storage/')) {
            $path = storage_path('app/public/' . ltrim(str_replace('/storage/', '', $mediaUrl), '/'));
            if (File::exists($path)) {
                try {
                    File::delete($path);
                } catch (\Exception $e) {
                    \Log::warning("Failed to delete story media: {$path}", ['error' => $e->getMessage()]);
                }
            }
        }
    }
}
