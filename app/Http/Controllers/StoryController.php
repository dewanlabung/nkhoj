<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Models\StoryHighlight;
use App\Traits\SavesOptimizedThumbnail;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    use SavesOptimizedThumbnail;

    public function index()
    {
        // Stories grouped by user, most recent first, active only
        $stories = Story::with('user')
            ->active()
            ->latest()
            ->get()
            ->groupBy('user_id');

        return view('stories.index', compact('stories'));
    }

    public function store(Request $request)
    {
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
            $mediaUrl = '/storage/' . $this->saveOptimizedThumbnail($file, 'stories');
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
        return view('stories.show', compact('story'));
    }

    public function destroy(Story $story)
    {
        abort_unless(auth()->id() === $story->user_id, 403);
        $story->delete();
        return back();
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
}
