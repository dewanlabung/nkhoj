# Comment & Notification System - Architecture Guide

## System Overview

The comment and notification system is designed for nkhoj with these core principles:

1. **Hierarchical Comments**: Comments are organized in trees (parent-child relationships)
2. **Polymorphic Relations**: Comments can attach to any model (Post, Article, Recipe, etc.)
3. **Preference-Based Notifications**: Users control which channels they receive notifications through
4. **Comprehensive Audit Trail**: Every notification action is logged for debugging and compliance
5. **High Performance**: Single-query loads for nested comments, efficient channel routing

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                     User Action Layer                        │
│  (Comments posted, notifications read, preferences updated)  │
└────────────────┬────────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────────┐
│                     API Controllers                          │
│  ├─ CommentController                                        │
│  ├─ NotificationController                                   │
│  └─ NotificationSubscriptionsController                      │
└────────────────┬────────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────────┐
│                   Core Actions                               │
│  ├─ CrupdateComment (create/update with notifications)      │
│  ├─ LoadChildComments (efficient nested loading)            │
│  ├─ PaginateModelComments (with nesting)                    │
│  └─ SubscribeUserToNotifications (initialize preferences)    │
└────────────────┬────────────────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────────────────┐
│                 Models & Relationships                       │
│  ├─ Comment (polymorphic, hierarchical)                     │
│  ├─ NotificationSubscription (user preferences)             │
│  └─ NotificationActivityLog (audit trail)                   │
└────────────────┬────────────────────────────────────────────┘
                 │
         ┌───────┴────────────┐
         │                    │
┌────────▼──────┐   ┌────────▼──────────┐
│   Database    │   │  Event System     │
│   (MySQL)     │   │  & Notifications  │
└───────────────┘   └───────────────────┘
                           │
                    ┌──────┴──────┐
                    │             │
            ┌───────▼──┐   ┌─────▼──────┐
            │ Database │   │ Email/Push │
            │ Notif.   │   │ Channels   │
            └──────────┘   └────────────┘
```

## Component Deep Dive

### 1. Comment Model & Hierarchy

#### Design Decision: Path-Based Hierarchy
Instead of recursive tree queries, comments use a "path" field storing IDs separated by slashes.

**Example**:
```
Comment 1 (root)
  └─ Comment 2 (path: "1/2")
      └─ Comment 3 (path: "1/2/3")
  └─ Comment 4 (path: "1/4")
```

**Benefits**:
- Load entire tree in 2 queries (root comments + all descendants)
- Depth calculation is trivial (count slashes)
- LIKE queries efficient for finding ancestors/descendants
- No recursive queries = better performance

#### Soft Delete Strategy
Comments have a `deleted` boolean flag. When deleting:
- **If has children**: Set `deleted = true` (soft delete) → preserves thread continuity
- **If no children**: Hard delete → keeps database clean

**Reasoning**:
- Users need to see conversation context (why someone said something)
- Deleting all traces breaks thread continuity
- Only true leaf comments are hard-deleted
- Nested soft-deleted comments show blank content to users

### 2. Polymorphic Relations

#### Design Pattern: MorphMany
```php
class Comment extends Model {
    public function commentable() {
        return $this->morphTo();
    }
}

class Post extends Model {
    public function comments() {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
```

**Storage**:
```
comments table:
  commentable_type: "App\\Models\\Post"
  commentable_id: 1
```

**Benefits**:
- Single `comments` table for all models
- No duplicate comment logic per model
- Easy to add comments to new models
- Efficient: single table with multi-table indexes

### 3. Notification Subscription System

#### Design: UUID-Based Preferences
```php
notification_subscriptions table:
  id: UUID (not auto-increment)
  user_id: integer
  notif_id: string (e.g., "comment_replied")
  channels: JSON array {"browser": true, "email": false, "mobile": true}
```

**Why UUID**:
- Immutable subscription identifiers
- Can refer to subscriptions in external systems
- Better for audit trails
- UUID auto-generated in model boot

**Why JSON channels**:
- Flexible channel set per notification type
- Easy to add new channels (browser → database+broadcast, email → mail, mobile → FCM)
- Single record per notification type per user (not 3 records for 3 channels)

#### Preference Hierarchy
1. **User explicitly set**: Use their preference
2. **Not set**: Fall back to defaults (all channels enabled)
3. **Permission check**: Some notifications require roles (e.g., system_error for admins)

### 4. Notification Delivery Architecture

#### Trait-Based Channel Selection
```php
trait GetsUserPreferredNotificationChannels {
    public function getPreferredNotificationChannels($notifId): array {
        $subscription = $this->notificationSubscriptions()
            ->where('notif_id', $notifId)
            ->first();
        
        $channels = $subscription->channels ?? [];
        
        // Map internal channels to Laravel notification channels
        return collect(['browser', 'email', 'mobile'])
            ->filter(fn($ch) => $channels[$ch] ?? true) // Default enabled
            ->map(fn($ch) => match($ch) {
                'browser' => 'database',  // + broadcast
                'email' => 'mail',
                'mobile' => 'fcm',
            })
            ->toArray();
    }
}
```

**Flow**:
1. Notification class extends `BaseNotification`
2. Calls `getPreferredNotificationChannels()` during send
3. Only sends through enabled channels
4. Logs success/failure to activity log

**Benefits**:
- User control without code changes
- Prevents notification fatigue
- Audit trail for compliance
- Easy to add new channels

### 5. Activity Logging

#### Comprehensive Audit Trail
```php
NotificationActivityLog::log(
    user: $user,
    notif_type: 'comment_replied',  // What notification
    action: 'sent',                 // What happened
    channel: 'email',               // Through which channel
    success: true,                  // Did it work?
    error: null,                    // Error message if failed
    data: ['comment_id' => 123]     // Context
);
```

**Logged Events**:
- Sent: Notification dispatched
- Read: User opened notification
- Deleted: User deleted notification
- Failed: Delivery failed (with error message)
- Subscription: User changed preferences

**Uses**:
- Debugging failed deliveries
- User activity timeline
- Compliance/audit requirements
- Analytics (which notifications matter to users)

### 6. Event-Driven Notifications

#### Event → Listener → Notification Flow

```
1. CrupdateComment.execute()
   └─ Creates Comment record
   └─ Fires CommentReplyCreated event
   
2. SendCommentReplyNotification listener
   └─ Gets original comment author
   └─ Gets their preferred channels
   └─ Creates CommentReceivedReply notification
   
3. CommentReceivedReply notification
   └─ Composes message with comment details
   └─ Gets preferred channels
   └─ Sends through: database + broadcast, email, FCM
   
4. Each channel send logged to NotificationActivityLog
   └─ Success/failure recorded
   └─ Error messages captured
```

**Decoupling**:
- Comment creation doesn't know about notifications
- Listener handles notification logic
- Easy to add new listeners without changing Comment model
- Events can be tested without full notification send

### 7. Permission-Based Authorization

#### Two-Layer Authorization

**Layer 1: Policy-Based (Resource)**
```php
class CommentPolicy extends Model {
    public function store($user) {
        return $user && !$user->is_banned && $user->hasPermission('comment.create');
    }
    
    public function update($user, $comment) {
        return $user->is($comment->user) || $user->hasPermission('comment.update');
    }
}
```

**Layer 2: Notification Permissions**
```php
// In notification-settings.php
[
    'notif_id' => 'system_error',
    'name' => 'System Errors',
    'permissions' => ['admin.view_system_errors']
]
```

**Flow**:
1. User tries to create/update comment → Policy check
2. Notification config loaded → Permission filter applied
3. User without permission → Can't see/modify that notification type
4. Trying to update restricted notification → 403 Forbidden

**Benefits**:
- Fine-grained control
- Can hide notification types from regular users
- Integrates with nkhoj's existing role system

### 8. Performance Optimizations

#### Efficient Comment Loading
```php
// Load root comments with children in 2 queries
$roots = Comment::where(...)->paginate(15);
$allWithChildren = LoadChildComments::execute($roots);
// vs. N queries with lazy loading of children
```

**Optimization Technique**:
```php
// Single query for all descendants of multiple roots
$paths = $roots->pluck('path')->toArray();
$descendants = Comment::where(function ($q) use ($paths) {
    foreach ($paths as $path) {
        $q->orWhere('path', 'LIKE', $path . '/%');
    }
})->get();
```

#### Index Strategy
```sql
-- Composite indexes for common queries
CREATE INDEX idx_commentable 
  ON comments(commentable_type, commentable_id);

CREATE INDEX idx_parent ON comments(parent_id);
CREATE INDEX idx_user ON comments(user_id);
CREATE INDEX idx_path ON comments(path);  -- For ancestor queries

-- Full-text search
FULLTEXT INDEX ft_content ON comments(content);
```

#### Query Logging
Comprehensive activity logging means:
- Track which notifications actually deliver
- Identify performance issues (e.g., slow FCM sending)
- Spot failed delivery patterns

### 9. Configuration System

#### Centralized, Extensible Config

**Structure**:
```php
return [
    'available_channels' => ['browser', 'email', 'mobile'],
    'subscriptions' => [
        'activity' => [
            'subscriptions' => [
                [
                    'notif_id' => 'comment_replied',
                    'name' => 'Comment Reply',
                    'permissions' => []
                ]
            ]
        ]
    ]
];
```

**Adding New Notification Type**:
1. Add entry to `subscriptions` array
2. Create notification class
3. Send via `$user->notify(new YourNotification())`
4. Automatically available in preference UI

**Adding New Channel**:
1. Add to `available_channels`
2. Update `GetsUserPreferredNotificationChannels` trait mapping
3. Implement channel sender (mail, FCM, etc.)

### 10. Testing Strategy

#### Unit Tests
```php
// Test comment hierarchy
CommentFactory::create()->assertPathGenerated();

// Test soft delete
$comment->softOrHardDelete();
$this->assertSoftDeleted($comment); // if has children
$this->assertDeleted($comment);     // if no children
```

#### Integration Tests
```php
// Test full flow
$comment = Comment::factory()->create();
event(new CommentReplyCreated($comment));

// Verify notification sent
Notification::assertSentTo($user, CommentReceivedReply::class);

// Verify activity logged
$this->assertDatabaseHas('notification_activity_logs', [
    'user_id' => $user->id,
    'notif_type' => 'comment_replied',
    'success' => true,
]);
```

#### API Tests
```php
// Test comment endpoints
$this->postJson('/api/v1/comments', [...])->assertCreated();
$this->getJson('/api/v1/comments/1')->assertOk();
$this->deleteJson('/api/v1/comments/1')->assertOk();

// Test preference endpoints
$this->getJson('/api/v1/notification-subscriptions/me')->assertOk();
$this->putJson('/api/v1/notification-subscriptions/me', [...])->assertOk();
```

## Design Patterns Used

### 1. Action Pattern
```php
class CrupdateComment {
    public static function execute(...): Comment {
        // Encapsulates complex business logic
        // Reusable from multiple contexts
    }
}
```

**Benefits**:
- Testable without controllers
- Reusable from jobs, commands, events
- Single responsibility
- Clean separation of concerns

### 2. Service Container
```php
// In CoreServiceProvider
$this->app->singleton(OtpServiceContract::class, OtpAuthService::class);

// Use anywhere
app(OtpServiceContract::class)->verify($code);
```

**Benefits**:
- Dependency injection
- Easy mocking in tests
- Loose coupling

### 3. Trait-Based Composition
```php
class BaseNotification {
    use GetsUserPreferredNotificationChannels;
    use TracksNotificationActivity;
    // Composes functionality without inheritance bloat
}
```

**Benefits**:
- Code reuse across multiple classes
- Cleaner than deep inheritance
- Easy to test individual concerns

### 4. Policy-Based Authorization
```php
$this->authorize('update', $comment);
// vs. hand-written if statements
```

**Benefits**:
- Centralized authorization logic
- Testable
- Consistent with Laravel conventions
- Easy to extend with additional rules

### 5. Event-Driven Architecture
```php
// Decouple comment creation from notifications
event(new CommentReplyCreated($comment));

// Listener handles notification
class SendCommentReplyNotification implements ShouldQueue {
    public function handle(CommentReplyCreated $event) { ... }
}
```

**Benefits**:
- Loose coupling
- Easy to add new listeners without modifying comment logic
- Can queue notification sending
- Testable in isolation

## Future Extensions

### Email Templates
Create views for email notifications:
```php
// resources/views/notifications/comment-replied.blade.php
// Will be used by CommentReceivedReply::toMail()
```

### FCM Push Notifications
```php
// Implement FCM sender
class FcmNotificationChannel extends Channel {
    public function send($notifiable, $notification) {
        // Send via Firebase Cloud Messaging
    }
}
```

### Real-Time Updates (WebSocket)
```php
// Broadcast notification to user's channel
event(new NotificationSent($user, $notification));

// Frontend listens for updates
Echo.private('user.' + userId)
    .notification((notification) => {
        // Update UI in real-time
    });
```

### Notification Batching
```php
// Group similar notifications
CommentRepliesBatch::for($user)
    ->add($comment)
    ->sendAfter(minutes: 5);
```

### Smart Delivery Timing
```php
// Don't send notifications during user's quiet hours
if (!$user->isInQuietHours()) {
    $user->notify($notification);
}
```

## Monitoring & Observability

### Key Metrics
- Comment creation rate
- Notification delivery rate
- Failed delivery patterns
- Channel preference distribution
- Response time for comment APIs

### Debugging Tools
```php
// Check notification flow
DB::table('notification_activity_logs')
    ->where('user_id', $userId)
    ->whereDate('created_at', today())
    ->get();

// Verify subscriptions
$user->notificationSubscriptions()->get();

// Check unread notifications
$user->unreadNotifications()->count();
```

### Error Handling
- Activity log captures all errors
- Failed deliveries can be retried
- Permission errors logged for audit
- Network errors captured with context

## Security Considerations

### Input Validation
- Comment content: 3-5000 characters
- Notification type: against whitelist
- Channels: against configured channels
- Commentable type: against registered models

### Authorization Checks
- Comment author or admin can delete
- User can only update own preferences
- Permission-based notification filtering
- IP logging for audit trail

### Data Protection
- Soft-deleted comments still accessible to author
- Hidden fields on comment model (paths, types)
- Notification data includes only necessary details
- Activity logs can be purged after retention period

## Maintenance Checklist

- [ ] Monitor notification delivery rates
- [ ] Check for permission-related errors
- [ ] Review failed deliveries weekly
- [ ] Archive old activity logs (retention policy)
- [ ] Test email/FCM channels monthly
- [ ] Update documentation with custom notifications
- [ ] Performance test with large comment threads
- [ ] Verify soft delete logic preserves threads correctly
