@component('mail::message')
# SMTP Configuration Test

Your mail server configuration is working correctly! ✓

This is a test email sent from {{ config('app.name') }} to verify that your SMTP settings are properly configured.

**Timestamp:** {{ now()->format('Y-m-d H:i:s') }}

You can now use the email system for sending notifications and other messages.

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent
