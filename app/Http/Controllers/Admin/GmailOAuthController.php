<?php

namespace App\Http\Controllers\Admin;

use App\Services\Mail\GmailClient;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Laravel\Socialite\Facades\Socialite;

class GmailOAuthController extends Controller
{
    public function redirect()
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);

        return Socialite::driver('google')
            ->scopes([
                'https://www.googleapis.com/auth/gmail.send',
            ])
            ->with([
                'access_type' => 'offline',
                'prompt'      => 'consent select_account',
                'redirect_uri' => url('/admin/gmail/callback'),
            ])
            ->redirect();
    }

    public function callback(Request $request)
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);

        if ($request->has('error')) {
            return redirect('/admin/email-settings')
                ->with('error', 'Gmail connection cancelled: ' . $request->get('error'));
        }

        try {
            $profile = Socialite::driver('google')
                ->with(['redirect_uri' => url('/admin/gmail/callback')])
                ->user();

            File::ensureDirectoryExists(dirname(GmailClient::tokenPath()));
            File::put(GmailClient::tokenPath(), json_encode([
                'access_token'  => $profile->token,
                'refresh_token' => $profile->refreshToken,
                'created'       => now()->timestamp,
                'expires_in'    => $profile->expiresIn,
                'email'         => $profile->email,
            ]));

            return redirect('/admin/email-settings')
                ->with('success', "Gmail connected as {$profile->email}. Set 'Mail Service' to 'Gmail API' and save.");

        } catch (\Throwable $e) {
            return redirect('/admin/email-settings')
                ->with('error', 'Gmail connection failed: ' . $e->getMessage());
        }
    }

    public function disconnect()
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);

        if (file_exists(GmailClient::tokenPath())) {
            File::delete(GmailClient::tokenPath());
        }

        return redirect('/admin/email-settings')->with('success', 'Gmail disconnected.');
    }
}
