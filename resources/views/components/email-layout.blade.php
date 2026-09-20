<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ $subject ?? config('app.name') }}</title>
  <style>
    *{box-sizing:border-box}
    body{margin:0;padding:0;background:#f1f3f4;font-family:Roboto,Arial,'Helvetica Neue',sans-serif;-webkit-font-smoothing:antialiased}
    .outer{padding:40px 16px 60px}
    .card{max-width:480px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.12),0 1px 2px rgba(0,0,0,.06);overflow:hidden}
    .brand{padding:24px 32px 20px;text-align:center}
    .brand-name{font-size:22px;font-weight:700;color:#1a73e8;letter-spacing:-.3px;text-decoration:none}
    .divider{height:1px;background:#e8eaed;margin:0}
    .content{padding:32px 32px 24px}
    .icon-wrap{text-align:center;margin-bottom:20px}
    .icon-circle{display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:50%;background:#e8f0fe}
    h1.title{font-size:22px;font-weight:400;color:#202124;margin:0 0 16px;line-height:1.35;text-align:center}
    p.body-text{font-size:14px;color:#5f6368;line-height:1.7;margin:0 0 12px}
    .otp-code{display:block;text-align:center;font-size:36px;font-weight:700;color:#202124;letter-spacing:8px;padding:20px;background:#f8f9fa;border:1px solid #e8eaed;border-radius:8px;margin:20px 0}
    .btn-wrap{text-align:center;margin:24px 0 8px}
    .btn{display:inline-block;padding:13px 32px;background:#1a73e8;color:#fff!important;text-decoration:none;font-size:14px;font-weight:500;border-radius:4px;letter-spacing:.25px}
    .note{font-size:12px;color:#9aa0a6;line-height:1.6;margin:20px 0 0;padding-top:16px;border-top:1px solid #f1f3f4}
    .username-box{text-align:center;margin:20px 0}
    .username-value{display:inline-block;font-size:24px;font-weight:700;color:#202124;padding:12px 32px;background:#f8f9fa;border:1px solid #e8eaed;border-radius:8px;letter-spacing:.5px;font-family:monospace}
    .footer{padding:16px 32px 28px;text-align:center;font-size:11px;color:#9aa0a6;line-height:1.7;border-top:1px solid #f1f3f4}
    .footer a{color:#9aa0a6;text-decoration:none}
    .footer a:hover{text-decoration:underline}
    @media(max-width:560px){.content,.footer{padding-left:20px;padding-right:20px}}
  </style>
</head>
<body>
<div class="outer">
  <div class="card">
    <div class="brand">
      <a href="{{ url('/') }}" class="brand-name">{{ config('app.name') }}</a>
    </div>
    <div class="divider"></div>
    <div class="content">
      {{ $slot }}
    </div>
    <div class="footer">
      {{ $footer ?? "You're receiving this because a password reset was requested for your account." }}<br>
      If you didn't request this, you can safely ignore this email.<br><br>
      <a href="{{ url('/') }}">{{ config('app.name') }}</a>
      &nbsp;·&nbsp;
      <a href="{{ url('/account/recovery') }}">Manage account recovery</a>
    </div>
  </div>
</div>
</body>
</html>
