<?php

namespace App\Domains\Identity\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationLinkMail;
use App\Models\LoggingAnalytics\OtpCode;
use App\Models\UserEngagement\User;
use App\Services\SiteSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    private function method(): string
    {
        return app(SiteSettingsService::class)->get()['auth']['email_verification_method'] ?? 'both';
    }

    public function show(Request $request)
    {
        $userId = $request->session()->get('pending_verification_user_id');
        if (!$userId) {
            return redirect('/login')->with('info', 'Please sign in to access this page.');
        }

        $user = User::find($userId);
        if (!$user || $user->email_verified_at) {
            $request->session()->forget('pending_verification_user_id');
            return redirect('/login')->with('success', 'Your email is already verified. Please sign in.');
        }

        return view('auth.verify-email', [
            'maskedEmail' => \Illuminate\Support\Str::mask($user->email, '*', 2, -strlen(strstr($user->email, '@'))),
            'method'      => $this->method(),
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $userId = $request->session()->get('pending_verification_user_id');
        if (!$userId) {
            return redirect('/login');
        }

        $request->validate(['code' => 'required|string|size:6']);

        $user = User::findOrFail($userId);

        $otp = OtpCode::where('user_id', $user->id)
            ->where('type', 'email_verification')
            ->first();

        if (!$otp || $otp->code !== $request->code || $otp->isExpired()) {
            return back()->withErrors(['code' => 'Invalid or expired code. Please try again.']);
        }

        $otp->delete();
        $user->update(['email_verified_at' => now()]);
        $request->session()->forget('pending_verification_user_id');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/dashboard')->with('success', 'Email verified. Welcome to ' . config('app.name') . '!');
    }

    public function resend(Request $request)
    {
        $userId = $request->session()->get('pending_verification_user_id');
        if (!$userId) {
            return redirect('/login');
        }

        $user = User::findOrFail($userId);
        $method = $this->method();

        if (in_array($method, ['both', 'otp_only'])) {
            $user->sendEmailVerificationNotification();
        }

        if (in_array($method, ['both', 'link_only'])) {
            $url = URL::temporarySignedRoute(
                'verify-email.link',
                now()->addHours(24),
                ['id' => $user->id, 'hash' => sha1($user->email)]
            );
            Mail::to($user->email)->send(new EmailVerificationLinkMail($user, $url));
        }

        return back()->with('success', 'A new verification ' . ($method === 'link_only' ? 'link' : 'code') . ' has been sent.');
    }

    public function verifyLink(Request $request, int $id, string $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals(sha1($user->email), $hash)) {
            abort(403, 'Invalid verification link.');
        }

        if ($user->email_verified_at) {
            $request->session()->forget('pending_verification_user_id');
            Auth::login($user);
            return redirect('/dashboard')->with('info', 'Email already verified.');
        }

        $user->update(['email_verified_at' => now()]);
        OtpCode::where('user_id', $user->id)->where('type', 'email_verification')->delete();
        $request->session()->forget('pending_verification_user_id');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/dashboard')->with('success', 'Email verified. Welcome to ' . config('app.name') . '!');
    }
}
