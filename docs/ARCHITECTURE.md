# nkhoj — System Architecture

> **नखोज** · Nepali community news & blogging platform  
> Stack: Laravel 11 · PHP 8.4 · MySQL 8.0 · Alpine.js v3 · Tailwind CSS · LiteSpeed (shared hosting)

---

## 1. Technology Stack

| Layer | Technology |
|-------|-----------|
| Language | PHP 8.4 |
| Framework | Laravel 11 |
| Database | MySQL 8.0 |
| Frontend JS | Alpine.js v3 (no build step) |
| CSS | Tailwind CSS (CDN play-CDN) |
| Rich text editor | TinyMCE |
| Auth | Laravel session-based + Laravel Socialite (OAuth) |
| API tokens | Laravel Sanctum |
| HTTP client | Guzzle 7 |
| Hosting | LiteSpeed shared hosting (cPanel, lsphp84) |

---

## 2. Directory Structure

```
nkhoj/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # All request handlers
│   │   │   ├── Admin/            # Admin panel controllers (merged into AdminController)
│   │   │   ├── Api/              # Internal JSON API (AIController)
│   │   │   └── Auth/             # AuthController, SocialAuthController
│   │   └── Middleware/
│   │       └── TrackLastSeen.php # Updates user.last_seen_at on every request
│   └── Models/                   # 40+ Eloquent models
├── database/
│   └── migrations/               # ~40 migration files
├── resources/
│   └── views/
│       ├── layouts/              # app.blade.php, admin.blade.php
│       ├── partials/             # Reusable fragments (header, footer, widgets)
│       ├── home.blade.php        # Homepage
│       ├── admin/                # Admin panel views (~35 pages)
│       ├── dashboard/            # User dashboard (create, edit, index)
│       ├── posts/                # Post show page
│       ├── recipes/              # Recipe community
│       ├── events/               # Events community
│       └── ...                   # categories, tags, profile, search, etc.
├── routes/
│   ├── web.php                   # ~425 lines, all web routes
│   └── api.php                   # Internal AI API (throttled)
├── storage/
│   └── app/
│       └── site_settings.json    # Flat-file site settings store
└── public/
    └── uploads/                  # User-uploaded images (moved directly to public)
```

---

## 3. Application Layers

### 3.1 Routing

All routes live in `routes/web.php`. Groups:

| Group | Prefix | Middleware | Purpose |
|-------|--------|-----------|---------|
| Public | — | none | Homepage, posts, categories, tags, search |
| Auth guest | — | `guest` | Login, register pages |
| Auth required | — | `auth` | Bookmarks, notifications, dashboard, account |
| Admin | `/admin` | `auth` (+ role check inside controller) | Full admin panel |
| Dashboard | `/dashboard` | `auth` | Author content management |
| API | `/api` | `throttle:60,1` | Internal AI endpoints |
| Webhook | `/webhook/deploy` | none (HMAC signature) | GitHub auto-deploy |

### 3.2 Controllers

| Controller | Responsibility |
|-----------|---------------|
| `HomeController` | Homepage: hero strip, editor's pick, paginated feed, widgets |
| `PostController` | Single post page, view count increment |
| `DashboardController` | Author CRUD for posts (articles, events) |
| `AdminController` | Monolithic admin panel — users, posts, categories, settings, deploy (~1400 lines) |
| `AuthController` | Login, register, logout (session) |
| `SocialAuthController` | OAuth via Socialite (Google, Facebook, etc.) |
| `SearchController` | Full-text search across posts, tags, users |
| `CategoryController` | Category archive page |
| `TagController` | Tag archive page |
| `CommentController` | Post comments (guest + auth) |
| `ReactionController` | Emoji reactions (guest + auth) |
| `BookmarkController` | Polymorphic bookmarks (auth) |
| `ProfileController` | User public profile, follow/unfollow |
| `NotificationController` | In-app notifications |
| `MembershipController` | Subscription plans, Stripe integration |
| `PollController` | Voting polls |
| `RecipeController` | Recipe community |
| `EventController` | Events community |
| `ContactController` | Contact form, newsletter subscribe |
| `SitemapController` | XML sitemap & RSS feed |
| `PageController` | Static CMS pages |
| `SocialPageController` | Social media pages |
| `Api/AIController` | Claude AI — content generation, moderation, newsletter |

### 3.3 Models & Relationships

```
User ──< Post (author_id)
User ──< Comment
User ──< Reaction
User ──< Bookmark (polymorphic: bookmarkable)
User ──< Notification
User ──< Subscription ──> MembershipPlan
User ──< SocialAccount (OAuth)
User ──< LoginHistory
User >──< User (followers/following, self-join)

Post ──> Category
Post >──< Tag (post_tag pivot)
Post ──< Comment
Post ──< Reaction
Post ──< Bookmark (as bookmarkable)

Poll ──< PollOption
PollOption ──< PollVote

Question ──< Answer
SupportTicket ──< SupportReply

Recipe ──< RecipeLike
Event ──< EventAttendee

Survey ──< SurveyQuestion ──< SurveyOption ──< SurveyResponse
```

### 3.4 User Roles

| Role | Access |
|------|--------|
| `admin` | Full admin panel, all settings, user management, deploy |
| `editor` | Admin panel (content only) |
| `reporter` | Dashboard (write posts) |
| `member` | Dashboard (write posts), profile, bookmarks |

Roles are stored as a single string column `users.role`. Extra per-user permissions are stored as JSON in `users.extra_permissions`.

---

## 4. Data Storage

### Database (MySQL 8.0)

Core tables:

| Table | Key columns |
|-------|------------|
| `users` | uuid, name, username, email, role, balance, ai_credits_used |
| `posts` | uuid, author_id, category_id, slug, title, body (JSON), status, is_featured, is_pro, view_count, published_at, post_format |
| `categories` | name_en, name_ne, slug, sort_order |
| `tags` | name_en, name_ne, slug |
| `post_tag` | post_id, tag_id (pivot) |
| `comments` | post_id, user_id, body, is_approved |
| `reactions` | post_id, user_id, type |
| `bookmarks` | user_id, bookmarkable_type, bookmarkable_id (polymorphic) |
| `widgets` | type, position, title, config (JSON) |
| `ad_zones` | name, position, code (HTML/JS embed) |
| `polls` / `poll_options` / `poll_votes` | Voting system |
| `membership_plans` / `subscriptions` | Paid membership |
| `support_tickets` / `support_replies` | Help desk |
| `recipes` / `events` | Community content types |

### Flat-file Settings

`storage/app/site_settings.json` — stores all site configuration (site name, logo, social links, SMTP, Stripe keys, Google Analytics ID, etc.) as a JSON object. Read/written directly by `AdminController`.

### File Uploads

User-uploaded images are moved directly to `public/uploads/` via `move()`, served as `/uploads/filename`. No cloud storage integration.

---

## 5. Frontend Architecture

No build step. All JS/CSS is loaded from CDN or inline.

| Concern | Approach |
|---------|---------|
| Reactivity | Alpine.js v3 (`x-data`, `x-bind`, `x-show`, `x-model`) |
| Styling | Tailwind CSS play-CDN |
| Rich text | TinyMCE (CDN) |
| Dark mode | Tailwind `dark:` classes + `data-theme` attribute on `<html>` |
| AJAX tabs | Native `fetch()` in Alpine — category pill switching reloads `#posts-feed` |
| Infinite scroll | Not implemented; standard Laravel `paginate()` links |
| Theme toggle | `ThemeController` sets a cookie + `data-theme` attribute |

### Layout System

```
layouts/app.blade.php          ← public-facing wrapper (header, sidebar, footer)
    └── partials/header.blade.php
    └── partials/footer.blade.php
    └── @yield('content')      ← page content

layouts/admin.blade.php        ← admin panel wrapper (sidebar nav, topbar)
    └── @yield('content')
```

---

## 6. Key Features

### Content Pipeline

```
Author writes post → Dashboard editor (TinyMCE)
    → DashboardController::store()
    → Post::create() with status=draft or published
    → syncTags() — upserts tags, syncs pivot
    → Redirect to /dashboard
```

Post body is stored as a JSON array of blocks: `[{ "type": "paragraph", "content": "..." }]`.

### Widget System

Admins configure sidebar/home widgets in `/admin/widgets`. Each widget has:
- `type` — e.g. `popular_posts`, `popular_tags`, `voting_poll`, `about_us`, `follow_us`, `recommended_posts`
- `position` — `sidebar`, `home_top`, `home_bottom`
- `config` — JSON blob with type-specific options (title, count, etc.)

`HomeController` pre-fetches data for all active widget types and passes it to `partials/_widget.blade.php`.

### AI Integration

Internal API at `/api/ai/*` (throttled 60 req/min). Uses Claude (Anthropic) via Guzzle:
- `author` — content generation for article writing assistance
- `moderation` — comment/content moderation
- `search` — semantic search enhancement
- `survey` / `newsletter` — automated content

### Deploy Pipeline

Admin-triggered from `/admin/deploy`:
1. `git fetch origin master`
2. `git reset --hard origin/master`
3. `composer install --no-dev --optimize-autoloader`
4. `php artisan migrate --force`
5. `php artisan cache:clear`
6. `php artisan config:clear`
7. `php artisan config:cache`
8. `php artisan view:clear`
9. `php artisan route:cache`

Also available via GitHub webhook at `/webhook/deploy` (HMAC-signed).

---

## 7. Authentication & Security

| Feature | Implementation |
|---------|--------------|
| Session auth | Laravel default (bcrypt passwords) |
| OAuth | Laravel Socialite — Google, Facebook, GitHub, etc. |
| API tokens | Laravel Sanctum |
| CSRF | Laravel default on all POST/PUT/DELETE |
| Rate limiting | `throttle:60,1` on API routes |
| Admin gate | `requireAdmin()` / `requireEditorOrAdmin()` helper methods in `AdminController` |
| User banning | `users.is_banned` flag, checked at login |
| Login history | `login_histories` table — IP, user agent, timestamp |
| Deploy webhook | HMAC-SHA256 signature verification (optional, via `DEPLOY_SECRET` env) |

---

## 8. Membership & Monetisation

- Plans stored in `membership_plans` (name, price, interval, features JSON)
- Subscriptions in `subscriptions` (user_id, plan_id, status, payment_method)
- Posts can be marked `is_pro = true` → members-only
- Balance tracking on `users.balance` for manual/reward credits
- Stripe integration planned (public key stored in site settings)
