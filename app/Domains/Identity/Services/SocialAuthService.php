<?php

namespace App\Domains\Identity\Services;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class SocialAuthService
{
    public function findOrCreateUser(string $provider, SocialiteUser $socialUser): User
    {
        $social = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->with('user')
            ->first();

        if ($social) {
            $social->update([
                'access_token'    => $socialUser->token,
                'provider_avatar' => $socialUser->getAvatar(),
            ]);
            return $social->user;
        }

        $user = User::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'uuid'              => Str::uuid(),
                'name'              => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                'email'             => $socialUser->getEmail(),
                'username'          => $this->uniqueUsername($socialUser->getNickname() ?? $socialUser->getName()),
                'avatar_url'        => $socialUser->getAvatar(),
                'role'              => 'author',
                'email_verified_at' => now(),
                'password'          => null,
            ]);
        }

        $this->linkAccount($user, $provider, $socialUser);

        if (!$user->avatar_url && $socialUser->getAvatar()) {
            $user->update(['avatar_url' => $socialUser->getAvatar()]);
        }

        return $user;
    }

    public function linkAccount(User $user, string $provider, SocialiteUser $socialUser): SocialAccount
    {
        return SocialAccount::create([
            'user_id'         => $user->id,
            'provider'        => $provider,
            'provider_id'     => $socialUser->getId(),
            'provider_email'  => $socialUser->getEmail(),
            'provider_avatar' => $socialUser->getAvatar(),
            'provider_name'   => $socialUser->getName(),
            'access_token'    => $socialUser->token,
        ]);
    }

    public function uniqueUsername(?string $name): string
    {
        if (!$name) $name = 'user';
        $base = Str::slug(strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name)));
        if (!$base) $base = 'user';
        $base = substr($base, 0, 20);

        $username = $base;
        $i = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $i++;
        }
        return $username;
    }
}
