# Comment & Notification System - Deployment Guide

## Overview
This document covers the deployment and testing procedures for the new hierarchical comment system and comprehensive notification infrastructure.

## Phase 1: Database Migrations

### Run Migrations
```bash
php artisan migrate
```

This will create the following tables:
- `comments` - Hierarchical comments with polymorphic relationships
- `notification_subscriptions` - User notification preferences per channel
- `notification_activity_logs` - Audit trail for notification activities

### Migration Details

#### comments table
- **Polymorphic**: Links to any model (Post, Article, Recipe, etc.)
- **Hierarchical**: Uses `parent_id` and `path` fields for tree structure
- **Soft delete**: `deleted` boolean flag preserves thread continuity
- **Indexes**: Optimized for common queries (commentable, parent, path, user)
- **Full-text**: Content column supports FULLTEXT search

#### notification_subscriptions table
- **UUID primary key**: Immutable subscription identifiers
- **Per-notification preferences**: Tracks enabled channels for each notification type
- **JSON channels**: Database, email, mobile preferences stored per type
- **Constraints**: Unique per user per notification type

#### notification_activity_logs table
- **Comprehensive tracking**: Every notification action is logged
- **Success/failure**: Logs including error messages for debugging
- **Audit trail**: IP address, timestamps, and context data
- **Query optimized**: Indexes for user, type, and date-based queries

## Phase 2: Post-Migration Setup

### Initialize Notification Subscriptions for Existing Users

Run this in Laravel Tinker or a command:

```php
use App\Core\Actions\SubscribeUserToNotifications;
use App\Models\User;

// For each existing user, initialize subscriptions
User::where('id', '>', 0)->each(function ($user) {
    SubscribeUserToNotifications::execute($user);
});
```

Or create a custom command:

```bash
php artisan notification:subscribe-users
```

**Note**: New users will be automatically subscribed via an event listener on User creation (if wired up).

## Phase 3: Event Listener Registration

The event listener is already registered in `CoreServiceProvider`:

```php
Event::listen(
    CommentReplyCreated::class,
    SendCommentReplyNotification::class
);
```

**Verification**: Check that `app/Providers/CoreServiceProvider.php` contains the event registration.

## Phase 4: Enable Polymorphic Relations on Models

For each model that should support comments (Post, Article, Recipe, etc.), add:

```php
public function comments()
{
    return $this->morphMany(\App\Core\Models\Comment::class, 'commentable');
}
```

**Models to update**:
- `app/Models/Post.php`
- `app/Models/Article.php`
- `app/Models/Recipe.php`
- Any other commentable models

## Phase 5: Configuration

### Notification Channels
Available channels are defined in `resources/defaults/notification-settings.php`:

1. **browser** - Database storage + broadcast to user's browser
2. **email** - Email notifications
3. **mobile** - FCM push notifications (must implement FCM sender)

### Notification Types
Currently configured notification subscriptions:

#### Activity Group
- `comment_replied` - Someone replies to your comment
- `post_commented` - Someone comments on your post
- `user_mentioned` - You are mentioned in a comment/post
- `content_liked` - Someone likes your content

#### Social Group
- `user_followed` - Someone follows you
- `user_unfollowed` - Someone unfollows you

#### Content Group
- `post_published` - A followed user publishes a post
- `article_published` - A new article is published

#### System Group
- `system_error` - System errors (admin only)
- `account_security` - Login, password, security alerts
- `account_updates` - Email verification, profile changes

## Testing Checklist

### 1. Comment Creation
```bash
# Create a comment on a post
POST /api/v1/comments
{
    "content": "Great post!",
    "commentable_type": "App\\Models\\Post",
    "commentable_id": 1
}
```
**Verify**:
- ✅ Comment saves to database
- ✅ `path` field auto-generated (should be comment ID for root)
- ✅ `created_at` timestamp set
- ✅ Activity logged to `notification_activity_logs`

### 2. Comment Reply
```bash
POST /api/v1/comments
{
    "content": "Thanks for your feedback!",
    "commentable_type": "App\\Models\\Post",
    "commentable_id": 1,
    "inReplyTo": {
        "id": 1,
        "user": {
            "id": 5
        }
    }
}
```
**Verify**:
- ✅ Reply saves with `parent_id` set
- ✅ `path` generated hierarchically (e.g., "1/2")
- ✅ Event `CommentReplyCreated` dispatched
- ✅ Notification created for original comment author (user 5)
- ✅ Activity logged with type `comment_replied`

### 3. Fetch Comments
```bash
# Get paginated root comments
GET /api/v1/commentable-comments?commentable_type=App%5CModels%5CPost&commentable_id=1

# Get all comments
GET /api/v1/comments?commentable_id=1
```
**Verify**:
- ✅ Root comments returned with nested children
- ✅ `depth` attribute shows nesting level
- ✅ Deleted comments show empty content but preserve thread structure
- ✅ Single database query (LoadChildComments optimization)

### 4. Update Comment
```bash
PUT /api/v1/comments/1
{
    "content": "Updated content"
}
```
**Verify**:
- ✅ Only comment author or admin can update
- ✅ Updated content saved
- ✅ `updated_at` timestamp changed
- ✅ Activity logged

### 5. Delete Comment
```bash
DELETE /api/v1/comments/1
```
**Verify**:
- ✅ If comment has children: soft-delete (set `deleted` = true)
- ✅ If comment has no children: hard-delete (remove from database)
- ✅ Deleted comments show blank content in responses
- ✅ Activity logged

### 6. Notification Preferences
```bash
# Get notification settings and user's selections
GET /api/v1/notification-subscriptions/me

# Update preferences
PUT /api/v1/notification-subscriptions/me
{
    "selections": [
        {
            "notif_id": "comment_replied",
            "channels": {
                "browser": true,
                "email": false,
                "mobile": true
            }
        },
        {
            "notif_id": "user_followed",
            "channels": {
                "browser": true,
                "email": true,
                "mobile": false
            }
        }
    ]
}
```
**Verify**:
- ✅ Preferences saved to `notification_subscriptions`
- ✅ User can toggle channels per notification type
- ✅ Invalid notification types rejected
- ✅ Activity logged with selections count

### 7. Reset Preferences
```bash
POST /api/v1/notification-subscriptions/me/reset
```
**Verify**:
- ✅ All subscriptions reset to default (all channels enabled)
- ✅ Activity logged

### 8. Fetch Notifications
```bash
# Get paginated notifications
GET /api/v1/notifications?perPage=15

# Get unread count
GET /api/v1/notifications/unread-count
```
**Verify**:
- ✅ Paginated results returned
- ✅ Unread count accurate
- ✅ Notifications include action_url and metadata

### 9. Mark Notifications as Read
```bash
POST /api/v1/notifications/mark-as-read
{
    "ids": [1, 2, 3]
}

# Or mark all as read
POST /api/v1/notifications/mark-as-read
{
    "markAllAsRead": true
}
```
**Verify**:
- ✅ `read_at` timestamp set
- ✅ Unread count decreases
- ✅ Activity logged

### 10. Activity Logs
```bash
# Get user's notification activity
GET /api/v1/notifications/activity-logs

# Get activity for specific notification type
GET /api/v1/notifications/activity-logs/comment_replied
```
**Verify**:
- ✅ Logs show all notification activities
- ✅ Includes success/failure status
- ✅ Error messages captured for failed deliveries

## Troubleshooting

### Event Listener Not Firing
**Symptom**: Comment reply created, but notification not sent

**Check**:
1. Verify listener is registered in `CoreServiceProvider.php` boot method
2. Run: `php artisan event:list` to see registered events
3. Check `notification_activity_logs` for failed delivery attempts

### Notifications Not Being Received
**Symptom**: Notification created but not appearing in API

**Check**:
1. User's channel preferences: `notification_subscriptions` table
2. Verify notification preference is enabled for the channel
3. Check `notification_activity_logs` for delivery failures
4. Ensure `notifications` table has records (Laravel's built-in notifications)

### Wrong Notifications Sent
**Symptom**: User receiving wrong notification type

**Check**:
1. Verify `notif_id` in `BaseNotification` subclass matches config
2. Check listener is sending correct notification class
3. Verify `NOTIF_ID` constant is set correctly

### Database Errors After Migration
**Symptom**: Migration fails with constraint errors

**Check**:
1. Run: `php artisan migrate:refresh --step=3` (to roll back last 3 migrations)
2. Check foreign key constraints in migration files
3. Ensure all referenced tables exist before creating foreign keys

## API Endpoints Summary

### Comments
- `GET /api/v1/comments` - List comments (filtered)
- `POST /api/v1/comments` - Create comment
- `GET /api/v1/comments/{id}` - Get single comment
- `PUT /api/v1/comments/{id}` - Update comment
- `DELETE /api/v1/comments/{ids}` - Delete comments (comma-separated)
- `POST /api/v1/comments/{id}/restore` - Restore deleted comment
- `GET /api/v1/commentable-comments` - Get comments for model (with nesting)

### Notifications
- `GET /api/v1/notifications` - Get paginated notifications
- `GET /api/v1/notifications/unread-count` - Get unread count
- `POST /api/v1/notifications/mark-as-read` - Mark as read (batch)
- `DELETE /api/v1/notifications/{ids}` - Delete notifications
- `POST /api/v1/notifications/delete-all` - Delete all notifications
- `GET /api/v1/notifications/activity-logs` - Get activity logs
- `GET /api/v1/notifications/activity-logs/{type}` - Get logs by type

### Notification Subscriptions
- `GET /api/v1/notification-subscriptions/{user}` - Get settings + preferences
- `PUT /api/v1/notification-subscriptions/{user}` - Update preferences
- `POST /api/v1/notification-subscriptions/{user}/reset` - Reset to defaults

## Performance Notes

### Comment Loading Optimization
The `LoadChildComments` action uses a single database query regardless of nesting depth:
- Loads root comments with pagination
- Loads all descendants using single OR clause on path patterns
- Splices children into proper position for UI rendering

**Query count**: 2 queries (1 root pagination + 1 descendants)

### Notification Routing
Channel routing uses user preferences without additional queries:
- Preferences cached in memory during notification send
- Uses single query to fetch user's subscriptions
- Channels determined before sending

## Security Considerations

### Authorization
- Comments checked via `CommentPolicy` for create/update/delete
- Notification preferences only visible to own user (policy-based)
- Admin-only notifications require explicit permission

### Validation
- Comment content limited to 3-5000 characters
- Notification types validated against config
- Channels validated against available_channels config

### Audit Trail
- All notification activities logged with:
  - User ID
  - Notification type
  - Action (sent, read, deleted)
  - Channel
  - Success/failure status
  - IP address
  - Error messages (if failed)

## Configuration Extension

### Add Custom Notification Type
1. Add to `resources/defaults/notification-settings.php`:
```php
[
    'notif_id' => 'my_custom_notif',
    'name' => 'My Custom Notification',
    'description' => 'Description here',
    'permissions' => [], // Optional permission checks
]
```

2. Create notification class extending `BaseNotification`:
```php
class MyCustomNotification extends BaseNotification {
    public const NOTIF_ID = 'my_custom_notif';
    // ...
}
```

3. Send notification:
```php
$user->notify(new MyCustomNotification($data));
```

### Enable Email Notifications
1. Configure mail driver in `.env`
2. Create email template for notification
3. Implement `toMail()` in notification class

### Enable FCM Push Notifications
1. Set up Firebase Cloud Messaging credentials
2. Implement FCM sender service
3. Store user's FCM device token
4. Send to 'mobile' channel when enabled

## Support & Debugging

### Enable Query Logging
```php
// In debugbar or local config
\Illuminate\Support\Facades\DB::enableQueryLog();
// ... run code ...
dd(\Illuminate\Support\Facades\DB::getQueryLog());
```

### Check Event Dispatch
```php
use Illuminate\Support\Facades\Event;
Event::fake();
// ... run code ...
Event::assertDispatched(CommentReplyCreated::class);
```

### Inspect Activity Logs
```sql
SELECT * FROM notification_activity_logs 
WHERE user_id = ? 
ORDER BY created_at DESC 
LIMIT 50;
```

## Rollback Procedure

If needed to rollback:
```bash
# Rollback last 3 migrations (comments, subscriptions, activity logs)
php artisan migrate:rollback --step=3
```

This will drop the three new tables but keep existing data intact.
