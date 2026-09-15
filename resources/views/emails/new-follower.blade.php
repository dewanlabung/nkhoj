<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: sans-serif; background: #f9fafb; margin: 0; padding: 24px; }
        .card { background: #fff; border-radius: 12px; max-width: 480px; margin: 0 auto; padding: 32px; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
        .avatar { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; }
        .avatar-placeholder { width: 64px; height: 64px; border-radius: 50%; background: #6366f1; color: #fff; font-size: 24px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
        h2 { margin: 16px 0 4px; font-size: 18px; color: #111827; }
        p { color: #6b7280; font-size: 14px; margin: 0 0 20px; }
        .btn { display: inline-block; background: #6366f1; color: #fff; text-decoration: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; font-size: 14px; }
        .footer { text-align: center; font-size: 12px; color: #9ca3af; margin-top: 24px; }
    </style>
</head>
<body>
<div class="card">
    <p style="font-size:12px;color:#9ca3af;margin:0 0 20px;">Nkhoj</p>
    @if($follower->avatar_url)
    <img src="{{ $follower->avatar_url }}" class="avatar" alt="{{ $follower->name }}">
    @else
    <div class="avatar-placeholder">{{ strtoupper(substr($follower->name, 0, 1)) }}</div>
    @endif
    <h2>{{ $follower->name }} ले तपाईंलाई फलो गर्नुभयो</h2>
    <p>@{{ $follower->username }}{{ $follower->bio ? ' — ' . Str::limit($follower->bio, 80) : '' }}</p>
    <a href="{{ url('/profile/' . $follower->username) }}" class="btn">Profile हेर्नुहोस्</a>
    <div class="footer">
        यो इमेल Nkhoj बाट पठाइएको हो।<br>
        <a href="{{ url('/profile/' . $recipient->username) }}" style="color:#6366f1;">आफ्नो profile</a> मा जानुहोस्।
    </div>
</div>
</body>
</html>
