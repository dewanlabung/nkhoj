# nkhoj — System Flowcharts

Mermaid diagrams for all major system flows.

---

## 1. Request Lifecycle

```mermaid
flowchart TD
    Browser -->|HTTP Request| LiteSpeed
    LiteSpeed -->|PHP 8.4 via lsphp84| Laravel
    Laravel --> Router[routes/web.php]
    Router -->|auth middleware?| AuthCheck{Authenticated?}
    AuthCheck -->|No + auth required| Login[Redirect /login]
    AuthCheck -->|Yes or public| Middleware[TrackLastSeen Middleware]
    Middleware --> Controller[Controller method]
    Controller --> Model[Eloquent Model / DB Query]
    Model --> MySQL[(MySQL 8.0)]
    MySQL --> Model
    Model --> Controller
    Controller --> View[Blade Template]
    View --> Response[HTML Response]
    Response --> Browser
```

---

## 2. Homepage Feed Flow

```mermaid
flowchart TD
    User([Visitor]) -->|GET /| HomeController
    HomeController --> HeroQuery[Query top 3 posts\nfeatured + view_count]
    HomeController --> PickQuery[Query next 3 posts\nEditor's Pick]
    HomeController --> FeedQuery[Query all published\npaginate 12]
    HomeController --> WidgetQuery[Load Widgets\nfor sidebar / home_top / home_bottom]
    WidgetQuery --> WidgetData{Widget types active?}
    WidgetData -->|popular_posts| PopularPosts[Top 5 by views]
    WidgetData -->|popular_tags| PopularTags[Top 15 tags]
    WidgetData -->|voting_poll| ActivePoll[Latest Poll]
    WidgetData -->|about_us / follow_us| SiteSettings[site_settings.json]
    HeroQuery & PickQuery & FeedQuery & WidgetData --> View[home.blade.php]

    View -->|Category pill click| AJAX[fetch /category/slug?ajax=1]
    AJAX --> CategoryController
    CategoryController -->|returns| FeedPartial[partials/posts-feed.blade.php]
    FeedPartial -->|innerHTML| PostsFeedDiv[#posts-feed div]
```

---

## 3. Post Publish Flow (Author)

```mermaid
flowchart TD
    Author([Author]) -->|GET /dashboard/posts/create| Dashboard[Dashboard Create Form]
    Dashboard --> TinyMCE[TinyMCE rich text editor]
    Author -->|Fill title, body, tags, settings| FormFill
    FormFill -->|POST /dashboard/posts| DashboardStore[DashboardController::store]

    DashboardStore --> Validate{Validation\npasses?}
    Validate -->|No| BackWithErrors[Redirect back with errors]
    Validate -->|Yes| BuildBody[textToBlocks → JSON array]
    BuildBody --> UploadThumb{Thumbnail\nuploaded?}
    UploadThumb -->|Yes| MoveFile[move to public/uploads/]
    UploadThumb -->|No| UseUrl[use thumbnail_url field]
    MoveFile & UseUrl --> CreatePost[Post::create]
    CreatePost --> SyncTags[syncTags — upsert + pivot]
    SyncTags --> Redirect[Redirect /dashboard\nwith success flash]
```

---

## 4. Post Edit & Toggle Flow

```mermaid
flowchart TD
    Author([Author]) -->|GET /dashboard/posts/id/edit| EditForm[edit.blade.php\nAlpine.js editForm]

    EditForm --> AlpineInit[init: parse currentTags\nload sources, FAQ, toggles]

    subgraph Toggle Flow
        ClickToggle([Click toggle div]) --> AlpineToggle[on = !on\nAlpine state updates]
        AlpineToggle --> HiddenInput[hidden input :value = on ? 1 : 0\nreactive binding]
    end

    subgraph Tags Flow
        AddTag([Press Enter / comma]) --> PushTag[tags.push tag]
        PushTag --> HiddenTags[hidden input :value = tags.join comma\nlive reactive]
    end

    Author -->|Submit| FormSubmit[@submit handler\nsets tagsHidden value]
    FormSubmit -->|PUT /dashboard/posts/id| DashboardUpdate[DashboardController::update]
    DashboardUpdate --> ValidateUpdate{Validation}
    ValidateUpdate -->|Yes| SavePost[post.update with all fields\nbooleans cast correctly]
    SavePost --> SyncTagsU[syncTags]
    SyncTagsU --> RedirectDash[Redirect /dashboard]
```

---

## 5. Authentication Flow

```mermaid
flowchart TD
    Visitor([Visitor]) --> LoginPage[GET /login]
    LoginPage -->|POST /login| AuthLogin[AuthController::login]
    AuthLogin --> CheckBanned{is_banned?}
    CheckBanned -->|Yes| DenyLogin[Back with error: banned]
    CheckBanned -->|No| VerifyPassword{Password correct?}
    VerifyPassword -->|No| FailLogin[Back with error]
    VerifyPassword -->|Yes| SessionLogin[auth()->login\nSession regenerate]
    SessionLogin --> TrackHistory[LoginHistory::create\nIP + user agent]
    TrackHistory --> RedirectHome[Redirect /]

    Visitor -->|GET /auth/google/redirect| Socialite[SocialAuthController::redirect]
    Socialite --> GoogleOAuth[Google OAuth consent]
    GoogleOAuth -->|Callback| SocialCallback[SocialAuthController::callback]
    SocialCallback --> FindOrCreate{User exists?}
    FindOrCreate -->|No| CreateUser[User::create\nSocialAccount::create]
    FindOrCreate -->|Yes| LinkAccount[SocialAccount::updateOrCreate]
    CreateUser & LinkAccount --> SessionLoginOAuth[auth()->login]
    SessionLoginOAuth --> RedirectHome
```

---

## 6. Admin Deploy Flow

```mermaid
flowchart TD
    Admin([Admin]) -->|POST /admin/deploy/run| DeployRun[AdminController::manualDeploy]
    DeployRun --> Step1[git fetch origin master]
    Step1 -->|ok| Step2[git reset --hard origin/master]
    Step2 -->|ok| Step3[composer install --no-dev]
    Step3 -->|ok| Step4[php artisan migrate --force]
    Step4 -->|ok| Step5[php artisan cache:clear]
    Step5 -->|ok| Step6[php artisan config:clear]
    Step6 -->|ok| Step7[php artisan config:cache]
    Step7 -->|ok| Step8[php artisan view:clear]
    Step8 -->|ok| Step9[php artisan route:cache]
    Step9 --> Success[JSON response: success + log]

    Step1 & Step2 & Step3 & Step4 & Step5 & Step6 & Step7 & Step8 & Step9 -->|exit code ≠ 0| Fail[break loop\nJSON: success=false + log]

    GitHub([GitHub push]) -->|POST /webhook/deploy| WebhookVerify{HMAC\nsignature valid?}
    WebhookVerify -->|No| Return401[HTTP 401]
    WebhookVerify -->|Yes| SameSteps[Same 9 steps\nexec in background]
    SameSteps --> HTTP200[HTTP 200]
```

---

## 7. Widget Rendering Flow

```mermaid
flowchart TD
    HomeController --> LoadWidgets[Widget::forPosition sidebar\nWidget::forPosition home_top\nWidget::forPosition home_bottom]
    LoadWidgets --> MergeTypes[merge all widgets\npluck unique types]
    MergeTypes --> PopularPosts{popular_posts\nin types?}
    PopularPosts -->|Yes| FetchPP[Top 5 posts by views]
    MergeTypes --> PopularTags{popular_tags\nin types?}
    PopularTags -->|Yes| FetchPT[Top 15 tags]
    MergeTypes --> VotingPoll{voting_poll\nin types?}
    VotingPoll -->|Yes| FetchVP[Latest Poll with options]
    MergeTypes --> FollowAbout{follow_us or\nabout_us?}
    FollowAbout -->|Yes| FetchSettings[Read site_settings.json]

    FetchPP & FetchPT & FetchVP & FetchSettings --> widgetData[widgetData array]
    widgetData --> HomeView[home.blade.php]
    HomeView -->|foreach sidebarWidgets| WidgetPartial[partials/_widget.blade.php]
    WidgetPartial -->|No widgets configured| FallbackSidebar[Hardcoded trending + categories]
```

---

## 8. Bookmark Flow (Polymorphic)

```mermaid
flowchart TD
    User([Auth User]) -->|POST /bookmarks/toggle| Toggle[BookmarkController::toggle]
    Toggle --> ParseBody[Parse bookmarkable_type\nbookmarkable_id from request]
    ParseBody --> FindExisting{Bookmark exists\nfor this user + item?}
    FindExisting -->|Yes| DeleteBookmark[Bookmark::delete]
    FindExisting -->|No| CreateBookmark[Bookmark::create\nuser_id, bookmarkable_type, bookmarkable_id]
    DeleteBookmark & CreateBookmark --> JSONResponse[JSON: bookmarked true/false]

    User2([Auth User]) -->|GET /bookmarks| BookmarkIndex[BookmarkController::index]
    BookmarkIndex --> LoadAll[Bookmarks with\nmorph polymorphic eager load]
    LoadAll --> BookmarkView[bookmarks/index.blade.php]
```

---

## 9. AI Content Generation Flow

```mermaid
flowchart TD
    Author([Author in Dashboard]) -->|POST /api/ai/author| AIController
    AIController --> RateLimit{throttle\n60/min?}
    RateLimit -->|Over limit| 429[HTTP 429]
    RateLimit -->|OK| BuildPrompt[Build prompt\nfrom action + topic]
    BuildPrompt --> ClaudeAPI[POST to Anthropic API\nvia Guzzle]
    ClaudeAPI --> ParseResponse[Parse JSON response]
    ParseResponse --> JSONReturn[JSON: result string]
    JSONReturn --> Dashboard[Dashboard shows suggestion\nAuthor can apply or discard]
```

---

## 10. Database Schema (Core Relations)

```mermaid
erDiagram
    users {
        bigint id PK
        string uuid
        string name
        string username
        string email
        string role
        boolean is_banned
        decimal balance
        json social_links
        json extra_permissions
    }

    posts {
        bigint id PK
        string uuid
        bigint author_id FK
        bigint category_id FK
        string slug
        string title
        json body
        string status
        boolean is_featured
        boolean is_pro
        int view_count
        string post_format
        datetime published_at
        json sources
        json article_faq
    }

    categories {
        bigint id PK
        string name_en
        string name_ne
        string slug
        int sort_order
    }

    tags {
        bigint id PK
        string name_en
        string name_ne
        string slug
    }

    post_tag {
        bigint post_id FK
        bigint tag_id FK
    }

    comments {
        bigint id PK
        bigint post_id FK
        bigint user_id FK
        text body
        boolean is_approved
    }

    bookmarks {
        bigint id PK
        bigint user_id FK
        string bookmarkable_type
        bigint bookmarkable_id
    }

    widgets {
        bigint id PK
        string type
        string position
        string title
        json config
        boolean is_active
    }

    membership_plans {
        bigint id PK
        string name
        decimal price
        string interval
        json features
    }

    subscriptions {
        bigint id PK
        bigint user_id FK
        bigint plan_id FK
        string status
        string payment_method
    }

    users ||--o{ posts : "authors"
    users ||--o{ comments : "writes"
    users ||--o{ bookmarks : "saves"
    users ||--o{ subscriptions : "subscribes"
    posts }o--|| categories : "belongs to"
    posts }o--o{ tags : "post_tag"
    posts ||--o{ comments : "has"
    subscriptions }o--|| membership_plans : "for plan"
```
