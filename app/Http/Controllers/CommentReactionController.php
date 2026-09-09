<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentReaction;
use Illuminate\Http\Request;

class CommentReactionController extends Controller
{
    private const ALLOWED = ['👍', '❤️', '😂', '😮', '😢', '😡'];

    public function toggle(Request $request, Comment $comment)
    {
        $emoji = $request->input('emoji');
        if (!in_array($emoji, self::ALLOWED)) {
            return response()->json(['error' => 'Invalid emoji'], 422);
        }

        $userId = auth()->id();
        $session = $userId ? null : substr(md5($request->ip() . $request->userAgent()), 0, 64);

        $existing = CommentReaction::where('comment_id', $comment->id)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when(!$userId, fn($q) => $q->where('session_key', $session))
            ->first();

        if ($existing) {
            if ($existing->emoji === $emoji) {
                $existing->delete();
            } else {
                $existing->update(['emoji' => $emoji]);
            }
        } else {
            CommentReaction::create([
                'comment_id'  => $comment->id,
                'user_id'     => $userId,
                'session_key' => $session,
                'emoji'       => $emoji,
            ]);
        }

        return response()->json([
            'counts' => $comment->reactionCounts(),
        ]);
    }
}
