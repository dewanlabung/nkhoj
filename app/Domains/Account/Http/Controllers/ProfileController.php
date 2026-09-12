<?php

namespace App\Domains\Account\Http\Controllers;

use App\Domains\Account\Services\FollowService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(private FollowService $followService) {}

    public function show(string $username)
    {
        $user = User::where('username', $username)->firstOrFail();

        $posts = $user->posts()
            ->with(['category', 'tags'])
            ->published()
            ->latest('published_at')
            ->paginate(12);

        $followerCount  = $user->followers()->count();
        $followingCount = $user->following()->count();
        $totalViews     = $user->posts()->published()->sum('view_count');
        $isFollowing    = auth()->check()
            ? auth()->user()->following()->where('following_id', $user->id)->exists()
            : false;

        $followerUsers  = $user->followers()->select('users.id', 'users.name', 'users.username', 'users.avatar_url')->limit(6)->get();
        $followingUsers = $user->following()->select('users.id', 'users.name', 'users.username', 'users.avatar_url')->limit(6)->get();

        $categoryBreakdown = $user->posts()
            ->published()
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(fn($g) => ['name' => $g->first()->category?->name_ne ?? $g->first()->category?->name_en ?? 'Uncategorized', 'slug' => $g->first()->category?->slug, 'count' => $g->count()])
            ->sortByDesc('count')
            ->values();

        return view('profile.show', compact(
            'user', 'posts', 'followerCount', 'followingCount', 'totalViews',
            'isFollowing', 'followerUsers', 'followingUsers', 'categoryBreakdown'
        ));
    }

    public function follow(Request $request, int $userId)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Login required'], 401);
        }

        if (auth()->id() === $userId) {
            return response()->json(['error' => 'Cannot follow yourself'], 422);
        }

        $action = $this->followService->toggle(auth()->user(), $userId);
        $count  = $this->followService->followerCount($userId);

        return response()->json(compact('action', 'count'));
    }
}
