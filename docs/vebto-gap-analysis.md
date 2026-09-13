# Vebto BeDesk Gap Analysis — nkhoj vs Common Package

**Reference**: `.claude/common/` (Vebto BeDesk common package)
**Last updated**: 2026-09-13
**Branch**: `claude/homepage-card-search-xmt1fe`

---

## Status Legend
- ✅ Implemented
- 🔄 Partial / adapted differently
- ❌ Missing — needs implementing
- ⏳ Planned / in progress

---

## 1. Security & Auth Middleware

| Feature | Vebto reference | nkhoj status | Notes |
|---|---|---|---|
| ForbidBannedUser middleware | `Auth/Middleware/ForbidBannedUser.php` | ✅ Added | Logs out banned users immediately |
| OptionalAuthenticate middleware | `Auth/Middleware/OptionalAuthenticate.php` | ✅ Added | Suppresses auth exceptions for guest routes |
| VerifyApiAccessMiddleware | `Auth/Middleware/VerifyApiAccessMiddleware.php` | 🔄 Adapted | nkhoj uses role-based check; no `guestRole` service |
| CheckIpBan middleware | n/a (nkhoj-custom) | ✅ Exists | `app/Http/Middleware/CheckIpBan.php` |
| SecurityHeaders middleware | n/a (nkhoj-custom) | ✅ Exists | `app/Http/Middleware/SecurityHeaders.php` |
| TrackLastSeen middleware | n/a (nkhoj-custom) | ✅ Exists | `app/Http/Middleware/TrackLastSeen.php` |

---

## 2. User Ban System

| Feature | Vebto reference | nkhoj status | Notes |
|---|---|---|---|
| Ban model | `Auth/Ban.php` | ❌ Missing | Vebto uses separate `bans` table with expiry + comment |
| `bans()` relationship on User | `Auth/BaseUser.php` | ❌ Missing | nkhoj uses flat `is_banned` boolean column |
| `isBanned()` method on User | `Auth/BaseUser.php` | ✅ Added | Checks `is_banned` field |
| BanController (API) | `Auth/Controllers/BanController.php` | 🔄 Partial | nkhoj admin sets `is_banned` via user edit form; no dedicated API endpoint |
| DeleteExpiredBansCommand | `Auth/Commands/` | ❌ Missing | Only relevant if per-user ban expiry is implemented |

**Recommendation**: Add a `bans` table and `Ban` model for timed bans with reason/comment. The current flat `is_banned` field only supports permanent bans.

---

## 3. Email / SMTP

| Feature | Vebto reference | nkhoj status | Notes |
|---|---|---|---|
| SMTP settings save to `.env` | `Settings/DotEnvEditor.php` | ✅ Implemented | `app/Services/DotEnvEditor.php` |
| `config:clear` after save | `Settings/SettingsController.php` | ✅ Implemented | Called in `SettingsController::updateSmtpSettings()` |
| `mail.default` applied at runtime | `Settings/Settings.php` | ✅ Fixed | `AppServiceProvider::boot()` sets `mail.default => 'smtp'` |
| OutgoingMailCredentialsValidator | `Settings/Validators/MailCredentials/` | ✅ Added | `app/Services/OutgoingMailCredentialsValidator.php` — sends test email to admin |
| MailCredentialsMailable | `Settings/Validators/MailCredentials/` | ✅ Added | `app/Mail/MailTestMailable.php` |

---

## 4. OTP / Email Verification Flow

| Feature | Vebto reference | nkhoj status | Notes |
|---|---|---|---|
| OtpCode model | `Auth/OtpCode.php` | ❌ Missing | Vebto uses DB-backed OTP codes for email verification |
| EmailVerificationController | `Auth/Controllers/EmailVerificationController.php` | ❌ Missing | Vebto sends OTP, verifies by code |
| DeleteExpiredOtpCodesCommand | `Auth/Commands/` | ❌ Missing | Cron cleanup for expired OTP rows |
| Current email verification | n/a | 🔄 Vague | nkhoj uses standard Laravel link-based verification |

**Recommendation**: Low priority unless OTP-based verification is a product requirement.

---

## 5. Notifications

| Feature | Vebto reference | nkhoj status | Notes |
|---|---|---|---|
| NotificationSubscription model | `Notifications/` | ❌ Missing | Per-user per-channel notification preferences |
| GetsUserPreferredChannels trait | `Notifications/` | ❌ Missing | Dynamically resolves channels from user preferences |
| NotificationPreference model | n/a | ✅ Exists | `app/Models/NotificationPreference.php` — nkhoj's equivalent |
| DeviceToken model | n/a | ✅ Exists | Push notification tokens |

**Status**: nkhoj has its own simpler `NotificationPreference` system that covers the same ground for the current feature set.

---

## 6. Logging & Admin Tools

| Feature | Vebto reference | nkhoj status | Notes |
|---|---|---|---|
| ErrorLogController | `Logging/` | ❌ Missing | Admin UI for viewing `storage/logs/*.log` |
| opcodes/log-viewer package | `Logging/` | ❌ Missing | Not installed; `composer.json` doesn't include it |
| MonitorsSchedule trait | `Logging/` | ❌ Missing | Records cron run success/failure to DB |
| ScheduleLogController | `Logging/` | ❌ Missing | Admin UI for schedule health |
| ScheduleLog model | n/a | ✅ Exists | `app/Models/ScheduleLog.php` — DB table exists |
| ScheduleHealthCommand | `Logging/` | ❌ Missing | Sends alert if schedule hasn't run |
| LogsController (basic) | n/a | ✅ Exists | `app/Domains/Admin/Http/Controllers/LogsController.php` |

**Recommendation**: Install `opcodes/log-viewer` and wire up `ErrorLogController` for admin log viewer. The `ScheduleLog` model is already there — add the `MonitorsSchedule` trait to scheduled commands.

---

## 7. Settings

| Feature | Vebto reference | nkhoj status | Notes |
|---|---|---|---|
| DotEnvEditor | `Settings/DotEnvEditor.php` | ✅ Implemented | `app/Services/DotEnvEditor.php` |
| Settings JSON store | n/a | ✅ Exists | `storage/app/site_settings.json` |
| AppServiceProvider applies settings | n/a | ✅ Exists | Reads JSON, calls `config()` on boot |
| Social OAuth `.env` sync | Vebto pattern | ✅ Implemented | Google/Facebook client IDs written to `.env` |
| SMTP validator (pre-save test) | `Settings/Validators/MailCredentials/` | ✅ Added | `app/Services/OutgoingMailCredentialsValidator.php` |

---

## 8. Scheduled Commands / Cron

| Feature | Vebto reference | nkhoj status | Notes |
|---|---|---|---|
| DeleteExpiredBansCommand | `Auth/Commands/` | ❌ Missing | Needs `bans` table first |
| DeleteExpiredOtpCodesCommand | `Auth/Commands/` | ❌ Missing | Needs `otp_codes` table first |
| General scheduled cleanup | n/a | 🔄 Partial | `routes/console.php` has some cleanup |

---

## Priority Backlog (ordered)

| Priority | Item | Effort |
|---|---|---|
| P1 | **SMTP validator** — test credentials before saving, show error in UI | Small |
| P1 | **ForbidBannedUser middleware** — auto-logout banned users | Small |
| P2 | **Ban model + bans table** — timed bans with reason/expiry | Medium |
| P2 | **Log viewer** — `opcodes/log-viewer` + admin route | Medium |
| P3 | **MonitorsSchedule** — record cron health to `schedule_logs` | Medium |
| P3 | **ScheduleHealthCommand** — alert if cron not running | Small |
| P4 | **OtpCode model** + email OTP flow | Large |
| P4 | **NotificationSubscription** model | Large |

---

## Files Changed This Session

| File | Change |
|---|---|
| `app/Http/Middleware/ForbidBannedUser.php` | New — blocks banned users |
| `app/Http/Middleware/OptionalAuthenticate.php` | New — optional auth for guest routes |
| `app/Models/User.php` | Added `isBanned()` method |
| `app/Services/DotEnvEditor.php` | New — writes key=value to `.env` |
| `app/Services/OutgoingMailCredentialsValidator.php` | New — tests SMTP credentials |
| `app/Mail/MailTestMailable.php` | New — test email mailable |
| `app/Providers/AppServiceProvider.php` | Added `mail.default => 'smtp'` |
| `app/Domains/Admin/Http/Controllers/SettingsController.php` | SMTP + OAuth write to `.env` |
| `bootstrap/app.php` | Registered `ForbidBannedUser` in web middleware stack |
