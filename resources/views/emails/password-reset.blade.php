<x-email-layout>
  <x-slot:footer>You're receiving this because a password reset was requested for your account.</x-slot:footer>

  <div class="icon-wrap">
    <div class="icon-circle">
      <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#1a73e8" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
    </div>
  </div>

  <h1 class="title">Reset your password</h1>

  <p class="body-text">Hi {{ $user->name }},</p>
  <p class="body-text">Someone requested a password reset for your {{ config('app.name') }} account. Click the button below to choose a new password. This link expires in <strong>1 hour</strong>.</p>

  <div class="btn-wrap">
    <a href="{{ $resetUrl }}" class="btn">Reset Password</a>
  </div>

  <p class="note">
    If the button doesn't work, copy and paste this link into your browser:<br>
    <a href="{{ $resetUrl }}" style="color:#1a73e8;word-break:break-all;font-size:12px;">{{ $resetUrl }}</a>
  </p>
</x-email-layout>
