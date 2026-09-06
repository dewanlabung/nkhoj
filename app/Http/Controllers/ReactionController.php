<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Reaction;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function store(Request $request, int $postId)
    {
        $post  = Post::published()->findOrFail($postId);
        $emoji = in_array($request->emoji, ['heart','laugh','wow','sad','angry','amazing'])
               ? $request->emoji : 'heart';

        if (auth()->check()) {
            $existing = Reaction::where('post_id', $postId)->where('user_id', auth()->id())->first();
            if ($existing) {
                if ($existing->emoji === $emoji) {
                    $existing->delete();
                    $action = 'removed';
                } else {
                    $existing->update(['emoji' => $emoji]);
                    $action = 'changed';
                }
            } else {
                Reaction::create(['post_id' => $postId, 'user_id' => auth()->id(), 'emoji' => $emoji]);
                $action = 'added';
            }
        } else {
            $key = 'guest_' . md5($request->ip() . $request->userAgent());
            $existing = Reaction::where('post_id', $postId)->where('session_key', $key)->first();
            if ($existing) {
                $existing->emoji === $emoji ? $existing->delete() : $existing->update(['emoji' => $emoji]);
                $action = 'toggled';
            } else {
                Reaction::create(['post_id' => $postId, 'session_key' => $key, 'emoji' => $emoji]);
                $action = 'added';
            }
        }

        $counts = Reaction::where('post_id', $postId)
            ->selectRaw('emoji, count(*) as total')
            ->groupBy('emoji')
            ->pluck('total', 'emoji');

        return response()->json(['action' => $action, 'counts' => $counts]);
    }
}
