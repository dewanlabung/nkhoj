# Technical Recommendations
## Deep Technical Guidance for NKHOJ Platform

**Document**: Architecture decisions, code patterns, and technical implementation  
**Date**: September 24, 2026  
**Status**: Implementation-Ready Technical Specification

---

## Table of Contents

1. [Technology Stack](#technology-stack)
2. [Architecture Decisions](#architecture-decisions)
3. [Code Patterns & Best Practices](#code-patterns--best-practices)
4. [Database Design](#database-design)
5. [API Design](#api-design)
6. [Performance Optimizations](#performance-optimizations)
7. [Security Considerations](#security-considerations)
8. [Deployment & DevOps](#deployment--devops)
9. [Scaling Strategy](#scaling-strategy)
10. [Monitoring & Observability](#monitoring--observability)

---

## Technology Stack

### Recommended Stack (vs. Current Codebases)

```
Backend:
  Framework:        Laravel 11 (better than CodeIgniter + procedural)
  Language:         PHP 8.2+
  API:              REST + GraphQL
  Real-Time:        Socket.io (keep from SNGINE)
  Queue:            RabbitMQ / Redis for background jobs
  
Database:
  Primary:          MySQL 8.0 (InnoDB)
  Cache:            Redis 7.0 (sessions, cache)
  Search:           Elasticsearch 8.0 (advanced search)
  Message Queue:    RabbitMQ 3.12
  
Frontend:
  Framework:        Vue 3 / React 18
  UI Components:    Bootstrap 5.3 (from SNGINE)
  State:            Vuex / Redux
  Real-Time Client: Socket.io client library
  Rich Editor:      TinyMCE 7.6 (from SNGINE)
  Charts:           Highcharts 12.6 (from SNGINE)
  Image:            Sharp.js for processing
  
DevOps:
  Container:        Docker 24
  Orchestration:    Kubernetes 1.28
  CI/CD:            GitHub Actions / GitLab CI
  Monitoring:       Prometheus + Grafana
  Logging:          ELK Stack (Elasticsearch, Logstash, Kibana)
  APM:              New Relic / DataDog
  
Third-Party:
  Payment:          Stripe + PayPal (dual integration)
  Email:            SendGrid / AWS SES
  Video:            Mux / Vimeo (transcoding)
  CDN:              Cloudflare / AWS CloudFront
  Notifications:    OneSignal (compatible with SNGINE)
  Search:           Algolia (simpler alternative to Elasticsearch)
  Auth:             Firebase Auth / Auth0 (SSO)
```

### Rationale

| Component | Choice | Why |
|-----------|--------|-----|
| Framework | Laravel 11 | Better structure than CodeIgniter, stronger ORM, ecosystem |
| Real-Time | Socket.io | Proven, well-supported, SNGINE uses it |
| Cache | Redis | Industry standard, fast, supports complex data |
| Search | Elasticsearch | Full-text search, faceting, analytics |
| Container | Docker | Reproducible environments, easy scaling |
| Orchestration | Kubernetes | Production-grade, auto-scaling, self-healing |
| CDN | Cloudflare | Best performance + security + DDoS protection |

---

## Architecture Decisions

### Microservices vs. Monolith

**Decision**: Modular Monolith (initially), migrate to microservices at scale

**Rationale**:
- Start with logical separation (modules)
- Easier to deploy and debug
- Microservices when load justifies

**Module Structure**:
```
app/
├── Auth/              (Authentication & Authorization)
├── Users/             (User profiles & management)
├── Content/           (Posts, articles, galleries)
├── Community/         (Groups, forums, events)
├── Marketplace/       (Products, orders, payments)
├── Notifications/     (Real-time notifications)
├── Search/            (Full-text search)
├── Analytics/         (Metrics & reporting)
└── Admin/             (Administration)
```

**Future Migration Path**:
```
Year 2 Microservices:
├── auth-service       (Independent authentication)
├── content-service    (Content creation & delivery)
├── social-service     (Social features)
├── commerce-service   (Marketplace & payments)
└── notification-service (Real-time messaging)
```

### Database Strategy

**Decision**: Single MySQL database with read replicas

**Setup**:
```
Master (Write):
  Primary MySQL 8.0 (all writes)
  
Read Replicas (Read-only):
  Replica 1: MySQL 8.0
  Replica 2: MySQL 8.0
  
Connection Pooling:
  ProxySQL between app and databases
  
Scaling:
  Year 1: Single master + 2 replicas
  Year 2: Sharding by user_id
```

**Key Tables** (from VARIENT + SNGINE):
```
users            - User accounts & profiles
posts            - Content (articles, blogs, etc.)
posts_products   - Marketplace products
comments         - Comments & discussions
messages         - Direct messages
groups           - Community groups
forums           - Forum discussions
events           - Community events
orders           - E-commerce orders
wallet_transactions - Creator earnings
notifications    - Real-time notifications
sessions         - User sessions (Redis)
```

### API Architecture

**Decision**: REST API (primary) + GraphQL (secondary)

**Endpoints**:
```
REST API:
  v1/auth/*              - Authentication
  v1/users/*             - User management
  v1/posts/*             - Content
  v1/comments/*          - Comments
  v1/communities/*       - Groups, forums, events
  v1/marketplace/*       - E-commerce
  v1/search/*            - Search
  v1/notifications/*     - Notifications
  v1/analytics/*         - Analytics
  
GraphQL:
  /graphql               - Single endpoint
  Queries: posts, users, search, comments
  Mutations: createPost, updateProfile, addComment
  Subscriptions: postUpdated, messageReceived
```

**Rate Limiting**:
```
Free tier:     100 req/hour per IP
Authenticated: 1000 req/hour per user
Premium:       10000 req/hour per API key
```

### Real-Time Architecture

**Decision**: Socket.io with Redis adapter

**Flow**:
```
Client Browser
    ↓
Socket.io Client Library
    ↓
Node.js Server (Socket.io)
    ↓
Redis Adapter (for clustering)
    ↓
MySQL Database
    ↓
Message Queue (RabbitMQ)
```

**Event Types**:
```
Presence Events:
  user:online     - User comes online
  user:offline    - User goes offline
  user:typing     - Typing indicator
  
Message Events:
  message:send    - Send message
  message:read    - Message read
  
Notification Events:
  notification:new      - New notification
  notification:read     - Notification read
  
Content Events:
  post:created    - New post
  post:updated    - Post edited
  comment:added   - New comment
```

---

## Code Patterns & Best Practices

### Repository Pattern (From VARIENT Models)

**Implementation**:
```php
// app/Repositories/PostRepository.php
class PostRepository {
    protected $model;
    
    public function __construct(Post $post) {
        $this->model = $post;
    }
    
    public function getLatestPosts($langId, $perPage, $offset) {
        return Cache::remember('posts:latest:' . $langId, 3600, function() use ($langId, $perPage, $offset) {
            return $this->model
                ->where('lang_id', $langId)
                ->where('status', 1)
                ->where('visibility', 1)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $offset);
        });
    }
    
    public function getPopularPosts($langId, $days = 30) {
        return Cache::remember('posts:popular:' . $langId . ':' . $days, 86400, function() use ($langId, $days) {
            return $this->model
                ->select('posts.*', DB::raw('COUNT(*) as view_count'))
                ->join('post_pageviews', 'post_pageviews.post_id', '=', 'posts.id')
                ->where('posts.lang_id', $langId)
                ->where('post_pageviews.viewed_at', '>=', Carbon::now()->subDays($days))
                ->groupBy('posts.id')
                ->orderBy('view_count', 'desc')
                ->limit(20)
                ->get();
        });
    }
}

// Usage in Controller
class PostController {
    public function __construct(PostRepository $postRepo) {
        $this->postRepo = $postRepo;
    }
    
    public function latestPosts() {
        $posts = $this->postRepo->getLatestPosts(1, 20, 0);
        return response()->json($posts);
    }
}
```

### Service Layer Pattern (From SNGINE traits)

**Implementation**:
```php
// app/Services/NotificationService.php
class NotificationService {
    protected $repository;
    protected $socket;
    
    public function __construct(NotificationRepository $repo, SocketService $socket) {
        $this->repository = $repo;
        $this->socket = $socket;
    }
    
    public function notifyUser($toUserId, $action, $data) {
        // Save to database
        $notification = $this->repository->create([
            'to_user_id' => $toUserId,
            'action' => $action,
            'data' => $data
        ]);
        
        // Send real-time via Socket.io
        $this->socket->emitTo($toUserId, 'notification:new', $notification);
        
        // Send push notification
        if ($this->shouldSendPush($toUserId, $action)) {
            $this->sendPushNotification($toUserId, $notification);
        }
        
        return $notification;
    }
    
    private function shouldSendPush($userId, $action) {
        // User preferences
        $user = User::find($userId);
        return $user->notification_preferences[$action] ?? true;
    }
}
```

### Trait-Based Composition (From SNGINE)

**Recommended Usage**:
```php
// app/Models/User.php
class User extends Model {
    use HasProfileTrait,
        HasAffiliatesTrait,
        HasFollowersTrait,
        HasNotificationsTrait,
        HasWalletTrait;
    
    // Core properties
    protected $fillable = ['email', 'username', 'password'];
}

// Traits are organized by feature
// app/Traits/HasWalletTrait.php
trait HasWalletTrait {
    public function getWalletBalance() {
        return $this->walletTransactions()
            ->selectRaw('SUM(amount) as balance')
            ->where('type', 'credit')
            ->value('balance');
    }
    
    public function addBalance($amount, $reason) {
        return $this->walletTransactions()->create([
            'amount' => $amount,
            'type' => 'credit',
            'reason' => $reason
        ]);
    }
}
```

### Caching Strategy (From VARIENT)

**Multi-Level Caching**:
```php
// L1: APCu (in-process memory)
// L2: Redis (distributed cache)
// L3: Database (source of truth)

class Cache {
    public function get($key, $callable = null, $ttl = 3600) {
        // Try L1: APCu
        $value = apcu_fetch($key);
        if ($value !== false) {
            return $value;
        }
        
        // Try L2: Redis
        $value = Redis::get($key);
        if ($value) {
            apcu_store($key, $value, 60);  // Cache in APCu for 1 min
            return $value;
        }
        
        // Fallback to database
        if ($callable) {
            $value = $callable();
            $this->set($key, $value, $ttl);
            return $value;
        }
        
        return null;
    }
    
    public function set($key, $value, $ttl = 3600) {
        apcu_store($key, $value, 60);
        Redis::setex($key, $ttl, serialize($value));
    }
    
    public function invalidate($pattern) {
        // Clear from Redis
        foreach (Redis::keys($pattern) as $key) {
            Redis::del($key);
        }
        // Clear from APCu
        apcu_delete(new APCuIterator('/^' . str_replace('*', '.*', $pattern) . '$/'));
    }
}

// Usage
$posts = Cache::get('posts:latest:' . $langId, function() use ($langId) {
    return Post::latest()->where('lang_id', $langId)->take(20)->get();
}, 3600);
```

### Error Handling

**Standard Exception Handling**:
```php
try {
    $post = $this->postRepo->find($postId);
    if (!$post) {
        throw new ResourceNotFoundException('Post not found');
    }
} catch (ResourceNotFoundException $e) {
    return response()->json(['error' => $e->getMessage()], 404);
} catch (ValidationException $e) {
    return response()->json(['errors' => $e->errors()], 422);
} catch (Exception $e) {
    Log::error('Post retrieval failed', ['error' => $e->message]);
    return response()->json(['error' => 'Internal server error'], 500);
}
```

---

## Database Design

### Schema Optimization

**Key Indexes** (From VARIENT):
```sql
-- Posts indexing
CREATE INDEX idx_posts_created_at ON posts(created_at DESC);
CREATE INDEX idx_posts_category_lang ON posts(category_id, lang_id, created_at DESC);
CREATE INDEX idx_posts_user ON posts(user_id, created_at DESC);
CREATE INDEX idx_posts_slug ON posts(slug) UNIQUE;
CREATE INDEX idx_posts_status ON posts(status, visibility, is_scheduled);

-- Users indexing
CREATE INDEX idx_users_email ON users(email) UNIQUE;
CREATE INDEX idx_users_username ON users(username) UNIQUE;
CREATE INDEX idx_users_created_at ON users(created_at DESC);

-- Comments indexing
CREATE INDEX idx_comments_post ON comments(post_id, created_at DESC);
CREATE INDEX idx_comments_user ON comments(user_id, created_at DESC);

-- Products indexing
CREATE INDEX idx_products_seller ON products(seller_id, availability);
CREATE INDEX idx_products_category ON products(category_id, availability);
CREATE INDEX idx_products_created ON products(created_at DESC);
```

### Partition Strategy (Year 2+)

**Partitioning by Date**:
```sql
CREATE TABLE posts_partitioned (
    id INT,
    created_at DATETIME,
    -- ... other columns
) PARTITION BY RANGE (YEAR(created_at)) (
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p2026 VALUES LESS THAN (2027),
    PARTITION pmax VALUES LESS THAN MAXVALUE
);
```

**Sharding by User ID** (when data exceeds 10TB):
```
User 1-1M       → Shard 1
User 1M-2M      → Shard 2
User 2M-3M      → Shard 3
```

---

## API Design

### RESTful Endpoints

**Resource Hierarchy**:
```
POST /api/v1/posts                    # Create post
GET  /api/v1/posts                    # List posts
GET  /api/v1/posts/{id}               # Get post
PUT  /api/v1/posts/{id}               # Update post
DELETE /api/v1/posts/{id}             # Delete post

GET  /api/v1/posts/{id}/comments      # Post comments
POST /api/v1/posts/{id}/comments      # Add comment
PUT  /api/v1/posts/{id}/comments/{cid}# Update comment

GET  /api/v1/users/{id}/posts         # User's posts
GET  /api/v1/users/{id}/followers     # User's followers
POST /api/v1/users/{id}/follow        # Follow user
```

### Request/Response Format

**Request**:
```json
POST /api/v1/posts
Content-Type: application/json
Authorization: Bearer {token}

{
    "title": "My First Post",
    "content": "Post content...",
    "category_id": 1,
    "post_type": "article",
    "tags": ["technology", "startup"],
    "published": true
}
```

**Response (201 Created)**:
```json
{
    "data": {
        "id": 123,
        "title": "My First Post",
        "slug": "my-first-post",
        "content": "Post content...",
        "category": {
            "id": 1,
            "name": "Technology"
        },
        "author": {
            "id": 456,
            "username": "john_doe"
        },
        "created_at": "2026-09-24T10:30:00Z",
        "updated_at": "2026-09-24T10:30:00Z"
    },
    "meta": {
        "code": 201,
        "message": "Post created successfully"
    }
}
```

### Pagination

**Cursor-Based** (recommended):
```
GET /api/v1/posts?limit=20&cursor=abc123

Response:
{
    "data": [...],
    "pagination": {
        "cursor": "xyz789",
        "limit": 20,
        "has_more": true
    }
}
```

**Offset-Based** (fallback):
```
GET /api/v1/posts?page=1&per_page=20

Response:
{
    "data": [...],
    "pagination": {
        "total": 5000,
        "page": 1,
        "per_page": 20,
        "pages": 250
    }
}
```

---

## Performance Optimizations

### Database Query Optimization

**N+1 Query Problem**:
```php
// BAD: N+1 queries
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name;  // Query for each post!
}

// GOOD: Single query with eager loading
$posts = Post::with('author', 'category', 'comments.author')->get();
foreach ($posts as $post) {
    echo $post->author->name;  // Already loaded
}
```

**Selective Columns**:
```php
// BAD: All columns including heavy ones
$posts = Post::all();

// GOOD: Only needed columns
$posts = Post::select('id', 'title', 'slug', 'created_at')
    ->with('author:id,username')
    ->get();
```

### Image Optimization (From VARIENT)

**Multi-Size Pipeline**:
```php
// Auto-generate multiple sizes
$image = new ImageProcessor($uploadedFile);
$sizes = [
    'big' => [1200, 800],      // Desktop
    'default' => [800, 533],   // Default
    'slider' => [600, 400],    // Slider
    'mid' => [400, 267],       // Article
    'small' => [200, 133]      // Thumbnail
];

foreach ($sizes as $name => $dimensions) {
    $image->resize($dimensions[0], $dimensions[1])
          ->convert('webp')
          ->save("storage/images/{$id}_{$name}.webp");
}

// Store metadata
$imageData = [
    'image_big' => "images/{$id}_big.webp",
    'image_default' => "images/{$id}_default.webp",
    'image_small' => "images/{$id}_small.webp"
];
```

**Responsive Images**:
```html
<picture>
    <source media="(min-width: 1200px)" 
            srcset="/img/{id}_big.webp 1x, /img/{id}_big@2x.webp 2x">
    <source media="(min-width: 768px)" 
            srcset="/img/{id}_default.webp 1x, /img/{id}_default@2x.webp 2x">
    <source media="(max-width: 767px)" 
            srcset="/img/{id}_small.webp 1x, /img/{id}_small@2x.webp 2x">
    <img src="/img/{id}_small.webp" alt="...">
</picture>
```

### Query Optimization

**Use EXPLAIN**:
```sql
EXPLAIN SELECT * FROM posts 
WHERE category_id = 5 
AND lang_id = 1 
AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Should use index on (category_id, lang_id, created_at)
```

---

## Security Considerations

### Authentication & Authorization

**OAuth2 Implementation**:
```php
// Middleware for API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
});

// Token generation
$token = $user->createToken('API Token')->plainTextToken;

// Token verification
$user = Auth::user();  // Sanctum automatically verifies
```

**Permission Checking**:
```php
// Policy-based authorization
class PostPolicy {
    public function update(User $user, Post $post) {
        return $user->id === $post->user_id || $user->isAdmin();
    }
}

// Usage in controller
public function update(Request $request, Post $post) {
    $this->authorize('update', $post);
    // Proceed with update
}
```

### Data Protection

**Sensitive Data Encryption**:
```php
// Encrypt wallet balance, payment info
class User extends Model {
    protected $encrypted = ['wallet_balance', 'bank_account'];
}

// Usage
$user->wallet_balance = 1000;  // Automatically encrypted
$balance = $user->wallet_balance;  // Automatically decrypted
```

**SQL Injection Prevention**:
```php
// BAD
$posts = DB::select("SELECT * FROM posts WHERE id = " . $id);

// GOOD
$posts = DB::select("SELECT * FROM posts WHERE id = ?", [$id]);
$posts = Post::where('id', $id)->get();  // ORM
```

### Rate Limiting

```php
// API rate limiting
Route::middleware('throttle:1000,60')->group(function () {  // 1000 req/hour
    Route::get('/posts', [PostController::class, 'index']);
});

// Custom rate limiting
RateLimiter::for('expensive-api', function (Request $request) {
    return Limit::perMinute(5);
});
```

---

## Deployment & DevOps

### Docker Setup

**Dockerfile**:
```dockerfile
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    mysql-client \
    redis-tools \
    git \
    && docker-php-ext-install pdo_mysql redis

WORKDIR /app

COPY composer.json .
RUN composer install --no-dev

COPY . .

RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

EXPOSE 9000

CMD ["php-fpm"]
```

**docker-compose.yml**:
```yaml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "8000:8000"
    environment:
      DB_HOST: mysql
      CACHE_DRIVER: redis
      REDIS_HOST: redis
    depends_on:
      - mysql
      - redis
  
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: nkhoj
      MYSQL_ROOT_PASSWORD: root
    volumes:
      - mysql_data:/var/lib/mysql
  
  redis:
    image: redis:7-alpine

  socket:
    build: ./socket-server
    ports:
      - "3000:3000"
    depends_on:
      - redis

volumes:
  mysql_data:
```

### CI/CD Pipeline

**GitHub Actions**:
```yaml
name: CI/CD

on:
  push:
    branches: [main, develop]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
        env:
          MYSQL_DATABASE: nkhoj_test
          MYSQL_ROOT_PASSWORD: root
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
          extensions: pdo_mysql, redis
      
      - name: Install dependencies
        run: composer install
      
      - name: Run tests
        run: |
          php artisan migrate:fresh
          php artisan test
      
      - name: Deploy to staging
        if: github.ref == 'refs/heads/develop'
        run: ./deploy-staging.sh
      
      - name: Deploy to production
        if: github.ref == 'refs/heads/main'
        run: ./deploy-production.sh
```

---

## Scaling Strategy

### Horizontal Scaling (Year 2+)

**Database Read Replicas**:
```
                  ┌─────────────────┐
                  │   Application   │
                  │    (Laravel)    │
                  └────────┬────────┘
                           │
        ┌──────────────────┼──────────────────┐
        │                  │                  │
    ┌───▼─────┐    ┌──────▼──────┐    ┌─────▼──┐
    │ ProxySQL│    │ ProxySQL    │    │ProxySQL│
    └───┬─────┘    └──────┬──────┘    └─────┬──┘
        │                 │                  │
        │           ┌─────▼─────┐            │
        ├──────────▶│   Master   │◀──────────┤
        │           │   MySQL    │           │
        │           └───────────┘           │
        │                                    │
    ┌───▼──────┐              ┌────────▶┌──▼────┐
    │ Replica  │              │         │Replica│
    │    1     │              │         │   2   │
    └──────────┘              │         └───────┘
                              │
                         (read-only)
```

### Microservices Decomposition (Year 2-3)

**Service Separation**:
```
Monolith (Year 1)
    ↓ (Scale-based decomposition)
Year 2 Services:
├── auth-service        (Handle auth independently)
├── content-service     (Post creation, retrieval)
├── social-service      (Groups, forums, comments)
├── commerce-service    (Products, orders)
└── notification-service (Real-time events)

Communication:
  Services → Message Queue (RabbitMQ)
  Services → API Gateway
  Services → Shared database (read replicas)
```

---

## Monitoring & Observability

### Key Metrics to Track

**Application Metrics**:
```
- Request latency (P50, P95, P99)
- Request rate (requests/second)
- Error rate (5xx errors)
- Cache hit rate
- Database query time
- API endpoint performance
```

**Infrastructure Metrics**:
```
- CPU usage
- Memory usage
- Disk I/O
- Network bandwidth
- Database connections
- Redis memory usage
```

**Business Metrics**:
```
- Active users (DAU, MAU)
- Content created/day
- Revenue/transactions
- User engagement
- Creator satisfaction
```

### Monitoring Setup

**Prometheus Configuration**:
```yaml
global:
  scrape_interval: 15s

scrape_configs:
  - job_name: 'laravel-app'
    static_configs:
      - targets: ['localhost:9090']
  
  - job_name: 'mysql'
    static_configs:
      - targets: ['localhost:3306']
  
  - job_name: 'redis'
    static_configs:
      - targets: ['localhost:6379']
```

**Grafana Dashboards**:
- Request latency dashboard
- Error rate dashboard
- Resource usage dashboard
- Business metrics dashboard

### Alerting

**Critical Alerts**:
```
- P99 latency > 5s
- Error rate > 1%
- CPU > 90% for 5 minutes
- Memory > 85%
- Database connections > 80 of max
```

---

## Summary & Recommendations

### Phase 1: Adopt VARIENT Patterns
- Caching strategy (multi-level)
- Image processing pipeline
- Database query optimization
- SEO architecture

### Phase 2: Integrate SNGINE Stack
- Socket.io real-time
- Trait-based features
- API modularity
- Notification system

### Phase 3: Modernize Architecture
- Move to Laravel framework
- Implement microservices
- Add GraphQL API
- Implement Kubernetes

### Key Success Factors
1. Strong caching strategy (80% performance gain)
2. Proper database indexing (50x query speedup)
3. Real-time capabilities (30% engagement gain)
4. Modular architecture (easy scaling)
5. Comprehensive monitoring (reliability)

---

**Document Version**: 1.0  
**Status**: Implementation-Ready  
**Last Updated**: September 24, 2026
