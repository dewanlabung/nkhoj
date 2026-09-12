<?php

namespace App\Domains\Identity\Http\Controllers\Auth;

use App\Domains\Identity\Services\AccountRecoveryService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountRecoveryController extends Controller
{
    public function __construct(private AccountRecoveryService $recovery) {}

    public function showForgotUsername()
    {
        return view('auth.forgot-username');
    }

    public function sendUsername(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $this->recovery->sendUsernameReminder($request->email);
        return back()->with('success', "If that email is registered, we've sent your username to it.");
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendPasswordReset(Request $request)
    {
        $request->validate(['login' => 'required|string|max:200']);
        $this->recovery->sendPasswordResetLink($request->login);
        return back()->with('success', 'If that account exists, a reset link has been sent to the registered email.');
    }

    public function showResetPassword(Request $request)
    {
        return view('auth.reset-password', [
            'token' => $request->query('token'),
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $ok = $this->recovery->resetPassword($request->email, $request->token, $request->password);

        if (!$ok) {
            return back()->withErrors(['token' => 'This reset link is invalid or has expired.']);
        }

        return redirect('/login')->with('success', 'Password reset successfully. Please sign in.');
    }

    public function showRecoverySettings()
    {
        return view('account.recovery', ['user' => auth()->user()]);
    }

    public function saveRecoveryEmail(Request $request)
    {
        $request->validate([
            'recovery_email' => 'required|email|max:200|different:email',
        ]);

        $this->recovery->saveRecoveryEmail(auth()->user(), $request->recovery_email);
        return back()->with('success', 'Verification email sent to ' . $request->recovery_email . '. Check your inbox.');
    }

    public function verifyRecoveryEmail(Request $request)
    {
        $ok = $this->recovery->verifyRecoveryEmail(auth()->user(), $request->query('token', ''));

        if (!$ok) {
            return redirect('/account/recovery')->with('error', 'Verification link has expired or is invalid.');
        }

        return redirect('/account/recovery')->with('success', 'Recovery email verified successfully.');
    }

    public function removeRecoveryEmail()
    {
        auth()->user()->update([
            'recovery_email'             => null,
            'recovery_email_verified_at' => null,
        ]);

        return back()->with('success', 'Recovery email removed.');
    }
}
