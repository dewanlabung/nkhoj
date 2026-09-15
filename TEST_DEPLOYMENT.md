# Comment & Notification System - Testing Guide

After running the deployment script, follow this testing checklist to verify everything works correctly.

## Prerequisites
- Database migrations completed
- User subscriptions initialized
- Access to API endpoints
- Test user ID (replace `{userId}` with an actual user ID)
- Authentication token (for protected endpoints)

## 1. Verify Database Setup

### Check Tables Exist
```bash
php artisan tinker
>>> Schema::getTables()  # Should show: comments, notification_subscriptions, notification_activity_logs
>>> exit
```

### Check Notification Subscriptions
```bash
php artisan tinker
>>> $user = User::first();
>>> $user->notificationSubscriptions()->count()  # Should be > 0 (at least 11 subscription types)
>>> $user->notificationSubscriptions()->first()->toArray()  # Verify structure
>>> exit
```

## 2. Test Comment Creation

### Create a Root Comment
```bash
curl -X POST http://localhost:8000/api/v1/comments \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "This is a test comment!",
    "commentable_type": "App\\Domains\\Blog\\Models\\Post",
    "commentable_id": 1
  }'
```

**Expected Response:**
- Status: 201 Created
- Response contains `id`, `content`, `user_id`, `commentable_type`, `commentable_id`
- `parent_id` should be null (root comment)
- `path` should equal the comment ID

### Create a Reply Comment
```bash
curl -X POST http://localhost:8000/api/v1/comments \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Thanks for the feedback!",
    "commentable_type": "App\\Domains\\Blog\\Models\\Post",
    "commentable_id": 1,
    "inReplyTo": {
      "id": 1,
      "user": {
        "id": 2
      }
    }
  }'
```

**Expected Response:**
- Status: 201 Created
- `parent_id` should be 1 (the comment ID we replied to)
- `path` should be "1/X" (hierarchical path)
- Notification should be created for user 2

## 3. Test Notification Delivery

### Check if Notification Was Sent
```bash
php artisan tinker
>>> $user = User::find(2);  # The person who created the original comment
>>> $user->notifications()->latest()->first()->toArray()  # Should see CommentReceivedReply notification
>>> exit
```

### Check Activity Log
```bash
php artisan tinker
>>> $logs = DB::table('notification_activity_logs')
    ->where('notif_type', 'comment_replied')
    ->latest()
    ->first();
>>> dd($logs);  # Should show success = 1 (true)
>>> exit
```

## 4. Test Notification Preferences

### Get User's Preferences
```bash
curl http://localhost:8000/api/v1/notification-subscriptions/{userId} \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected Response:**
- Status: 200 OK
- Contains `subscriptions` array with all notification types
- Contains `user_selections` with current user's preferences
- Each selection has channels (browser, email, mobile)

### Update Preferences
```bash
curl -X PUT http://localhost:8000/api/v1/notification-subscriptions/{userId} \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
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
        "notif_id": "post_commented",
        "channels": {
          "browser": true,
          "email": true,
          "mobile": false
        }
      }
    ]
  }'
```

**Expected Response:**
- Status: 200 OK
- Message: "Notification preferences updated successfully"

### Verify Preferences Were Saved
```bash
php artisan tinker
>>> $sub = NotificationSubscription::where('user_id', {userId})->where('notif_id', 'comment_replied')->first();
>>> dd($sub->channels);  # Should show: {"browser": true, "email": false, "mobile": true}
>>> exit
```

## 5. Test Comment Loading

### Get Comments for a Post
```bash
curl 'http://localhost:8000/api/v1/commentable-comments?commentable_type=App%5CDomains%5CBlog%5CModels%5CPost&commentable_id=1' \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected Response:**
- Status: 200 OK
- Paginated array of root comments
- Nested children included in each comment
- `depth` attribute showing nesting level (0 for root, 1+ for replies)

### Get All Comments (No Nesting)
```bash
curl 'http://localhost:8000/api/v1/comments?commentable_id=1' \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected Response:**
- Status: 200 OK
- Flat list of all comments for the post
- Can be paginated with `?page=2&perPage=15`

## 6. Test Comment Updates

### Update a Comment
```bash
curl -X PUT http://localhost:8000/api/v1/comments/{commentId} \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Updated comment content"
  }'
```

**Expected Response:**
- Status: 200 OK
- `updated_at` timestamp should be recent
- `content` should be updated

## 7. Test Comment Deletion

### Delete a Comment (Soft Delete)
```bash
curl -X DELETE http://localhost:8000/api/v1/comments/{commentId} \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected Response:**
- Status: 200 OK if successful
- If comment has children: soft-deleted (deleted flag = true)
- If comment has no children: hard-deleted (removed from database)

### Verify Deletion
```bash
php artisan tinker
>>> $comment = Comment::find({commentId});
>>> // If soft-deleted: $comment->deleted should be true
>>> // If hard-deleted: $comment should be null
>>> exit
```

## 8. Test Notification Endpoints

### Get Unread Count
```bash
curl http://localhost:8000/api/v1/notifications/unread-count \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected Response:**
- Status: 200 OK
- JSON: `{"success": true, "unread_count": X}`

### Get Paginated Notifications
```bash
curl 'http://localhost:8000/api/v1/notifications?perPage=15' \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected Response:**
- Status: 200 OK
- Paginated list of notifications
- Each notification has metadata (from, action_url, etc.)

### Mark Notification as Read
```bash
curl -X POST http://localhost:8000/api/v1/notifications/mark-as-read \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "ids": [1, 2, 3]
  }'
```

**Expected Response:**
- Status: 200 OK
- Message: "Notifications marked as read"
- `unread_count` decreased

## 9. Test Activity Logging

### Get Activity Logs
```bash
curl http://localhost:8000/api/v1/notifications/activity-logs \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected Response:**
- Status: 200 OK
- Array of activity logs for the user
- Each log has: `user_id`, `notif_type`, `action`, `channel`, `success`, `created_at`

### Get Logs for Specific Type
```bash
curl http://localhost:8000/api/v1/notifications/activity-logs/comment_replied \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected Response:**
- Status: 200 OK
- Filtered logs for comment_replied notification type

## 10. Authorization Tests

### Test Non-Owner Cannot Update Another's Comment
```bash
# Login as user A, try to update user B's comment
curl -X PUT http://localhost:8000/api/v1/comments/{userBCommentId} \
  -H "Authorization: Bearer USER_A_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"content": "Hacked!"}'
```

**Expected Response:**
- Status: 403 Forbidden

### Test Non-Owner Cannot Delete Another's Comment
```bash
curl -X DELETE http://localhost:8000/api/v1/comments/{userBCommentId} \
  -H "Authorization: Bearer USER_A_TOKEN"
```

**Expected Response:**
- Status: 403 Forbidden

### Test Admin Can Bypass Restrictions
```bash
# Login as admin, update any comment
curl -X PUT http://localhost:8000/api/v1/comments/{anyCommentId} \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"content": "Admin update"}'
```

**Expected Response:**
- Status: 200 OK (if admin has update permission)

## Debugging

### Check Database State
```bash
php artisan tinker
>>> Comment::with('user', 'commentable')->latest()->first()
>>> NotificationSubscription::with('user')->latest()->first()
>>> NotificationActivityLog::latest()->first()
>>> exit
```

### Monitor Query Log
```bash
php artisan tinker
>>> DB::enableQueryLog();
>>> // ... run your API call or code ...
>>> dd(DB::getQueryLog());
>>> exit
```

### Test Event Dispatch
```bash
php artisan tinker
>>> Event::fake();
>>> // Create a reply comment
>>> Comment::create([...]);
>>> Event::assertDispatched(\App\Core\Events\CommentReplyCreated::class);
>>> exit
```

## Performance Testing

### Load Testing - Create Many Comments
```bash
php artisan tinker
>>> for ($i = 0; $i < 100; $i++) {
    Comment::create([
        'content' => "Test comment $i",
        'user_id' => 1,
        'commentable_type' => 'App\\Domains\\Blog\\Models\\Post',
        'commentable_id' => 1
    ]);
}
>>> exit
```

### Test Nested Load Performance
```bash
php artisan tinker
>>> // Measure time to load 100+ nested comments
>>> $start = microtime(true);
>>> $comments = app(\App\Core\Actions\LoadChildComments::class)->execute(...);
>>> $time = microtime(true) - $start;
>>> echo "Loaded in: $time seconds";
>>> exit
```

**Expected:**
- Should load in < 100ms even with hundreds of comments
- Should use only 2 queries (root + descendants)

## Common Issues & Solutions

### Issue: "Column not found" error
**Solution:** Ensure migrations have run: `php artisan migrate`

### Issue: Notifications not being created
**Solution:** Check event listener registration in CoreServiceProvider, verify listener is in correct namespace

### Issue: Wrong notification type appears
**Solution:** Verify NOTIF_ID constant matches config in notification-settings.php

### Issue: User subscriptions weren't created
**Solution:** Run: `php artisan notifications:subscribe-users --force`

### Issue: Comments don't appear nested
**Solution:** Verify LoadChildComments action is being used, check database for path values

## Success Criteria

✅ All 10 test sections pass
✅ Comments create/read/update/delete work correctly
✅ Notifications are sent when comments are replied to
✅ User preferences are saved and respected
✅ Activity logs capture all actions
✅ Authorization prevents unauthorized access
✅ Soft/hard delete strategy works as expected
✅ Performance is acceptable (2 queries for nested comments)

If all tests pass, your deployment is complete and production-ready!
