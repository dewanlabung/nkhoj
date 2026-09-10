<?php

namespace App\Http\Controllers;

use App\Jobs\CreateNotification;
use App\Mail\NewFollowerMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ProfileController extends Controller
{
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

        // Followers/following with avatars (limited to 6 for display)
        $followerUsers  = $user->followers()->select('users.id', 'users.name', 'users.username', 'users.avatar_url')->limit(6)->get();
        $followingUsers = $user->following()->select('users.id', 'users.name', 'users.username', 'users.avatar_url')->limit(6)->get();

        // Posts by category (for sidebar)
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
        $target = User::findOrFail($userId);

        if (!auth()->check()) {
            return response()->json(['error' => 'Login required'], 401);
        }

        if (auth()->id() === $userId) {
            return response()->json(['error' => 'Cannot follow yourself'], 422);
        }

        $existing = auth()->user()->following()->where('following_id', $userId)->exists();

        if ($existing) {
            auth()->user()->following()->detach($userId);
            $action = 'unfollowed';
        } else {
            auth()->user()->following()->attach($userId);
            $action = 'followed';
            CreateNotification::dispatch($userId, 'follow', [
                'follower' => auth()->user()->name,
                'username' => auth()->user()->username,
            ]);
            if ($target->email) {
                Mail::to($target->email)->queue(new NewFollowerMail($target, auth()->user()));
            }
        }

        return response()->json(['action' => $action, 'count' => $target->followers()->count()]);
    }
}
