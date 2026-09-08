<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private const ALLOWED = ['google', 'facebook'];

    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, self::ALLOWED), 404);
        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        abort_unless(in_array($provider, self::ALLOWED), 404);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable) {
            return redirect('/login')->with('error', 'OAuth login failed. Please try again.');
        }

        // Find existing social account link
        $social = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->with('user')
            ->first();

        if ($social) {
            // Update token and avatar
            $social->update([
                'access_token'    => $socialUser->token,
                'provider_avatar' => $socialUser->getAvatar(),
            ]);
            $user = $social->user;
        } else {
            // Try to match by email
            $user = User::where('email', $socialUser->getEmail())->first();

            if (!$user) {
                // Create new user
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

            // Link social account
            SocialAccount::create([
                'user_id'          => $user->id,
                'provider'         => $provider,
                'provider_id'      => $socialUser->getId(),
                'provider_email'   => $socialUser->getEmail(),
                'provider_avatar'  => $socialUser->getAvatar(),
                'provider_name'    => $socialUser->getName(),
                'access_token'     => $socialUser->token,
            ]);

            // Sync avatar if user doesn't have one
            if (!$user->avatar_url && $socialUser->getAvatar()) {
                $user->update(['avatar_url' => $socialUser->getAvatar()]);
            }
        }

        if ($user->is_banned) {
            return redirect('/login')->with('error', 'Your account has been suspended.');
        }

        Auth::login($user, true);
        try { LoginHistory::record($user->id, $provider); } catch (\Throwable) {}

        return redirect()->intended('/dashboard');
    }

    private function uniqueUsername(?string $name): string
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
