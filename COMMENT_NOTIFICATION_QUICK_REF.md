# Comment & Notification System - Quick Reference

## Core Models

### Comment
```php
use App\Core\Models\Comment;

// Create a comment
$comment = Comment::create([
    'content' => 'Great post!',
    'user_id' => auth()->id(),
    'commentable_type' => 'App\\Models\\Post',
    'commentable_id' => 1,
]);

// Create a reply (parent_id automatically sets path)
$reply = Comment::create([
    'content' => 'Thanks!',
    'user_id' => auth()->id(),
    'commentable_type' => 'App\\Models\\Post',
    'commentable_id' => 1,
    'parent_id' => $comment->id, // Make it a reply
]);

// Accessing depth and nesting
$comment->depth; // 0 (root comment)
$reply->depth;   // 1 (child comment)
$reply->path;    // "1/2" (hierarchical path)

// Soft/hard delete strategy
$comment->softOrHardDelete(); // Deletes children only if has no grandchildren
```

### NotificationSubscription
```php
use App\Core\Models\NotificationSubscription;

// Get user's subscriptions
$user->notificationSubscriptions()->get();

// Check if subscribed to channel
$subscription = $user->notificationSubscriptions()
    ->where('notif_id', 'comment_replied')
    ->first();
$subscription->isSubscribedToChannel('email'); // true/false

// Update channels
$subscription->subscribeToChannel('mobile');
$subscription->unsubscribeFromChannel('browser');
```

### NotificationActivityLog
```php
use App\Core\Models\NotificationActivityLog;

// Log an activity
NotificationActivityLog::log(
    $user,
    'comment_replied',      // notification type
    'sent',                 // action
    'email',                // channel
    true,                   // success
    null,                   // error message (if failed)
    ['comment_id' => 123]   // additional data
);

// Query logs
$logs = NotificationActivityLog::forUser($user, 50);
$failed = NotificationActivityLog::failedDeliveries();
$byType = NotificationActivityLog::byNotificationType('comment_replied');
```

## Core Actions

### Create/Update Comment
```php
use App\Core\Actions\CrupdateComment;

$comment = CrupdateComment::execute(
    user: auth()->user(),
    data: [
        'content' => 'Great post!',
        'commentable_type' => 'App\\Models\\Post',
        'commentable_id' => 1,
        'inReplyTo' => [
            'id' => 5,
            'user' => ['id' => 3]
        ]
    ],
    comment: null // For update, pass existing comment
);
```

### Load Nested Comments
```php
use App\Core\Actions\LoadChildComments;

// Load all nested comments for root comments
$rootComments = Comment::where(...)->get();
$nested = LoadChildComments::execute($rootComments);
// Returns array with children spliced after parents
```

### Paginate Comments
```php
use App\Core\Actions\PaginateModelComments;

$paginated = PaginateModelComments::execute(
    commentableType: 'App\\Models\\Post',
    commentableId: 1,
    perPage: 15,
    page: 1
);
// Returns paginated array with nested children loaded
```

### Subscribe User to Notifications
```php
use App\Core\Actions\SubscribeUserToNotifications;

// Called automatically on user creation
SubscribeUserToNotifications::execute($user);
// Creates NotificationSubscription records with default channels
```

## Sending Notifications

### From Event Listener
```php
use App\Core\Events\CommentReplyCreated;
use App\Core\Listeners\SendCommentReplyNotification;
use App\Core\Notifications\CommentReceivedReply;
use App\Core\Traits\GetsUserPreferredNotificationChannels;

// Event fires when comment reply created
event(new CommentReplyCreated($comment));

// Listener sends notification
class SendCommentReplyNotification {
    public function handle(CommentReplyCreated $event): void {
        $originalAuthor = $event->comment->parent->user;
        $channels = $originalAuthor->getPreferredNotificationChannels('comment_replied');
        
        $originalAuthor->notify(
            new CommentReceivedReply($event->comment, $channels)
        );
    }
}
```

### Custom Notification
```php
use App\Core\Notifications\BaseNotification;

class MyNotification extends BaseNotification {
    public const NOTIF_ID = 'my_custom_notif';
    
    public function __construct(private array $data) {}
    
    public function notificationType(): string {
        return self::NOTIF_ID;
    }
    
    public function subject(): string {
        return 'My Notification Subject';
    }
    
    public function body(): string {
        return 'Notification body text';
    }
    
    public function actionUrl(): ?string {
        return url('/my-action');
    }
    
    public function actionLabel(): ?string {
        return 'View Details';
    }
}

// Send it
$user->notify(new MyNotification($data));
```

## Authorization

### Comment Policy
```php
// In controllers or requests
$this->authorize('store', Comment::class);
$this->authorize('update', $comment);
$this->authorize('destroy', $comment);
$this->authorize('restore', $comment);
```

### Check User Permissions
```php
// Using traits
if ($user->hasPermission('admin.view_system_errors')) {
    // Show system error notifications
}
```

## API Usage

### Create Comment
```bash
curl -X POST http://nkhoj.local/api/v1/comments \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Great post!",
    "commentable_type": "App\\\\Models\\\\Post",
    "commentable_id": 1
  }'
```

### Create Reply
```bash
curl -X POST http://nkhoj.local/api/v1/comments \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Thanks for your feedback!",
    "commentable_type": "App\\\\Models\\\\Post",
    "commentable_id": 1,
    "inReplyTo": {
      "id": 123,
      "user": {"id": 45}
    }
  }'
```

### Get Comments
```bash
# Get paginated comments with nested structure
curl http://nkhoj.local/api/v1/commentable-comments \
  -G \
  --data-urlencode 'commentable_type=App\Models\Post' \
  --data-urlencode 'commentable_id=1' \
  -H "Authorization: Bearer $TOKEN"
```

### Update Comment
```bash
curl -X PUT http://nkhoj.local/api/v1/comments/123 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"content": "Updated content"}'
```

### Delete Comment
```bash
curl -X DELETE http://nkhoj.local/api/v1/comments/123 \
  -H "Authorization: Bearer $TOKEN"
```

### Get Notifications
```bash
# Paginated
curl http://nkhoj.local/api/v1/notifications?perPage=15 \
  -H "Authorization: Bearer $TOKEN"

# Unread count
curl http://nkhoj.local/api/v1/notifications/unread-count \
  -H "Authorization: Bearer $TOKEN"
```

### Update Preferences
```bash
curl -X PUT http://nkhoj.local/api/v1/notification-subscriptions/me \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "selections": [
      {
        "notif_id": "comment_replied",
        "channels": {
          "browser": true,
          "email": true,
          "mobile": false
        }
      }
    ]
  }'
```

## Common Tasks

### Wire Up Comments on New Model
```php
// In your model (e.g., app/Models/Page.php)
use Illuminate\Database\Eloquent\Relations\MorphMany;

public function comments(): MorphMany {
    return $this->morphMany(\App\Core\Models\Comment::class, 'commentable');
}
```

### Create Test Comment
```php
// In tinker or seeder
use App\Core\Models\Comment;
use App\Models\User;

$comment = Comment::create([
    'content' => 'Test comment',
    'user_id' => User::first()->id,
    'commentable_type' => 'App\\Models\\Post',
    'commentable_id' => 1,
]);
```

### Send Test Notification
```php
use App\Core\Notifications\CommentReceivedReply;
use App\Models\User;

$user = User::find(1);
$comment = \App\Core\Models\Comment::find(1);

$user->notify(new CommentReceivedReply($comment, ['browser', 'email']));
```

### Debug Notification Delivery
```php
// Check logs
$logs = \App\Core\Models\NotificationActivityLog::forUser(auth()->user());
dd($logs->toArray());

// Check preferences
$prefs = auth()->user()->notificationSubscriptions()->get();
dd($prefs->toArray());

// Check unread notifications
$notifs = auth()->user()->unreadNotifications()->get();
dd($notifs->toArray());
```

### Get User's Preferred Channels for Notification Type
```php
use App\Core\Traits\GetsUserPreferredNotificationChannels;

// This trait is used in BaseNotification and other notification classes
$channels = $user->getPreferredNotificationChannels('comment_replied');
// Returns: ['browser', 'email'] (or whatever user has enabled)
```

## Constants & Config

### Notification IDs
```
Activity:
- comment_replied
- post_commented
- user_mentioned
- content_liked

Social:
- user_followed
- user_unfollowed

Content:
- post_published
- article_published

System:
- system_error
- account_security
- account_updates
```

### Channels
```
- browser (database + broadcast)
- email
- mobile (FCM)
```

## Debugging Tips

### View Pending Jobs (if using queues)
```bash
php artisan queue:work --daemon
```

### Monitor Broadcasting (WebSocket)
```php
// Enable broadcasting in config/broadcasting.php
// Check that channel is being broadcasted to
```

### Check Database State
```php
// Comments
Comment::with('user', 'commentable')->latest()->first();

// Subscriptions
NotificationSubscription::where('user_id', auth()->id())->get();

// Activity logs
NotificationActivityLog::where('user_id', auth()->id())
    ->latest()
    ->limit(10)
    ->get();

// Notifications
auth()->user()->notifications()->latest()->limit(10)->get();
```

### Trace Notification Flow
```php
// 1. Comment created
$comment = Comment::create([...]);

// 2. Event dispatched (check CommentReplyCreated in events)
// Event::dispatched(CommentReplyCreated::class);

// 3. Listener invoked (check SendCommentReplyNotification)
// Should insert into notifications table

// 4. Check activity log
NotificationActivityLog::latest()->first();

// 5. Check notification record
auth()->user()->notifications()->latest()->first();
```
