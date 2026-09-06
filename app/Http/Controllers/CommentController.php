<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Notification;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $post = Post::where('slug', $slug)->published()->firstOrFail();

        $data = $request->validate([
            'body'       => 'required|string|max:2000',
            'parent_id'  => 'nullable|integer|exists:comments,id',
            'guest_name' => 'nullable|string|max:100',
        ]);

        $comment = $post->comments()->create([
            'user_id'    => auth()->id(),
            'parent_id'  => $data['parent_id'] ?? null,
            'body'       => $data['body'],
            'guest_name' => auth()->check() ? null : ($data['guest_name'] ?? 'अतिथि'),
            'is_approved' => true,
        ]);

        // notify post author
        if ($post->author_id && $post->author_id !== auth()->id()) {
            Notification::create([
                'user_id' => $post->author_id,
                'type'    => 'comment',
                'data'    => [
                    'commenter' => auth()->user()?->name ?? ($data['guest_name'] ?? 'अतिथि'),
                    'post_title' => $post->title,
                    'post_slug'  => $post->slug,
                    'excerpt'    => \Str::limit($data['body'], 80),
                ],
            ]);
        }

        if ($request->wantsJson()) {
            $comment->load('author');
            return response()->json(['comment' => $comment, 'display_name' => $comment->displayName()]);
        }

        return back()->with('success', 'टिप्पणी थपियो!');
    }

    public function destroy(int $id)
    {
        $comment = Comment::findOrFail($id);
        if (auth()->id() !== $comment->user_id && !auth()->user()?->isAdmin()) {
            abort(403);
        }
        $comment->delete();

        if (request()->wantsJson()) {
            return response()->json(['deleted' => true]);
        }
        return back();
    }
}
