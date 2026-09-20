<x-email-layout>
    <p style="margin:0 0 24px;font-size:15px;color:#374151;line-height:1.6;">
        Hi {{ $user->name }},
    </p>
    <p style="margin:0 0 24px;font-size:15px;color:#374151;line-height:1.6;">
        Please verify your email address to complete your {{ config('app.name') }} account setup.
        Click the button below — this link expires in 24 hours.
    </p>

    <div style="text-align:center;margin:32px 0;">
        <a href="{{ $verifyUrl }}"
           style="display:inline-block;padding:12px 32px;background:#6366f1;color:#ffffff;font-size:15px;font-weight:600;border-radius:10px;text-decoration:none;">
            Verify Email Address
        </a>
    </div>

    <p style="margin:24px 0 0;font-size:13px;color:#6b7280;line-height:1.6;">
        If the button doesn't work, copy and paste this link into your browser:
    </p>
    <p style="margin:8px 0 0;font-size:12px;color:#6b7280;word-break:break-all;">
        <a href="{{ $verifyUrl }}" style="color:#6366f1;">{{ $verifyUrl }}</a>
    </p>

    <x-slot name="footer">
        If you didn't create an account, you can safely ignore this email.
    </x-slot>
</x-email-layout>
