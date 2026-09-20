<x-email-layout>
  <x-slot:footer>You're receiving this because a password reset code was requested for your account.</x-slot:footer>

  <div class="icon-wrap">
    <div class="icon-circle">
      <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#1a73e8" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
    </div>
  </div>

  <h1 class="title">Your verification code</h1>

  <p class="body-text">Hi {{ $user->name }},</p>
  <p class="body-text">Use the code below to reset your {{ config('app.name') }} password. This code expires in <strong>15 minutes</strong>.</p>

  <div class="otp-code">{{ $code }}</div>

  <p class="body-text" style="text-align:center;font-size:13px;color:#9aa0a6;">Never share this code with anyone.</p>
</x-email-layout>
