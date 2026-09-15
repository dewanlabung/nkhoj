# Database Quick Reference

Quick lookup for managing your 112-table database organization.

---

## Module Quick Links

| Module | Tables | Size | Key Tables |
|--------|--------|------|-----------|
| [Core](#core) | 11 | 320 KB | users, roles, settings |
| [Blog](#blog) | 18 | 690 KB | posts, comments, tags |
| [QnA](#qna) | 14 | 644 KB | questions, answers, votes |
| [Social Pages](#social-pages) | 28 | 1.35 MB | social_pages, page_posts |
| [Media & Content](#media--content) | 15 | 912 KB | recipes, events, reels |
| [**Notifications (NEW)**](#notifications-new) | **4** | **192 KB** | **notification_subscriptions** |
| [User & Engagement](#user--engagement) | 18 | 608 KB | followers, conversations |
| [Memberships](#memberships) | 6 | 176 KB | subscriptions, plans |
| [Support](#support) | 3 | 96 KB | support_tickets |
| [Logging & Analytics](#logging--analytics) | 11 | 400 KB | activity_logs, search_logs |
| [Configuration](#configuration) | 4 | 176 KB | surveys, rss_feeds |

---

## Common Tasks

### Find Tables by Feature

**Need to work with comments?**
```
- comments (128 KB) ← Core comment table
- comment_reactions (64 KB) ← Emoji reactions
```

**Need to work with posts?**
```
- posts (160 KB) ← Core posts
- post_tag (32 KB) ← Tags
- post_series (48 KB) ← Series grouping
- bookmarks (64 KB) ← Saved posts
```

**Need to work with notifications?** *(NEW)*
```
- notification_subscriptions (48 KB) ← User preferences
- notification_activity_logs (64 KB) ← Audit trail
- notifications (32 KB) ← Notification records
```

**Need to work with user profiles?**
```
- users (80 KB) ← Main user table
- user_sessions (48 KB) ← Sessions
- user_wallets (48 KB) ← Credits/balance
- followers (48 KB) ← Relationships
```

---

## Query Tips

### Fast Comment Loading
```php
// ✅ GOOD - Uses indexes
$comments = Comment::where('commentable_type', 'Post')
    ->where('commentable_id', 1)
    ->approved()
    ->with('user')
    ->latest()
    ->paginate();

// ❌ BAD - N+1 problem
$comments = Comment::all();
foreach ($comments as $c) {
    echo $c->user->name; // Separate query per comment!
}
```

### Fast Post Loading
```php
// ✅ GOOD - Eager load everything
$post = Post::with([
    'author',
    'category',
    'tags',
    'comments.user' // NEW
])
->where('slug', 'my-post')
->first();

// ❌ BAD - Multiple separate queries
$post = Post::where('slug', 'my-post')->first();
$author = User::find($post->author_id);
$tags = Tag::whereHas('posts', fn($q) => $q->where('id', $post->id))->get();
```

---

## Maintenance Checklist

### Weekly
- [ ] Check database size: `php artisan db:health-check`
- [ ] Verify backups completed: `ls -lh /home/alphaome/backups/`

### Monthly
- [ ] Archive logs older than 90 days
- [ ] Check for slow queries
- [ ] Review table growth rates

### Quarterly
- [ ] Full database audit
- [ ] Performance review
- [ ] Update this documentation

---

## Common Issues & Solutions

### Issue: Comments not appearing
**Check:**
```php
// 1. Verify relationship is loaded
$post = Post::with('comments')->find(1);

// 2. Check if comments are approved
Comment::where('deleted', false)->count();

// 3. Verify user relationship
$comment->user()->count();
```

### Issue: Slow queries
**Solution:**
```bash
# Check table sizes
php artisan db:health-check

# Analyze query performance
# Add EXPLAIN before SELECT:
EXPLAIN SELECT * FROM comments WHERE user_id = 1;

# Add missing index if needed
ALTER TABLE comments ADD INDEX idx_user_id (user_id);
```

### Issue: Disk space growing
**Solution:**
```bash
# Archive old logs (keep 90 days)
DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
DELETE FROM api_audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

# Optimize tables
OPTIMIZE TABLE activity_logs, api_audit_logs, search_logs;
```

---

## Database Sizes by Module

```
Core                 320 KB  ████
Blog                 690 KB  █████████
QnA                  644 KB  ████████
Social Pages       1,350 KB  ███████████████████
Media & Content      912 KB  ██████████
Notifications        192 KB  ██ ← NEW
User & Engagement    608 KB  ███████
Memberships          176 KB  ██
Support               96 KB  █
Logging & Analytics  400 KB  █████
Configuration        176 KB  ██
─────────────────────────────────────
TOTAL              5,340 KB  (5.34 MB)
```

---

## New Notification System Tables

### notification_subscriptions (48 KB)
Stores user preferences for receiving notifications.

```
Fields: id (UUID), user_id, notif_id, channels (JSON)
Example: 
{
  "notif_id": "comment_replied",
  "channels": {
    "browser": true,
    "email": true,
    "mobile": false
  }
}
```

**Query Examples:**
```php
// Get user's notification preferences
$prefs = NotificationSubscription::where('user_id', auth()->id())->get();

// Check if user wants email for comment replies
$sub = NotificationSubscription::where('user_id', auth()->id())
    ->where('notif_id', 'comment_replied')
    ->first();

if ($sub && $sub->channels['email']) {
    // Send email
}
```

### notification_activity_logs (64 KB)
Tracks all notification deliveries for audit.

```
Fields: id, user_id, notif_type, action, channel, success, error_message, created_at
```

**Query Examples:**
```php
// Check if notification was sent successfully
$sent = NotificationActivityLog::where('user_id', 1)
    ->where('notif_type', 'comment_replied')
    ->where('success', true)
    ->latest()
    ->first();

// Find failed notifications
$failed = NotificationActivityLog::where('success', false)
    ->where('created_at', '>', now()->subHours(24))
    ->get();
```

---

## Performance Benchmarks

Expected query performance on current database:

| Operation | Expected Time | Notes |
|-----------|---------------|-------|
| Load 100 comments with users | <50ms | Uses indexes |
| Create comment + notify | <200ms | Includes event dispatch |
| List all posts | <100ms | Full table scan OK (160KB) |
| Search in 10K posts | <200ms | Uses fulltext index |
| Get user profile | <50ms | Single row + relations |
| Load notifications page | <100ms | Indexed queries |

*Baseline: Empty database. Times scale linearly with data growth.*

---

## Useful Commands

```bash
# Check database health
php artisan db:health-check

# Backup database
/home/alphaome/backups/daily-backup.sh

# List all tables
mysql -u alphaome_user -p alphaome_dewnkhoj -e "SHOW TABLES;"

# Check table sizes
php artisan db:show

# Run migrations
php artisan migrate

# Initialize notifications
php artisan notifications:subscribe-users --force
```

---

## When to Optimize

| Signal | Action |
|--------|--------|
| Database >10 MB | Review and archive old logs |
| Database >100 MB | Consider table partitioning |
| Database >1 GB | Consider database replication |
| Single table >500 MB | Plan for archiving strategy |

---

**Current Status:** Excellent ✅
- Database size: 5.34 MB (plenty of room)
- All tables organized by module
- Indexes in place
- Backup automation active
- Notification system integrated

**Next Milestone:** Check in Q4 2026 (quarterly review)

