<?php

namespace App\Domains\Account\Http\Controllers\Api\V1;

use App\Domains\Account\Services\AccountService;
use App\Domains\Account\Services\FollowService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function __construct(
        private AccountService $account,
        private FollowService  $follow,
    ) {}

    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'id'         => $user->id,
            'name'       => $user->name,
            'username'   => $user->username,
            'avatar_url' => $user->avatar_url,
            'email'      => $user->email,
            'role'       => $user->role,
            'bio'        => $user->bio,
            'website'    => $user->website,
            'created_at' => $user->created_at,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'name'       => 'sometimes|required|string|max:100',
            'first_name' => 'nullable|string|max:60',
            'last_name'  => 'nullable|string|max:60',
            'username'   => 'nullable|string|max:40|alpha_dash|unique:users,username,' . $user->id,
            'bio'        => 'nullable|string|max:500',
            'website'    => 'nullable|url|max:200',
        ]);

        $this->account->updatePersonalInfo($user, $request->all());

        return response()->json(['message' => 'Profile updated.', 'user' => $user->fresh()]);
    }

    public function updateAvatar(Request $request)
    {
        $request->validate(['avatar' => 'required|image|max:2048']);
        $url = $this->account->updateAvatar($request->user(), $request->file('avatar'));
        return response()->json(['avatar_url' => $url]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        $this->account->changePassword($request->user(), $request->current_password, $request->password);
        return response()->json(['message' => 'Password updated.']);
    }

    public function profile(string $username)
    {
        $user = User::where('username', $username)->firstOrFail();
        $isFollowing = auth('sanctum')->check()
            ? auth('sanctum')->user()->following()->where('following_id', $user->id)->exists()
            : false;

        return response()->json([
            'id'              => $user->id,
            'name'            => $user->name,
            'username'        => $user->username,
            'avatar_url'      => $user->avatar_url,
            'bio'             => $user->bio,
            'website'         => $user->website,
            'follower_count'  => $user->followers()->count(),
            'following_count' => $user->following()->count(),
            'post_count'      => $user->posts()->published()->count(),
            'is_following'    => $isFollowing,
        ]);
    }

    public function follow(Request $request, int $userId)
    {
        if ($request->user()->id === $userId) {
            return response()->json(['error' => 'Cannot follow yourself'], 422);
        }

        $action = $this->follow->toggle($request->user(), $userId);
        $count  = $this->follow->followerCount($userId);

        return response()->json(compact('action', 'count'));
    }
}
