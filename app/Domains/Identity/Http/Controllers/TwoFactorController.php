<?php

namespace App\Domains\Identity\Http\Controllers;

use App\Domains\Identity\Services\TwoFactorService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    public function __construct(private TwoFactorService $twoFactor) {}

    public function setup()
    {
        $user = auth()->user();

        if ($user->two_factor_enabled) {
            return redirect('/account/security')->with('info', '2FA is already enabled.');
        }

        ['secret' => $secret, 'qrSvg' => $qrSvg] = $this->twoFactor->generateSetup($user);
        session(['2fa_setup_secret' => $secret]);

        return view('auth.two-factor-setup', compact('secret', 'qrSvg'));
    }

    public function confirm(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);
        $secret = session('2fa_setup_secret');

        if (!$secret || !$this->twoFactor->confirm(auth()->user(), $secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        session()->forget('2fa_setup_secret');
        return redirect('/account/security')->with('success', 'Two-factor authentication enabled!');
    }

    public function disable(Request $request)
    {
        $request->validate(['password' => 'required|current_password']);
        $this->twoFactor->disable(auth()->user());
        return redirect('/account/security')->with('success', 'Two-factor authentication disabled.');
    }

    public function challenge()
    {
        if (!session()->has('2fa_user_id')) {
            return redirect('/login');
        }
        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);
        $userId = session('2fa_user_id');

        if (!$userId) {
            return redirect('/login');
        }

        $user = \App\Models\User::findOrFail($userId);

        if (!$this->twoFactor->verifyChallenge($user, $request->code)) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        session()->forget('2fa_user_id');
        auth()->login($user, session()->pull('2fa_remember', false));

        return redirect()->intended('/');
    }
}
