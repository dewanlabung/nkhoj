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

### Performance
- [x] Homepage caching — all non-paginated `HomeController` queries in `Cache::remember()` (5–15 min TTL)
- [x] Rate limiting — `throttle` middleware on search (30/min), autocomplete (60/min), comments (15/min), reactions/polls (30/min)

### SEO / Structured Data
- [x] JSON-LD `NewsArticle` schema on `posts/show.blade.php` (pre-existing)
- [x] JSON-LD `Event` schema on `events/show.blade.php`
- [x] JSON-LD `Recipe` schema on `recipes/show.blade.php`

### Database
- [x] FULLTEXT index on `posts` (pre-existing migration `2026_09_09_100001`)
- [x] FULLTEXT indexes on `events` (title, description), `social_pages` (name, bio), `questions` (title, content), `recipes` (title, description) — migration `2026_09_10_000001`
- [x] Soft deletes (`deleted_at`) on `posts`, `comments`, `questions` tables — migration `2026_09_10_000002`
- [x] `SoftDeletes` trait added to `Post`, `Comment`, `Question` models

### PWA
- [x] `manifest.json` (pre-existing)
- [x] `sw.js` service worker with network-first caching (pre-existing)

### UX / Frontend
- [x] Reading time display on post show page (pre-existing)
- [x] `loading="lazy"` on images: post cards, series index/show, home editor picks, widget partials

---

## 🔲 Not Yet Implemented (candidate backlog)

> Suggestions from this list will be offered in future recommendations. Cross off when done.

- [ ] Queued/async notifications — extract `Notification::create()` calls into queued jobs (needs queue driver config on cPanel)
- [ ] FULLTEXT `MATCH()…AGAINST()` in `SearchController` with LIKE fallback (currently all queries still use LIKE)
- [ ] Cache invalidation — clear relevant cache keys when posts/categories/widgets are updated in admin
- [ ] Infinite scroll on home feed (replace or augment current "load more" / pagination)
- [ ] Dark-mode persistence via `localStorage` (if not already persisted)
- [ ] Image WebP conversion / responsive `srcset` on upload
- [ ] OpenGraph / Twitter card meta tags on event and recipe show pages
- [ ] Sitemap entries for events, recipes, questions, social pages
- [ ] Email digest / newsletter from published posts
- [ ] Two-factor authentication (TOTP) for admin/author accounts
- [ ] Post scheduling UI improvements (calendar picker in editor)
- [ ] Comment threading depth limit + "load more replies" for deep threads
- [ ] Tag-based content recommendations at bottom of post show page
- [ ] Author follow notification email (when someone follows you)
- [ ] Search analytics dashboard in admin (top queries, zero-result queries)
- [ ] Bookmark collections sharing (public shareable URL)
- [ ] Social page post scheduling
- [ ] Recipe rating / review system
- [ ] Event RSVP / attendance tracking
- [ ] Leaderboard caching (currently uncached)
