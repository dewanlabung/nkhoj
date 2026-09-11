<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AccountRecoveryController extends Controller
{
    // ── Public: Forgot Username ──────────────────────────────────────────────

    public function showForgotUsername()
    {
        return view('auth.forgot-username');
    }

    public function sendUsername(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Always show success to prevent email enumeration
        $query = User::where('email', $request->email);
        if (Schema::hasColumn('users', 'recovery_email')) {
            $query->orWhere('recovery_email', $request->email);
        }
        $user = $query->first();

        if ($user) {
            Mail::raw(
                "Hi {$user->name},\n\nYour username on " . config('app.name') . " is: {$user->username}\n\nIf you didn't request this, ignore this email.\n\n— " . config('app.name'),
                function ($m) use ($user, $request) {
                    $m->to($request->email)
                      ->subject('Your ' . config('app.name') . ' username');
                }
            );
        }

        return back()->with('success', 'If that email is registered, we\'ve sent your username to it.');
    }

    // ── Public: Forgot Password (by username or email) ───────────────────────

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendPasswordReset(Request $request)
    {
        $request->validate(['login' => 'required|string|max:200']);

        $login = $request->input('login');
        $query = User::where('email', $login)->orWhere('username', $login);
        if (Schema::hasColumn('users', 'recovery_email')) {
            $query->orWhere('recovery_email', $login);
        }
        $user = $query->first();

        if ($user && Schema::hasColumn('users', 'recovery_token')) {
            $token = Str::random(64);
            $user->update([
                'recovery_token'            => hash('sha256', $token),
                'recovery_token_expires_at' => now()->addHour(),
            ]);

            $resetUrl = url('/reset-password?token=' . $token . '&email=' . urlencode($user->email));

            Mail::raw(
                "Hi {$user->name},\n\nClick the link below to reset your password (expires in 1 hour):\n\n{$resetUrl}\n\nIf you didn't request this, ignore this email.\n\n— " . config('app.name'),
                function ($m) use ($user) {
                    $m->to($user->email)
                      ->subject('Reset your ' . config('app.name') . ' password');
                }
            );
        }

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

        if (!Schema::hasColumn('users', 'recovery_token')) {
            return back()->withErrors(['token' => 'This reset link is invalid or has expired.']);
        }

        $user = User::where('email', $request->email)
                    ->whereNotNull('recovery_token')
                    ->where('recovery_token_expires_at', '>', now())
                    ->first();

        if (!$user || !hash_equals($user->recovery_token, hash('sha256', $request->token))) {
            return back()->withErrors(['token' => 'This reset link is invalid or has expired.']);
        }

        $user->update([
            'password'                  => bcrypt($request->password),
            'recovery_token'            => null,
            'recovery_token_expires_at' => null,
        ]);

        return redirect('/login')->with('success', 'Password reset successfully. Please sign in.');
    }

    // ── Auth: Account Recovery Settings ─────────────────────────────────────

    public function showRecoverySettings()
    {
        return view('account.recovery', ['user' => auth()->user()]);
    }

    public function saveRecoveryEmail(Request $request)
    {
        $request->validate([
            'recovery_email' => 'required|email|max:200|different:email',
        ]);

        $user  = auth()->user();
        $token = Str::random(64);

        $user->update([
            'recovery_email'              => $request->recovery_email,
            'recovery_email_verified_at'  => null,
            'recovery_token'              => hash('sha256', $token),
            'recovery_token_expires_at'   => now()->addHours(24),
        ]);

        $verifyUrl = url('/account/recovery/verify-email?token=' . $token);

        Mail::raw(
            "Hi {$user->name},\n\nVerify this email as your recovery address for " . config('app.name') . ":\n\n{$verifyUrl}\n\nThis link expires in 24 hours.\n\n— " . config('app.name'),
            function ($m) use ($request, $user) {
                $m->to($request->recovery_email)
                  ->subject('Verify your recovery email — ' . config('app.name'));
            }
        );

        return back()->with('success', 'Verification email sent to ' . $request->recovery_email . '. Check your inbox.');
    }

    public function verifyRecoveryEmail(Request $request)
    {
        $user = auth()->user();

        if (!$user->recovery_token || !$user->recovery_token_expires_at?->isFuture()) {
            return redirect('/account/recovery')->with('error', 'Verification link has expired. Please resend.');
        }

        if (!hash_equals($user->recovery_token, hash('sha256', $request->query('token', '')))) {
            return redirect('/account/recovery')->with('error', 'Invalid verification link.');
        }

        $user->update([
            'recovery_email_verified_at' => now(),
            'recovery_token'             => null,
            'recovery_token_expires_at'  => null,
        ]);

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
