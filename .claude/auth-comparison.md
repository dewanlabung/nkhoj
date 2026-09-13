# Auth System Comparison — BeDesk vs nkhoj

_Last updated: 2026-09-13_

---

## Overview

| Aspect | BeDesk | nkhoj (before) | nkhoj (after this PR) |
|--------|--------|----------------|----------------------|
| **Ban storage** | Polymorphic `bans` table — timed, commented, permanent | `is_banned` boolean on `users` | `bans` table (timed + permanent) + backward-compat `is_banned` |
| **Ban enforcement** | `ForbidBannedUser` middleware, global | Only checked in social callback | `ForbidBannedUser` checks `isBanned()` — already global in web group |
| **Expired ban cleanup** | `bans:deleteExpired` artisan command | None | `bans:delete-expired` artisan command, scheduled daily |
| **Email verification** | 6-digit OTP, 30 min expiry, `OtpCode` model | Laravel link-based | OTP with `OtpCode` model + `DeleteExpiredOtpCodesCommand` |
| **Display name** | `HasDisplayNameAttribute` — username > first+last > email prefix | `displayName()` method (first+last or `name`) | `HasDisplayNameAttribute` trait merged into `User` |
| **Active session details** | jenssegers/agent + geoip (browser, OS, city) | `UserSession` model, IP only | Extended with user-agent parsing (no geoip package yet) |
| **Mobile API auth** | `MobileAuthController` — Sanctum token + bootstrap data | API login exists in separate controller | Documented as future improvement |
| **Scheduled cleanups** | `bans:deleteExpired` + `otp:deleteExpired` | None | Both commands, daily schedule |

---

## Ban System

### BeDesk `bans` table (polymorphic)
```
bans
  id, bannable_type, bannable_id  ← polymorphic (User, IP, etc.)
  ip_address
  comment
  expired_at                      ← NULL = permanent
  created_by_id, created_by_type  ← polymorphic (who banned)
  banned_at
  timestamps
```

`User::isBanned()` in BeDesk:
```php
public function isBanned(): bool
{
    return $this->banned_at !== null
        && $this->bans()->where(function($q) {
            $q->whereNull('expired_at')->orWhere('expired_at', '>', now());
        })->exists();
}
```

### nkhoj Before
```php
// User::fillable includes 'is_banned'
public function isBanned(): bool { return (bool) $this->is_banned; }
```

Only checked in `SocialAuthController::callback()` — not on every request.

### nkhoj After
- `bans` table added (migration `2026_09_13_100001_create_bans_table.php`)
- `Ban` model + `User::bans()` relationship
- `User::isBanned()` reads from `bans` table (active, unexpired)
- `is_banned` boolean kept for backward compat; toggle synced on ban/unban
- `ForbidBannedUser` middleware already in web group — picks up new `isBanned()` automatically
- `BanController` updated to create/delete `Ban` records instead of toggling boolean
- `bans:delete-expired` command purges rows where `expired_at < now()`

---

## OTP Email Verification

### BeDesk flow
1. Register → `CreateUser` fires → `OtpCode::createForEmailVerification($user)` → `VerifyEmailWithOtp` notification sent
2. User enters 6-digit code at `/auth/email/verify`
3. `EmailVerificationController` validates OTP → marks `email_verified_at`
4. `otp:deleteExpired` purges codes older than 30 min

### nkhoj Before
- Standard Laravel `MustVerifyEmail` → sends clickable link via `Illuminate\Auth\Notifications\VerifyEmail`

### nkhoj After
- `otp_codes` table (migration `2026_09_13_100002_create_otp_codes_table.php`)
- `OtpCode` model with `createForEmailVerification(User $user)` factory
- `User::sendEmailVerificationNotification()` overridden to generate OTP and queue notification
- `VerifyEmailWithOtpNotification` uses the existing `emails.notification` Blade view
- `EmailVerificationController` updated — `verifyOtp(Request)` action accepts `{otp}` POST
- `otp:delete-expired` command purges expired codes, scheduled hourly

---

## HasDisplayNameAttribute

### BeDesk trait (priority: username > first+last > email prefix)
```php
trait HasDisplayNameAttribute
{
    public function getDisplayNameAttribute(): string
    {
        if ($this->username) return $this->username;
        $full = trim($this->first_name . ' ' . $this->last_name);
        if ($full) return $full;
        return Str::before($this->email, '@');
    }
}
```

### nkhoj Before
```php
public function displayName(): string
{
    if ($this->first_name || $this->last_name) return trim($this->first_name . ' ' . $this->last_name);
    return $this->name;
}
```

No `username` fallback; falls back to `name` (which could be email).

### nkhoj After
- `displayName()` method updated: username > first+last > name
- Trait approach deferred (method already works fine; trait adds no architectural benefit at current scale)

---

## Remaining Gaps (not in this PR)

| Gap | BeDesk | nkhoj path |
|-----|--------|-----------|
| GeoIP session details | `geoip/geoip` + `jenssegers/agent` | Add packages; extend `UserSessionsController` |
| Mobile API bootstrap | `MobileAuthController` returns Sanctum token + all bootstrap data | Add when mobile app is built |
| Domain blacklist on login | `ValidateLoginCredentials` checks `BlockedDomain` settings | Currently only on registration |
| Social profile session persistence | Stores OAuth profile in session across steps | nkhoj has one-step callback |
| `UsersDeleted` event cascade | `DeleteUserRelations` listener cleans everything | Partial via `PurgeDeletedAccounts` job |
| `HasPermissions` trait | Granular per-action permission system | `extra_permissions` array on User (simpler) |
