<?php

namespace App\Domains\Account\Services;

use App\Jobs\CreateNotification;
use App\Mail\NewFollowerMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class FollowService
{
    public function toggle(User $actor, int $targetId): string
    {
        $target   = User::findOrFail($targetId);
        $existing = $actor->following()->where('following_id', $targetId)->exists();

        if ($existing) {
            $actor->following()->detach($targetId);
            return 'unfollowed';
        }

        $actor->following()->attach($targetId);

        CreateNotification::dispatch($targetId, 'follow', [
            'follower' => $actor->name,
            'username' => $actor->username,
        ]);

        if ($target->email) {
            Mail::to($target->email)->queue(new NewFollowerMail($target, $actor));
        }

        return 'followed';
    }

    public function followerCount(int $userId): int
    {
        return User::findOrFail($userId)->followers()->count();
    }
}
