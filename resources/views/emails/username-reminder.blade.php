<x-email-layout>
  <x-slot:footer>You're receiving this because a username reminder was requested for this email address.</x-slot:footer>

  <div class="icon-wrap">
    <div class="icon-circle">
      <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#1a73e8" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
    </div>
  </div>

  <h1 class="title">Your username</h1>

  <p class="body-text">Hi {{ $user->name }},</p>
  <p class="body-text">Here is the username associated with your {{ config('app.name') }} account:</p>

  <div class="username-box">
    <span class="username-value">{{ $user->username }}</span>
  </div>

  <p class="body-text" style="text-align:center">
    <a href="{{ url('/login') }}" style="color:#1a73e8;font-size:14px;font-weight:500;text-decoration:none">Sign in to your account →</a>
  </p>
</x-email-layout>
