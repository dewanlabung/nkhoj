<!DOCTYPE html>
<html lang="ne">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $subject }}</title>
<style>
  body { margin:0; padding:0; background:#f4f5f7; font-family: -apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif; }
  .wrap { max-width:560px; margin:32px auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.1); }
  .header { background:#2563eb; padding:24px 32px; }
  .header a { color:#fff; text-decoration:none; font-size:20px; font-weight:700; }
  .body { padding:32px; color:#374151; font-size:15px; line-height:1.6; }
  .body p { margin:0 0 16px; }
  .btn { display:inline-block; margin-top:8px; padding:12px 24px; background:#2563eb; color:#fff !important; border-radius:6px; text-decoration:none; font-weight:600; font-size:14px; }
  .footer { padding:16px 32px; background:#f9fafb; border-top:1px solid #e5e7eb; font-size:12px; color:#9ca3af; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <a href="{{ url('/') }}">{{ config('app.name') }}</a>
  </div>
  <div class="body">
    <p>नमस्ते {{ $user->name }},</p>
    <p>{{ $body }}</p>
    @if($url)
    <a href="{{ $url }}" class="btn">{{ __('View') }}</a>
    @endif
  </div>
  <div class="footer">
    यो इमेल स्वचालित रूपमा पठाइएको हो। जवाफ नगर्नुहोस्।<br>
    <a href="{{ url('/account/notifications') }}" style="color:#6b7280;">सूचना सेटिङ बदल्नुहोस्</a>
  </div>
</div>
</body>
</html>
