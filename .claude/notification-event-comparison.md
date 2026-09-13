# Notification & Event Architecture — BeDesk vs nkhoj

_Last updated: 2026-09-13_

---

## Overview

| Aspect | BeDesk (Vebto) | nkhoj (before) | nkhoj (after this PR) |
|--------|---------------|----------------|----------------------|
| **Notification base** | `TicketingNotification` abstract class | None — raw `CreateNotification` job | `BaseNotification` abstract class |
| **Channel routing** | `via()` reads from `notificationSubscriptions` per NOTIF_ID | Always DB + FCM (ignores preferences) | `via()` checks `NotificationPreference` per type |
| **Channels** | database, broadcast (WebSocket), mail, Slack | DB only (push sent regardless of pref) | database, mail, FCM push (gated by pref) |
| **FCM API** | N/A (BeDesk is SaaS, no FCM) | **Legacy HTTP API** (dead since June 2024) | **FCM HTTP v1** with OAuth2 service account |
| **Email channel** | Laravel `MailMessage` in `via()` | None | `Mail::html()` via `emails.notification` view |
| **Event → Listener** | `TicketCreated` → `SendTicketCreatedNotif` listener | Dispatches job directly from controller | Unchanged (opportunity for future refactor) |
| **Per-scenario classes** | 9 notification classes (assigned, reply variants) | Single `CreateNotification` job | `BaseNotification` for new notifications; existing job improved |
| **Conditional broadcast** | `broadcastWhen()` skips drafts | N/A | N/A (no WebSockets yet) |
| **Stale token cleanup** | N/A | No | Yes — deletes `UNREGISTERED` tokens |
| **User deletion cleanup** | `DeleteUserRelations` listener | None documented | Not yet implemented |
| **Loader classes** | `LandingPageLoader`, `TicketLoader`, `ArticleLoader`, etc. | Logic in controllers | Not yet extracted |

---

## BeDesk Event → Listener → Notification Flow

```
TicketCreated (Event, ShouldBroadcast)
    └── Channel('tickets')                        ← real-time WebSocket
    └── SendTicketCreatedNotif (Listener)
            └── whereNeedsNotificationFor('01')   ← filters subscribed users
            └── Notification::send(users, TicketCreatedNotif)
                    └── via() → ['database','broadcast','mail'] per user preference

TicketReplyCreated (Event, ShouldBroadcast, ShouldQueue)
    └── Channel('tickets')
    └── broadcastWhen() — skips draft replies
    └── SendReplyCreatedNotif (Listener, ShouldQueue)
            └── 6 variant notification classes based on assignee/sender role

TicketsAssigned (Event)
    └── SendTicketsAssignedNotif (Listener)
            └── TicketAssignedNotif + TicketAssignedNotMeNotif
```

### `whereNeedsNotificationFor` scope (BeDesk)

```php
// On User model — filters users who have subscribed to a NOTIF_ID
User::whereNeedsNotificationFor(TicketCreatedNotif::NOTIF_ID)->get();

// TicketingNotification::via() reads the subscription:
$sub = $notifiable->notificationSubscriptions
    ->where('notif_id', static::NOTIF_ID)
    ->first();
// maps 'browser' → ['database','broadcast'], 'email' → ['mail'], 'slack' → 'slack'
```

---

## nkhoj Before

```
CommentController::store()
    └── CreateNotification::dispatch($post->author_id, 'comment', $data)
            └── Always: Notification::create(...)      ← DB record (no pref check)
            └── Always: sendFcmPush()                  ← ignores push preference
                    └── POST fcm.googleapis.com/fcm/send  ← DEAD (June 2024)
                    └── Authorization: key=...             ← legacy auth, rejected
```

---

## nkhoj After (this PR)

```
CommentController::store()
    └── CreateNotification::dispatch($post->author_id, 'comment', $data)
            └── Check NotificationPreference for user + type
            ├── in_app=true  → Notification::create(...)
            ├── email=true   → Mail::html(emails.notification) 
            └── push=true    → sendFcmPush() via FCM HTTP v1
                    └── getFcmAccessToken() via google/apiclient OAuth2
                    └── POST /v1/projects/{id}/messages:send  ← current API
                    └── UNREGISTERED tokens auto-deleted
```

---

## FCM Legacy vs HTTP v1

| | Legacy (removed) | HTTP v1 (current) |
|-|-----------------|-------------------|
| **Auth** | `Authorization: key=<server_key>` | `Authorization: Bearer <oauth2_token>` |
| **Endpoint** | `fcm.googleapis.com/fcm/send` | `fcm.googleapis.com/v1/projects/{id}/messages:send` |
| **Batch tokens** | `registration_ids: [...]` (up to 1000) | One token per request |
| **Removed** | **June 2024** | Active |
| **Config needed** | `FIREBASE_SERVER_KEY` | `FIREBASE_PROJECT_ID` + `FIREBASE_SERVICE_ACCOUNT_PATH` |

### Migration steps

1. Firebase Console → Project Settings → Service Accounts → Generate new private key → download JSON
2. Upload JSON to a path **outside** `public/` (e.g. `/home/user/secrets/firebase-sa.json`)
3. Set `.env`:
   ```
   FIREBASE_PROJECT_ID=your-project-id
   FIREBASE_SERVICE_ACCOUNT_PATH=/home/user/secrets/firebase-sa.json
   ```
4. Remove the old `FIREBASE_SERVER_KEY` entry

---

## Loader Classes (BeDesk pattern, not yet in nkhoj)

BeDesk separates complex eager-loading from controllers into `Data/` loader classes:

```php
// BeDesk: controller is thin
class TicketController {
    public function show(Ticket $ticket): Response {
        return Inertia::render('Ticket', (new TicketLoader)->loadData($ticket));
    }
}

// BeDesk: all query logic in loader
class TicketLoader {
    public function loadData(Ticket $ticket): array {
        $ticket->load(['tags.categories','user.purchase_codes','assignee']);
        $replies = (new PaginateTicketReplies)->execute($ticket->id);
        return ['ticket' => $ticket, 'replies' => $replies, 'draft' => $draft];
    }
}
```

nkhoj has complex query logic directly in controller methods. Extracting loaders would make controllers testable in isolation. Low priority for now — controllers are readable enough at current size.

---

## New `BaseNotification` Usage Example

```php
// app/Notifications/CommentNotification.php
class CommentNotification extends BaseNotification
{
    public function __construct(
        private string $commenterName,
        private string $postTitle,
        private string $postUrl,
    ) {}

    public function notifType(): string { return 'comment'; }

    protected function subject(User $notifiable): string
    {
        return "{$this->commenterName} ले तपाईंको पोस्टमा टिप्पणी गरे";
    }

    protected function body(User $notifiable): string
    {
        return "\"{$this->postTitle}\" मा नयाँ टिप्पणी";
    }

    protected function actionUrl(): ?string { return $this->postUrl; }
    protected function actionLabel(): string { return 'पोस्ट हेर्नुहोस्'; }
}

// Dispatching:
$post->author->notify(new CommentNotification($commenter->name, $post->title, $post->url()));
```

The `via()` on `BaseNotification` reads from `notification_preferences` automatically — mail and push only fire if the user opted in.

---

## `whereNeedsNotification` Scope Usage

```php
// Get all users who want email for 'new_post' events:
User::wantsNotification('new_post', 'email')->get();

// Get all users who want in_app for 'comment' (or have no preference row yet):
User::wantsNotification('comment', 'in_app')->get();
```

---

## What BeDesk Does Better (remaining gaps)

| Gap | BeDesk solution | nkhoj path |
|-----|----------------|-----------|
| No real-time WebSocket events | Laravel Broadcasting + Pusher | Add Reverb (free, self-hosted) or Pusher |
| No Slack integration | `SlackMessage` in `TicketingNotification` | Add `slack-notification-channel` package |
| No per-scenario notification classes | 9 typed classes with `NOTIF_ID` | Migrate to `BaseNotification` subclasses per type |
| No automation trigger emails | `TriggerEmailAction` + placeholder replace | Add trigger rules engine for admin-defined automations |
| No user deletion cascade listener | `DeleteUserRelations` listener | Add `UsersDeleted` event listener to clean up tokens, prefs |
| No activity log | `Activity` model (BeDesk) | Already partially exists in nkhoj as `ActivityLog` |
