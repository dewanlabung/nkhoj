<?php

namespace App\Http\Controllers;

use App\Models\Reel;
use App\Models\ReelLike;
use App\Models\ReelComment;
use App\Traits\SavesOptimizedThumbnail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReelController extends Controller
{
    use SavesOptimizedThumbnail;

    public function index()
    {
        $reels = Reel::where('is_published', true)
            ->with('user')
            ->latest()
            ->paginate(10);
        return view('reels.index', compact('reels'));
    }

    public function create()
    {
        return view('reels.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'nullable|string|max:200',
            'description' => 'nullable|string|max:500',
            'video'       => 'required|file|mimetypes:video/mp4,video/webm,video/quicktime|max:102400',
            'thumbnail'   => 'nullable|image|max:4096',
        ]);

        $video = $request->file('video');
        $videoName = time() . '_' . Str::random(8) . '.' . $video->getClientOriginalExtension();
        $dir = public_path('uploads/reels');
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $video->move($dir, $videoName);
        $videoUrl = '/uploads/reels/' . $videoName;

        $thumbnailUrl = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailUrl = $this->saveOptimizedThumbnail($request->file('thumbnail'), 'reels/thumbs');
        }

        $reel = Reel::create([
            'uuid'         => Str::uuid(),
            'user_id'      => auth()->id(),
            'title'        => $data['title'] ?? null,
            'description'  => $data['description'] ?? null,
            'video_url'    => $videoUrl,
            'thumbnail_url'=> $thumbnailUrl,
            'is_published' => true,
        ]);

        return redirect('/reels')->with('success', 'Reel uploaded!');
    }

    public function like(Reel $reel)
    {
        $userId = auth()->id();
        $existing = ReelLike::where('reel_id', $reel->id)->where('user_id', $userId)->first();
        if ($existing) {
            $existing->delete();
            $reel->decrement('likes_count');
            $liked = false;
        } else {
            ReelLike::create(['reel_id' => $reel->id, 'user_id' => $userId]);
            $reel->increment('likes_count');
            $liked = true;
        }
        return response()->json(['liked' => $liked, 'count' => $reel->fresh()->likes_count]);
    }

    public function comment(Request $request, Reel $reel)
    {
        $data = $request->validate(['body' => 'required|string|max:500']);
        $comment = ReelComment::create(['reel_id' => $reel->id, 'user_id' => auth()->id(), 'body' => $data['body']]);
        $reel->increment('comments_count');
        $comment->load('user');
        return response()->json([
            'id'   => $comment->id,
            'name' => $comment->user->name,
            'body' => $comment->body,
            'time' => $comment->created_at->diffForHumans(),
        ]);
    }

    public function comments(Reel $reel)
    {
        $comments = ReelComment::where('reel_id', $reel->id)->with('user')->latest()->limit(30)->get();
        return response()->json($comments->map(fn($c) => [
            'id'   => $c->id,
            'name' => $c->user->name,
            'body' => $c->body,
            'time' => $c->created_at->diffForHumans(),
        ]));
    }
}
