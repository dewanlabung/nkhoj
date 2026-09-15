# Vebto BeDesk Gap Analysis — nkhoj vs Common Package

**Reference**: `.claude/common/` (Vebto BeDesk common package, extracted from `common.zip`)
**Last updated**: 2026-09-13
**Branch tracking**: `master`

---

## Status Legend
- ✅ Implemented — feature exists and works
- 🔄 Partial — feature exists but simpler/different approach than Vebto
- ❌ Missing — not implemented at all
- ⏳ Planned

---

## 1. Security & Auth Middleware

| Feature | Vebto file | nkhoj status | Detail |
|---|---|---|---|
| ForbidBannedUser | `Auth/Middleware/ForbidBannedUser.php` | ✅ | `app/Http/Middleware/ForbidBannedUser.php` — uses `Auth::logout()` facade (not constructor injection) |
| OptionalAuthenticate | `Auth/Middleware/OptionalAuthenticate.php` | ✅ | `app/Http/Middleware/OptionalAuthenticate.php` — alias `optional.auth` registered |
| CheckIpBan | nkhoj-custom | ✅ | `app/Http/Middleware/CheckIpBan.php` — IP + CIDR range ban |
| SecurityHeaders | nkhoj-custom | ✅ | `app/Http/Middleware/SecurityHeaders.php` |
| TrackLastSeen | nkhoj-custom | ✅ | `app/Http/Middleware/TrackLastSeen.php` |
| VerifyApiAccessMiddleware | `Auth/Middleware/VerifyApiAccessMiddleware.php` | ❌ | Vebto gates API by `api.access` permission; nkhoj API uses Sanctum tokens only — no per-token permission scope |

---

## 2. User Ban System

| Feature | Vebto file | nkhoj status | Detail |
|---|---|---|---|
| `isBanned()` on User | `Auth/BaseUser.php` | ✅ | Added to `app/Models/User.php` |
| `hasPermission()` on User | `Auth/BaseUser.php` | ✅ | Added to `app/Models/User.php` |
| `is_banned` column (permanent) | — | ✅ | Flat boolean on `users` table |
| Timed bans (`bans` table) | `Auth/Ban.php` | ❌ | Vebto stores ban records with `expired_at`, `comment`, and `created_by`. nkhoj only has the flag — no expiry, no audit trail |
| BanController API | `Auth/Controllers/BanController.php` | 🔄 | nkhoj sets `is_banned` via user-edit admin form; no dedicated ban/unban API endpoint |
| `bans()` hasMany relationship | `Auth/BaseUser.php` | ❌ | Not present; blocked by missing `bans` table |
| DeleteExpiredBansCommand | `Auth/Commands/` | ❌ | Requires `bans` table first |

**To fully match Vebto**: add a `bans` table migration, `Ban` model with `expired_at`/`comment`, `bans()` relationship on User, and a cron to clear expired bans.

---

## 3. Email — SMTP Configuration

| Feature | Vebto file | nkhoj status | Detail |
|---|---|---|---|
| DotEnvEditor | `Settings/DotEnvEditor.php` | ✅ | `app/Services/DotEnvEditor.php` |
| SMTP settings → `.env` sync | `Settings/SettingsController.php` | ✅ | `updateSmtpSettings()` writes 8 `MAIL_*` vars to `.env` |
| `config:clear` after save | Vebto pattern | ✅ | Called inside `updateSmtpSettings()` |
| `mail.default => 'smtp'` applied at runtime | `Settings/Settings.php` | ✅ | `AppServiceProvider::boot()` reads JSON and sets it |
| OAuth social credentials → `.env` | Vebto pattern | ✅ | Google/Facebook IDs written on `updateAuthSettings()` |
| OutgoingMailCredentialsValidator | `Settings/Validators/MailCredentials/` | ✅ | `app/Services/OutgoingMailCredentialsValidator.php` — friendly error messages |
| MailTestMailable | `Settings/Validators/MailCredentials/` | ✅ | `app/Mail/MailTestMailable.php` |
| sendTestEmail admin action | route: `POST /admin/email-settings/test` | ✅ | Uses `OutgoingMailCredentialsValidator` |
| Gmail API mail transport | `Logging/Mail/GmailApiMailTransport.php` | ❌ | Vebto supports Gmail OAuth API as a transport; nkhoj only does SMTP |
| Mailgun / SES transport config | Vebto `updateSmtpSettings` | 🔄 | nkhoj maps `service` to mailer name but doesn't configure the `services.*` keys for Mailgun/SES |

---

## 4. Email Logging (Outgoing)

| Feature | Vebto file | nkhoj status | Detail |
|---|---|---|---|
| `OutgoingEmailLogSubscriber` | `Logging/Mail/OutgoingEmailLogSubscriber.php` | ✅ | `app/Listeners/OutgoingEmailLogSubscriber.php` — listens on `MessageSending`/`MessageSent`, writes `X-NK-LOG-ID` header |
| `OutgoingEmailLog` model | `Logging/Mail/OutgoingEmailLogItem.php` | ✅ | `app/Models/OutgoingEmailLog.php` |
| Email log in admin UI | `Logging/Mail/OutgoingEmailLogController.php` (API) | 🔄 | nkhoj shows email logs in blade `admin.logs` view; Vebto uses a JSON API + SPA table. Functionally equivalent. |
| Download email log | Vebto controller | ✅ | `LogsController::downloadEmailLog()` |
| MIME body parse/preview | Vebto uses `ZBateson\MailMimeParser` | ❌ | nkhoj stores raw `mime` column but has no parsed HTML preview in admin; Vebto parses headers and HTML body on demand |
| Subscriber registered | `AppServiceProvider` | ✅ | `Event::subscribe(OutgoingEmailLogSubscriber::class)` |
| Clean old log entries | `CleanLogTables.php` | ✅ | `logs:clean` cron — 7-day email logs, 30-day schedule logs |

---

## 5. Schedule Monitoring

| Feature | Vebto file | nkhoj status | Detail |
|---|---|---|---|
| Per-event before/after hooks | `Logging/Schedule/MonitorsSchedule.php` | ✅ | Implemented inline in `routes/console.php` using `$event->before()` / `$event->after()` with a Stopwatch — same logic as Vebto's trait |
| `ScheduleLog` model | `Logging/Schedule/ScheduleLogItem.php` | ✅ | `app/Models/ScheduleLog.php` |
| Dedup logic (one row per signature per hour) | Vebto trait | ✅ | nkhoj's inline monitor does the same dedup |
| Schedule log in admin UI | `Logging/Schedule/ScheduleLogController.php` (API) | 🔄 | nkhoj shows in blade `admin.logs` view; Vebto uses JSON API. Equivalent. |
| ScheduleHealthCommand | `Logging/Schedule/ScheduleHealthCommand.php` | ❌ | Vebto runs `schedule:be-health` every minute — a canary job that proves cron is alive. nkhoj has no equivalent |
| MonitorsSchedule as a reusable trait | Vebto trait | 🔄 | nkhoj has the same behaviour but embedded directly in `routes/console.php` instead of a trait — harder to reuse |

**To fully match Vebto**: add `ScheduleHealthCommand` (runs every minute as a canary) and optionally extract the monitoring block into a `MonitorsSchedule` trait.

---

## 6. Error Log Viewer

| Feature | Vebto file | nkhoj status | Detail |
|---|---|---|---|
| Basic error log viewer | `Logging/Error/ErrorLogController.php` | 🔄 | nkhoj reads last 200KB of `laravel.log` in `LogsController::readErrorLog()` and groups by entry — functional but primitive |
| `opcodes/log-viewer` package | Required by Vebto | ❌ | Not in `composer.json`. Gives searchable, level-filtered, multi-file log viewer. Vebto's `ErrorLogController` delegates entirely to it |
| Search log entries | `LogViewer::getFile()->logs()->search()` | ❌ | nkhoj has no search — raw lines only |
| Filter by log level | `LogViewer` | ❌ | nkhoj shows all levels together |
| Download log file | `ErrorLogController::download()` | ❌ | nkhoj has no per-file download from error logs |
| Multiple log file support | `LogViewer::getFiles()` | ❌ | nkhoj reads only `laravel.log` |

**To match Vebto**: run `composer require opcodesio/log-viewer` and add a route + controller that wraps `LogViewer::getFiles()`. The admin blade view already has a log tab to plug into.

---

## 7. Notifications

| Feature | Vebto file | nkhoj status | Detail |
|---|---|---|---|
| Custom `Notification` model | nkhoj-custom | ✅ | `app/Models/Notification.php` — `user_id`, `type`, `data`, `read_at` |
| `NotificationPreference` model | nkhoj-custom | ✅ | Per-user per-type flags: `in_app`, `email`, `push` with defaults |
| `DeviceToken` model | nkhoj-custom | ✅ | `app/Models/DeviceToken.php` — FCM push tokens |
| `CreateNotification` job | nkhoj-custom | ✅ | Queued job: writes DB row + sends FCM push via legacy HTTP API |
| `NotificationController` | `Notifications/NotificationController.php` | ✅ | nkhoj has index, recent, markRead, unreadCount — blade + JSON API |
| FCM push (Firebase) | nkhoj-custom | ✅ | Legacy FCM HTTP API in `CreateNotification` job |
| `NotificationSubscription` model | `Notifications/NotificationSubscription.php` | ❌ | Vebto stores per-user per-notification-type channel preferences (database, email, mobile, broadcast). nkhoj's `NotificationPreference` covers `in_app`/`email`/`push` per type — similar but not per-notification-id |
| `GetsUserPreferredChannels` trait | `Notifications/GetsUserPreferredChannels.php` | ❌ | Vebto notification classes use this to dynamically select channels from user's subscription. nkhoj hard-codes channels in `CreateNotification` job |
| `SubscribeUserToNotifications` action | `Notifications/SubscribeUserToNotifications.php` | ❌ | Vebto bulk-creates subscription rows on user registration. nkhoj uses `NotificationPreference::defaultsFor()` — same idea, different model |
| `NotificationSubscriptionsController` | `Notifications/NotificationSubscriptionsController.php` | ❌ | Vebto has a UI for users to pick which notification types arrive via which channel. nkhoj has `NotificationPreference` model but no user-facing settings UI for it |
| `broadcast` channel (websocket) | Vebto via `GetsUserPreferredChannels` | ❌ | nkhoj has no real-time broadcast channel |
| FCM via `notificationchannels/fcm` package | Vebto `FcmChannel::class` | 🔄 | nkhoj uses raw HTTP to FCM legacy API; Vebto uses the dedicated package |

---

## 8. OTP / Email Verification

| Feature | Vebto file | nkhoj status | Detail |
|---|---|---|---|
| OtpCode model | `Auth/OtpCode.php` | ❌ | Vebto sends time-limited numeric OTPs stored in DB |
| EmailVerificationController (OTP) | `Auth/Controllers/EmailVerificationController.php` | ❌ | Vebto verifies email by OTP code. nkhoj uses standard Laravel signed-link verification |
| DeleteExpiredOtpCodesCommand | `Auth/Commands/` | ❌ | Requires OTP table |
| Standard Laravel email verification | Laravel built-in | ✅ | nkhoj uses `email_verified_at` + signed URL |

**Assessment**: low priority unless product requires OTP-style verification instead of link-based.

---

## 9. Priority Backlog

| Priority | Item | Effort | Benefit |
|---|---|---|---|
| **P1** | `opcodes/log-viewer` + admin error log controller | Small | Search/filter production errors from admin panel |
| **P1** | `ScheduleHealthCommand` (canary cron job) | Tiny | Know immediately if cron stops running |
| **P2** | `bans` table + `Ban` model + timed bans | Medium | Audit trail, temporary bans with expiry |
| **P2** | Notification subscription UI (user picks channels per type) | Medium | Users control how they're notified |
| **P3** | Gmail API mail transport | Large | Send via Gmail OAuth instead of raw SMTP |
| **P3** | Migrate FCM to `notificationchannels/fcm` package | Medium | Use maintained package instead of raw HTTP |
| **P4** | OTP email verification flow | Large | Only if product requires it |
| **P4** | WebSocket broadcast channel | Large | Requires Pusher/Reverb setup |

---

## 10. Already Well-Matched Areas

These areas are **implemented and functioning** in nkhoj — no major gaps:

- SMTP settings persistence (`.env` sync + `config:clear`)
- Outgoing email logging with subscriber + admin view + cleanup cron
- Schedule monitoring with before/after hooks + ScheduleLog + admin view
- FCM push notifications (functional, legacy API)
- User ban flag (`is_banned`) + `ForbidBannedUser` middleware
- `NotificationPreference` model with per-type defaults
- `CheckIpBan` middleware with CIDR support
- Auth: standard Laravel email verification, social OAuth, 2FA, session tracking
- Log table cleanup cron (`logs:clean`)
