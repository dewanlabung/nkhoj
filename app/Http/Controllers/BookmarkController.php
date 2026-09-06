<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Post;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function toggle(int $postId)
    {
        $post = Post::findOrFail($postId);
        $userId = auth()->id();

        $existing = Bookmark::where('user_id', $userId)->where('post_id', $postId)->first();

        if ($existing) {
            $existing->delete();
            $action = 'removed';
        } else {
            Bookmark::create(['user_id' => $userId, 'post_id' => $postId]);
            $action = 'saved';
        }

        $count = Bookmark::where('post_id', $postId)->count();
        return response()->json(['action' => $action, 'count' => $count]);
    }
}
