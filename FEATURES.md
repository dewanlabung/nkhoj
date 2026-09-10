# Nkhoj — Features & Improvements Tracker

This file tracks all implemented features and improvements so recommendations are never duplicated.

---

## ✅ Implemented

### Search
- [x] Fix production 500: `Unknown column 'category'` on `social_pages` — replaced with `page_type` / `bio`
- [x] Integrate Recipes into search (title, description, cuisine_type, meal_type)
- [x] Relevance ranking via `CASE WHEN title LIKE ? THEN 0 ELSE 1 END` on all search types
- [x] FB Lite-style search UI — compact sticky search bar, flat `divide-y` list rows
- [x] Recent search history on empty state (clock icon, last 8 unique searches)
- [x] "People you may know" suggestion list on empty state
- [x] Horizontal-scroll filter tabs with count badges (All, लेखहरू, Pages, Events, Recipes, People, Q&A)
- [x] Live search autocomplete — `GET /search/suggest` JSON endpoint + Alpine.js debounced dropdown in header
- [x] FULLTEXT `MATCH()…AGAINST()` in `SearchController` with LIKE fallback

### Performance
- [x] Homepage caching — all non-paginated `HomeController` queries in `Cache::remember()` (5–15 min TTL)
- [x] Rate limiting — `throttle` middleware on search (30/min), autocomplete (60/min), comments (15/min), reactions/polls (30/min)
- [x] Cache invalidation — `flushPostCaches()` / `flushWidgetCaches()` called in `AdminController` on post/category/widget changes
- [x] Leaderboard caching — all three leaderboard queries wrapped in `Cache::remember()` (600s TTL)

### SEO / Structured Data
- [x] JSON-LD `NewsArticle` schema on `posts/show.blade.php` (pre-existing)
- [x] JSON-LD `Event` schema on `events/show.blade.php`
- [x] JSON-LD `Recipe` schema on `recipes/show.blade.php`
- [x] OpenGraph / Twitter card meta tags on `events/show.blade.php`
- [x] OpenGraph / Twitter card meta tags on `recipes/show.blade.php`
- [x] Sitemap entries for events, recipes, questions, social pages

### Database
- [x] FULLTEXT index on `posts` (pre-existing migration `2026_09_09_100001`)
- [x] FULLTEXT indexes on `events` (title, description), `social_pages` (name, bio), `questions` (title, content), `recipes` (title, description) — migration `2026_09_10_000001`
- [x] Soft deletes (`deleted_at`) on `posts`, `comments`, `questions` tables — migration `2026_09_10_000002`
- [x] `SoftDeletes` trait added to `Post`, `Comment`, `Question` models
- [x] `recipe_ratings` table + `rating_avg` / `ratings_count` cached columns — migration `2026_09_10_000003`
- [x] Laravel `jobs`, `job_batches`, `failed_jobs` tables for database queue driver — migration `2026_09_10_000004`
- [x] `two_factor_secret`, `two_factor_enabled`, `two_factor_confirmed_at` columns on `users` — migration `2026_09_10_000005`

### PWA
- [x] `manifest.json` (pre-existing)
- [x] `sw.js` service worker with network-first caching (pre-existing)

### UX / Frontend
- [x] Reading time display on post show page (pre-existing)
- [x] `loading="lazy"` on images: post cards, series index/show, home editor picks, widget partials
- [x] Tag-based content recommendations ("तपाईंलाई मन पर्न सक्छ") on post show page sidebar
- [x] Recipe star rating widget (Alpine.js 1–5 stars + review textarea, cached avg + count)

### Notifications & Email
- [x] Queued/async notifications — `CreateNotification` queued job replaces inline `Notification::create()` in CommentController, DashboardController, ProfileController
- [x] Author follow notification email — `NewFollowerMail` queued mailable, dispatched from ProfileController on follow
- [x] Search analytics dashboard in admin (`/admin/search-analytics`) — top queries, zero-result queries, daily volume chart

### Security
- [x] Two-factor authentication (TOTP) — `pragmarx/google2fa-laravel` + `bacon/bacon-qr-code`; setup/confirm/disable/challenge flows; integrated into login; UI on `/account/security`

---

## 🔲 Not Yet Implemented (candidate backlog)

> Suggestions from this list will be offered in future recommendations. Cross off when done.

- [ ] Infinite scroll on home feed (replace or augment current pagination)
- [ ] Dark-mode persistence via `localStorage` (if not already persisted)
- [ ] Image WebP conversion / responsive `srcset` on upload
- [ ] Post scheduling UI improvements (calendar picker in editor)
- [ ] Comment threading depth limit + "load more replies" for deep threads
- [ ] Bookmark collections sharing (public shareable URL)
- [ ] Social page post scheduling
- [ ] Event RSVP attendance export (CSV/PDF for organizers)
- [ ] Post reaction analytics (which reactions, by whom)
- [ ] Author earnings / monetization dashboard
- [ ] Mobile push notifications (Web Push API)
- [ ] AI-powered auto-tagging on post publish
