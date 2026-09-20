<?php

namespace App\Domains\Identity\Http\Controllers\Auth;

use App\Domains\Identity\Services\AccountRecoveryService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountRecoveryController extends Controller
{
    public function __construct(private AccountRecoveryService $recovery) {}

    // ──────────────────────────────────────────────────────────────────
    //  MULTI-STEP PASSWORD RESET
    // ──────────────────────────────────────────────────────────────────

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /** Step 1: find account */
    public function findAccount(Request $request)
    {
        $request->validate(['login' => 'required|string|max:200']);

        $user = $this->recovery->findByLogin($request->login);

        if (!$user) {
            return back()->withErrors(['login' => 'No account found with that email or username.']);
        }

        $request->session()->put('recovery_user_id', $user->id);
        $request->session()->forget(['recovery_method', 'recovery_otp_destination']);

        return redirect('/forgot-password/choose');
    }

    /** Step 2: show account card + choose method */
    public function showChooseMethod(Request $request)
    {
        $userId = $request->session()->get('recovery_user_id');
        if (!$userId) return redirect('/forgot-password');

        $user = \App\Models\UserEngagement\User::find($userId);
        if (!$user) return redirect('/forgot-password');

        return view('auth.forgot-password-choose', compact('user'));
    }

    /** Step 2 POST: send OTP via chosen channel */
    public function chooseMethod(Request $request)
    {
        $userId = $request->session()->get('recovery_user_id');
        if (!$userId) return redirect('/forgot-password');

        $user = \App\Models\UserEngagement\User::find($userId);
        if (!$user) return redirect('/forgot-password');

        $method = $request->input('method');

        // Validate the chosen method is available for this user
        $allowed = ['otp_primary'];
        if ($user->recovery_email && $user->recovery_email_verified_at) {
            $allowed[] = 'otp_recovery';
        }

        if (!in_array($method, $allowed, true)) {
            return back()->withErrors(['method' => 'Please choose a valid option.']);
        }

        $destination = $method === 'otp_recovery' ? $user->recovery_email : $user->email;

        $this->recovery->sendPasswordResetOtp($user, $destination);

        $request->session()->put('recovery_method', $method);
        $request->session()->put('recovery_otp_destination', $destination);

        return redirect('/forgot-password/verify');
    }

    /** Step 3: enter OTP */
    public function showVerify(Request $request)
    {
        $userId = $request->session()->get('recovery_user_id');
        if (!$userId || !$request->session()->get('recovery_method')) {
            return redirect('/forgot-password');
        }

        $destination = $request->session()->get('recovery_otp_destination', '');

        return view('auth.forgot-password-verify', [
            'maskedDestination' => $this->maskEmail($destination),
        ]);
    }

    /** Step 3 POST: verify OTP → issue reset token */
    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $userId = $request->session()->get('recovery_user_id');
        if (!$userId) return redirect('/forgot-password');

        $user = \App\Models\UserEngagement\User::find($userId);
        if (!$user) return redirect('/forgot-password');

        if (!$this->recovery->verifyPasswordResetOtp($user->id, $request->code)) {
            return back()->withErrors(['code' => 'Incorrect or expired code. Please try again.']);
        }

        $request->session()->forget(['recovery_user_id', 'recovery_method', 'recovery_otp_destination']);

        $token = $this->recovery->issueResetToken($user);

        return redirect('/reset-password?token=' . $token . '&email=' . urlencode($user->email));
    }

    /** Resend OTP */
    public function resendOtp(Request $request)
    {
        $userId      = $request->session()->get('recovery_user_id');
        $method      = $request->session()->get('recovery_method');
        $destination = $request->session()->get('recovery_otp_destination');

        if (!$userId || !$method || !$destination) {
            return redirect('/forgot-password');
        }

        $user = \App\Models\UserEngagement\User::find($userId);
        if (!$user) return redirect('/forgot-password');

        $this->recovery->sendPasswordResetOtp($user, $destination);

        return back()->with('success', 'A new code has been sent.');
    }

    // ──────────────────────────────────────────────────────────────────
    //  RESET PASSWORD (existing token-link flow, still used by step 3)
    // ──────────────────────────────────────────────────────────────────

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

    // ──────────────────────────────────────────────────────────────────
    //  FORGOT USERNAME
    // ──────────────────────────────────────────────────────────────────

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

    // ──────────────────────────────────────────────────────────────────
    //  ACCOUNT RECOVERY SETTINGS (authenticated)
    // ──────────────────────────────────────────────────────────────────

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

    // ──────────────────────────────────────────────────────────────────

    private function maskEmail(string $email): string
    {
        if (!str_contains($email, '@')) return $email;
        [$local, $domain] = explode('@', $email, 2);
        $masked = substr($local, 0, min(2, strlen($local))) . str_repeat('*', max(0, strlen($local) - 2));
        return $masked . '@' . $domain;
    }
}
