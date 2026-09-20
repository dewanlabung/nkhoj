<x-email-layout>
  <x-slot:footer>You're receiving this because a recovery email was added to a {{ config('app.name') }} account.</x-slot:footer>

  <div class="icon-wrap">
    <div class="icon-circle">
      <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#1a73e8" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </div>
  </div>

  <h1 class="title">Verify your recovery email</h1>

  <p class="body-text">Hi {{ $user->name }},</p>
  <p class="body-text">This email address was added as a recovery email for your {{ config('app.name') }} account. Click the button below to verify it. This link expires in <strong>24 hours</strong>.</p>

  <div class="btn-wrap">
    <a href="{{ $verifyUrl }}" class="btn">Verify Recovery Email</a>
  </div>

  <p class="note">
    If the button doesn't work, copy and paste this link into your browser:<br>
    <a href="{{ $verifyUrl }}" style="color:#1a73e8;word-break:break-all;font-size:12px;">{{ $verifyUrl }}</a>
  </p>
</x-email-layout>
