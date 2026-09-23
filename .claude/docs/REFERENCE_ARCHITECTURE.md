# NKHOJ Reference Architecture Guide

**Status**: 📚 Reference Documentation  
**References**: `.claude/varient/` + `.claude/app/`  
**Date**: 2026-09-23

---

## Overview

This document provides reference architecture patterns extracted from:
1. **Varient** - Newsletter & search system patterns (CodeIgniter 4)
2. **NKHOJ App** - Existing application reference structure (Laravel)
3. **Newsletter Implementation** - Multi-provider email system

---

## 1. Search & Homepage Card System (Varient Reference)

### Architecture

```
┌─────────────────────────────────────────┐
│      Search/Homepage UI                 │
│  (Grid layout: 2-column card display)   │
└────────────┬────────────────────────────┘
             │
┌────────────▼────────────────────────────┐
│    SearchController / HomeController    │
│  (Query posts, paginate, filter)        │
└────────────┬────────────────────────────┘
             │
┌────────────▼────────────────────────────┐
│    Post Model & Query Builder           │
│  (Build search query, apply filters)    │
└────────────┬────────────────────────────┘
             │
        ┌────▼────┐
        │Database │
        └─────────┘
```

### Varient Search Implementation

**Location**: `.claude/varient/Upload/app/Views/themes/classic/search.php`

**Key Features**:
- Grid layout with 2-column post cards
- "Load More" pagination (AJAX-based)
- Post item component reuse (`_post_item` partial)
- Ad space integration (between posts)
- Search result counter
- No results message

**Code Pattern**:
```php
// search.php
<div class="col-sm-6 col-xs-12">
    <?= loadView("post/_post_item", ["post" => $post, 'showLabel' => true]); ?>
</div>
```

### Recommended NKHOJ Search Implementation

**Apply Varient pattern to NKHOJ**:

```php
// resources/views/search/index.blade.php
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @forelse($posts as $post)
        <x-post-card :post="$post" />
    @empty
        <p class="text-center text-gray-500">No results found</p>
    @endforelse
</div>

<!-- Load More Button -->
<button onclick="loadMorePosts({{ $page }})">
    Load More
</button>
```

**AJAX Progressive Loading**:
```javascript
async function loadMorePosts(page) {
    const response = await fetch(`/search?q=${query}&page=${page}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    
    const html = await response.text();
    document.getElementById('posts-container').insertAdjacentHTML('beforeend', html);
}
```

---

## 2. Newsletter System Implementation

### Architecture

```
┌──────────────────────────────────────────┐
│      Admin Newsletter Dashboard          │
│  (Create → Send → Track → Analytics)     │
└──────────────┬───────────────────────────┘
               │
       ┌───────▼────────┐
       │ NewsletterService
       │ (Orchestrator)
       └───────┬────────┘
               │
    ┌──────────┴──────────┐
    │                     │
┌───▼────────┐      ┌────▼──────┐
│EmailService│      │EmailCampaign
│ Factory    │      │ Model
└────────────┘      └───────────┘
```

### Implementation Status

| Component | File | Status |
|-----------|------|--------|
| **Models** | `app/Domains/Newsletter/Models/` | ✅ Complete |
| **Service** | `app/Services/Newsletter/NewsletterService.php` | ✅ Complete |
| **Email Services** | `app/Services/Email/*` | ✅ SMTP Ready |
| **Admin Controller** | `app/Domains/Newsletter/Http/Controllers/Admin/` | ✅ Complete |
| **Admin UI** | `resources/views/admin/newsletter-campaigns.blade.php` | 🔲 Pending |

---

## 3. Post Card Component (Homepage & Search)

### Varient Post Card Structure

**Location**: `.claude/varient/Upload/app/Views/post/_post_item.php`

```php
<div class="post-card">
    <div class="post-header">
        <img src="post.thumbnail" />
        <span class="post-label">{{ post.category }}</span>
    </div>
    <div class="post-body">
        <h3>{{ post.title }}</h3>
        <p>{{ post.excerpt }}</p>
    </div>
    <div class="post-footer">
        <small>{{ post.author }} • {{ post.date }}</small>
    </div>
</div>
```

### NKHOJ Post Card Implementation (Blade Component)

**File**: `resources/views/components/post-card.blade.php`

```blade
<div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
    @if($post->thumbnail)
        <img src="{{ $post->thumbnail }}" class="w-full h-48 object-cover">
    @endif
    
    <div class="p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-blue-600">
                {{ $post->category->name ?? 'General' }}
            </span>
            <span class="text-xs text-gray-500">
                {{ $post->created_at->diffForHumans() }}
            </span>
        </div>
        
        <h3 class="text-lg font-bold mb-2">
            <a href="{{ route('posts.show', $post->slug) }}">
                {{ $post->title }}
            </a>
        </h3>
        
        <p class="text-gray-600 text-sm mb-3 line-clamp-2">
            {{ $post->excerpt ?? Str::limit($post->content, 150) }}
        </p>
        
        <div class="flex items-center justify-between text-xs text-gray-500">
            <span>By {{ $post->author->name }}</span>
            <a href="{{ route('posts.show', $post->slug) }}" class="text-blue-600 hover:underline">
                Read More →
            </a>
        </div>
    </div>
</div>
```

**Usage**:
```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($posts as $post)
        <x-post-card :post="$post" />
    @endforeach
</div>
```

---

## 4. Homepage Card Search Implementation

### Requirements

Based on branch name `claude/homepage-card-search-xmt1fe`:

1. **Homepage Cards** - Display featured posts/content as cards
2. **Card Search** - Search functionality within card grid
3. **Progressive Loading** - Load more cards on demand

### Implementation Plan

### Step 1: Create Post Card Component

```bash
# Already shown above in section 3
```

### Step 2: Homepage with Featured Cards

**File**: `resources/views/home/index.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <!-- Featured Section -->
        <div class="mb-12">
            <h2 class="text-3xl font-bold mb-8">Featured Stories</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($featured as $post)
                    <x-post-card :post="$post" />
                @endforeach
            </div>
        </div>
        
        <!-- Latest Section with Search -->
        <div>
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold">Latest Stories</h2>
                <input 
                    type="text" 
                    id="cardSearch" 
                    placeholder="Search stories..."
                    class="px-4 py-2 border rounded-lg"
                />
            </div>
            
            <div id="cards-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($latest as $post)
                    <x-post-card :post="$post" />
                @endforeach
            </div>
            
            <div class="mt-8 text-center">
                <button 
                    id="load-more-btn"
                    onclick="loadMoreCards()"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    Load More
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let searchQuery = '';

// Real-time search
document.getElementById('cardSearch').addEventListener('input', (e) => {
    searchQuery = e.target.value;
    currentPage = 1;
    filterCards();
});

async function filterCards() {
    const response = await fetch(`/api/posts/search?q=${searchQuery}&page=${currentPage}`, {
        headers: { 'Accept': 'application/json' }
    });
    
    const data = await response.json();
    const container = document.getElementById('cards-container');
    
    if (currentPage === 1) {
        container.innerHTML = '';
    }
    
    data.posts.forEach(post => {
        const card = createPostCard(post);
        container.appendChild(card);
    });
    
    // Show/hide load more button
    if (data.has_more) {
        document.getElementById('load-more-btn').style.display = 'block';
    } else {
        document.getElementById('load-more-btn').style.display = 'none';
    }
}

function loadMoreCards() {
    currentPage++;
    filterCards();
}

function createPostCard(post) {
    const template = document.querySelector('template[data-post-card]');
    const card = template.content.cloneNode(true);
    // Populate card with post data
    return card;
}
</script>
@endsection
```

### Step 3: API Endpoint for Search

**File**: `app/Http/Controllers/PostController.php`

```php
public function search(Request $request)
{
    $query = $request->input('q', '');
    $page = $request->input('page', 1);
    $perPage = 12;
    
    $posts = Post::query()
        ->published()
        ->when($query, function ($q) use ($query) {
            $q->where('title', 'like', "%{$query}%")
              ->orWhere('content', 'like', "%{$query}%");
        })
        ->orderBy('created_at', 'desc')
        ->paginate($perPage, ['*'], 'page', $page);
    
    if ($request->expectsJson()) {
        return response()->json([
            'posts' => $posts->items(),
            'has_more' => $posts->hasMorePages(),
            'total' => $posts->total(),
        ]);
    }
    
    return view('search.index', compact('posts', 'query'));
}
```

### Step 4: Routes

**File**: `routes/web.php`

```php
// Homepage with featured cards
Route::get('/', [HomeController::class, 'index'])->name('home');

// Search/filter cards (JSON API)
Route::get('/api/posts/search', [PostController::class, 'search'])->name('posts.search');

// Full page search
Route::get('/search', [SearchController::class, 'index'])->name('search');
```

---

## 5. Database Integration

### Required Tables

**posts** (existing):
```sql
CREATE TABLE posts (
    id BIGINT PRIMARY KEY,
    title VARCHAR(255),
    slug VARCHAR(255) UNIQUE,
    content LONGTEXT,
    excerpt TEXT,
    category_id BIGINT,
    author_id BIGINT,
    thumbnail VARCHAR(255),
    status ENUM('draft', 'published', 'archived'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX (status, created_at),
    INDEX (category_id)
);
```

**categories** (existing):
```sql
CREATE TABLE categories (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    slug VARCHAR(255) UNIQUE
);
```

**email_campaigns** (new - from newsletter):
```sql
CREATE TABLE email_campaigns (
    id BIGINT PRIMARY KEY,
    subject VARCHAR(255),
    html_content LONGTEXT,
    total_recipients INT,
    sent_count INT DEFAULT 0,
    status ENUM('draft', 'sending', 'completed', 'failed'),
    created_at TIMESTAMP
);
```

**Indexes for Search Performance**:
```sql
CREATE INDEX idx_posts_title_content ON posts(title(100), content(100));
CREATE INDEX idx_posts_category_date ON posts(category_id, created_at);
CREATE INDEX idx_posts_published ON posts(status, created_at);
```

---

## 6. Configuration & Environment

### Mail Configuration (for Newsletter)

```env
# .env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=newsletter@dewanlabung.com.np
MAIL_FROM_NAME=NKHOJ
```

### Search Configuration

```env
# App settings for search
APP_POSTS_PER_PAGE=12
SEARCH_RESULTS_PER_PAGE=20
FEATURED_POSTS_COUNT=6
```

---

## 7. Performance Optimization

### Homepage Cards

```php
// Use eager loading to prevent N+1 queries
$posts = Post::query()
    ->with(['author', 'category', 'comments'])
    ->published()
    ->latest()
    ->paginate(12);
```

### Search Queries

```php
// Use full-text search for better performance
$posts = Post::query()
    ->whereRaw('MATCH(title, content) AGAINST(? IN BOOLEAN MODE)', [$query])
    ->published()
    ->paginate(20);
```

### Caching

```php
// Cache featured posts (24 hours)
$featured = Cache::remember('featured-posts', 86400, function () {
    return Post::published()
        ->where('is_featured', true)
        ->latest()
        ->limit(6)
        ->get();
});
```

---

## 8. Testing the Implementation

### Test Homepage Cards

```bash
# Visit homepage
curl http://localhost/

# Check featured posts displayed
# Verify post cards have: title, excerpt, author, date, thumbnail
```

### Test Search

```bash
# Search via API
curl "http://localhost/api/posts/search?q=test&page=1"

# Response should include:
# {
#   "posts": [...],
#   "has_more": true,
#   "total": 45
# }
```

### Test Newsletter Integration

```bash
# Create campaign
curl -X POST http://localhost/admin/newsletter/campaigns \
  -d "subject=Test&html_content=<h1>Test</h1>"

# Send to subscribers
curl -X POST http://localhost/admin/newsletter/campaigns/1/send

# Check progress
curl http://localhost/admin/newsletter/campaigns/1/status
```

---

## 9. Integration Checklist

- [ ] Post card component created (`resources/views/components/post-card.blade.php`)
- [ ] Homepage controller updated with featured & latest posts
- [ ] Search controller created with pagination
- [ ] API endpoint for card search (`/api/posts/search`)
- [ ] Database indexes added for search performance
- [ ] Real-time search JavaScript implemented
- [ ] Load more button with pagination
- [ ] Newsletter integration (already complete)
- [ ] Testing completed (all endpoints verified)
- [ ] Deployed to production

---

## 10. File Structure

```
.claude/
├── varient/                    # Reference: Search patterns
│   ├── Upload/
│   │   └── app/
│   │       ├── Controllers/
│   │       ├── Models/
│   │       └── Views/themes/classic/search.php
│   └── documentation/
├── app/                        # Reference: Laravel structure
│   ├── Http/Controllers/
│   ├── Models/
│   └── Services/
├── docs/
│   ├── NEWSLETTER_SYSTEM_IMPLEMENTATION.md
│   ├── REFERENCE_ARCHITECTURE.md  ← You are here
│   └── VARIENT_NEWSLETTER_SKILLS.md
└── sngine/                     # Search engine reference (if added)
```

---

## References

- **Varient Search**: `.claude/varient/Upload/app/Views/themes/classic/search.php`
- **Newsletter Docs**: `.claude/docs/NEWSLETTER_SYSTEM_IMPLEMENTATION.md`
- **NKHOJ App Ref**: `.claude/app/` (186 files of application structure)

---

**Last Updated**: 2026-09-23  
**Status**: 📚 Complete Reference Documentation
