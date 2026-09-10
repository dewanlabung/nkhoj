<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorController extends Controller
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /** Show the 2FA setup page */
    public function setup()
    {
        $user = auth()->user();

        if ($user->two_factor_enabled) {
            return redirect('/account/security')->with('info', '2FA is already enabled.');
        }

        $secret = $this->google2fa->generateSecretKey();
        session(['2fa_setup_secret' => $secret]);

        $qrUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        $renderer = new ImageRenderer(new RendererStyle(200), new SvgImageBackEnd());
        $writer    = new Writer($renderer);
        $qrSvg     = $writer->writeString($qrUrl);

        return view('auth.two-factor-setup', compact('secret', 'qrSvg'));
    }

    /** Confirm and activate 2FA */
    public function confirm(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);
        $secret = session('2fa_setup_secret');

        if (!$secret || !$this->google2fa->verifyKey($secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        auth()->user()->update([
            'two_factor_secret'       => encrypt($secret),
            'two_factor_enabled'      => true,
            'two_factor_confirmed_at' => now(),
        ]);
        session()->forget('2fa_setup_secret');

        return redirect('/account/security')->with('success', 'Two-factor authentication enabled!');
    }

    /** Disable 2FA */
    public function disable(Request $request)
    {
        $request->validate(['password' => 'required|current_password']);

        auth()->user()->update([
            'two_factor_secret'       => null,
            'two_factor_enabled'      => false,
            'two_factor_confirmed_at' => null,
        ]);

        return redirect('/account/security')->with('success', 'Two-factor authentication disabled.');
    }

    /** Show the challenge page during login */
    public function challenge()
    {
        if (!session()->has('2fa_user_id')) {
            return redirect('/login');
        }
        return view('auth.two-factor-challenge');
    }

    /** Verify code during login challenge */
    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);
        $userId = session('2fa_user_id');

        if (!$userId) {
            return redirect('/login');
        }

        $user   = \App\Models\User::findOrFail($userId);
        $secret = decrypt($user->two_factor_secret);

        if (!$this->google2fa->verifyKey($secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        session()->forget('2fa_user_id');
        auth()->login($user, session()->pull('2fa_remember', false));

        return redirect()->intended('/');
    }
}
