<?php

namespace App\Domains\Blog\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Domains\Blog\Models\Post;
use App\Models\MediaContent\Story;
use App\Models\MediaContent\StoryHighlight;
use App\Traits\SavesOptimizedThumbnail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class StoryController extends Controller
{
    use SavesOptimizedThumbnail;

    public function index()
    {
        // Stories grouped by user, most recent first, active only
        $stories = Story::with(['user', 'post'])
            ->active()
            ->latest()
            ->get()
            ->groupBy('user_id');

        return view('stories.index', compact('stories'));
    }

    public function store(Request $request)
    {
        // Blog-post-linked story mode: pull featured image from existing post
        if ($request->filled('post_id')) {
            $request->validate([
                'post_id' => 'required|exists:posts,id',
                'caption' => 'nullable|string|max:500',
            ]);

            $post = Post::findOrFail($request->post_id);

            Story::create([
                'user_id'    => auth()->id(),
                'post_id'    => $post->id,
                'media_url'  => $post->thumbnail_url ?? '',
                'media_type' => 'image',
                'caption'    => $request->caption ?? $post->title,
                'expires_at' => now()->addHours(24),
            ]);

            return back()->with('success', 'Story from blog post created!');
        }

        $request->validate([
            'media'   => 'required|file|mimes:jpeg,jpg,png,gif,webp,mp4,mov,webm|max:51200',
            'caption' => 'nullable|string|max:500',
        ]);

        $file      = $request->file('media');
        $isVideo   = str_starts_with($file->getMimeType(), 'video');
        $mediaType = $isVideo ? 'video' : 'image';

        if ($isVideo) {
            $path = $file->store('stories', 'public');
            $mediaUrl = '/storage/' . $path;
        } else {
            $mediaUrl = $this->saveOptimizedThumbnail($file, 'stories');
        }

        Story::create([
            'user_id'    => auth()->id(),
            'media_url'  => $mediaUrl,
            'media_type' => $mediaType,
            'caption'    => $request->caption,
            'expires_at' => now()->addHours(24),
        ]);

        return back()->with('success', 'Story posted!');
    }

    public function show(Story $story)
    {
        abort_if($story->isExpired(), 404);

        if ($story->post_id) {
            $story->load('post');
            if ($story->post) {
                return redirect('/posts/' . $story->post->slug);
            }
        }

        return view('stories.show', compact('story'));
    }

    public function destroy(Story $story)
    {
        $user = auth()->user();
        abort_unless($user->id === $story->user_id || $user->isAdmin(), 403);

        // Delete physical media file if it's a direct upload (not linked to a blog post)
        if ($story->media_url && !$story->post_id) {
            $this->deleteStoryMedia($story->media_url);
        }

        $story->delete();
        return redirect('/stories')->with('success', 'Story deleted.');
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

    // Highlight management
    public function storeHighlight(Request $request)
    {
        $request->validate(['title' => 'required|string|max:100']);

        $highlight = StoryHighlight::create([
            'user_id' => auth()->id(),
            'title'   => $request->title,
        ]);

        if ($request->story_ids) {
            foreach ((array)$request->story_ids as $order => $storyId) {
                $highlight->stories()->attach($storyId, ['order' => $order]);
            }
        }

        return back()->with('success', 'Highlight created.');
    }

    public function addToHighlight(Request $request, StoryHighlight $highlight)
    {
        abort_unless(auth()->id() === $highlight->user_id, 403);
        $request->validate(['story_id' => 'required|exists:stories,id']);

        $highlight->stories()->syncWithoutDetaching([$request->story_id => ['order' => $highlight->stories()->count()]]);
        return back();
    }

    public function destroyHighlight(StoryHighlight $highlight)
    {
        abort_unless(auth()->id() === $highlight->user_id, 403);
        $highlight->delete();
        return back();
    }

    public function highlightsIndex()
    {
        $highlights = StoryHighlight::with(['stories' => fn($q) => $q->active()])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        $availableStories = Story::where('user_id', auth()->id())
            ->active()
            ->latest()
            ->get();

        return view('stories.highlights', compact('highlights', 'availableStories'));
    }
}
