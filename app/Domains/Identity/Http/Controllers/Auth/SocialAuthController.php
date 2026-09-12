<?php

namespace App\Domains\Identity\Http\Controllers\Auth;

use App\Domains\Identity\Services\SocialAuthService;
use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Services\SiteSettingsService;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private const ALLOWED = ['google', 'facebook'];

    public function __construct(private SocialAuthService $socialAuth) {}

    private function isEnabled(string $provider): bool
    {
        $s = app(SiteSettingsService::class)->get();
        $auth = $s['auth'] ?? [];
        return (bool) ($auth["{$provider}_login_enabled"] ?? true);
    }

    public function redirect(string $provider)
    {
        abort_unless(in_array($provider, self::ALLOWED), 404);
        abort_unless($this->isEnabled($provider), 404);
        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        abort_unless(in_array($provider, self::ALLOWED), 404);
        abort_unless($this->isEnabled($provider), 404);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable) {
            return redirect('/login')->with('error', 'OAuth login failed. Please try again.');
        }

        $user = $this->socialAuth->findOrCreateUser($provider, $socialUser);

        if ($user->is_banned) {
            return redirect('/login')->with('error', 'Your account has been suspended.');
        }

        Auth::login($user, true);
        try { LoginHistory::record($user->id, $provider); } catch (\Throwable) {}

        return redirect()->intended('/dashboard');
    }
}
