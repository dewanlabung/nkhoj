<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\SocialPageController;

// Public routes
Route::get('/', [HomeController::class, 'index']);
Route::get('/leaderboard', [HomeController::class, 'leaderboard']);
Route::get('/health', fn() => response()->json(['status' => 'ok', 'app' => 'nkhoj', 'time' => now()->toIso8601String()]));
Route::get('/search', [SearchController::class, 'index'])->middleware('throttle:30,1');
Route::get('/search/suggest', [SearchController::class, 'suggest'])->middleware('throttle:60,1');
Route::get('/category/{slug}', [CategoryController::class, 'show']);
Route::get('/tag/{slug}', [TagController::class, 'show']);
Route::get('/posts/{slug}', [PostController::class, 'show']);
Route::post('/posts/{slug}/share', [PostController::class, 'share']);

// SEO
Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/feed.xml',    [SitemapController::class, 'feed']);

// Contact form (public)
Route::get('/contact',  [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store']);

// Newsletter subscribe (public)
Route::post('/newsletter/subscribe',            [ContactController::class, 'subscribe']);
Route::get('/newsletter/unsubscribe/{token}',   [ContactController::class, 'unsubscribe']);

// Polls (public JSON endpoints)
Route::post('/polls/{pollId}/vote',    [PollController::class, 'vote'])->middleware('throttle:10,1');
Route::get('/polls/{pollId}/results',  [PollController::class, 'results']);

// Comments (auth or guest)
Route::post('/posts/{slug}/comments', [CommentController::class, 'store'])->middleware('throttle:15,1');
Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->middleware('auth');
Route::post('/comments/{comment}/react', [\App\Http\Controllers\CommentReactionController::class, 'toggle']);

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister']);
    Route::post('/register', [AuthController::class, 'register']);

    // Account recovery (public — no auth)
    Route::get('/forgot-username',  [\App\Http\Controllers\Auth\AccountRecoveryController::class, 'showForgotUsername']);
    Route::post('/forgot-username', [\App\Http\Controllers\Auth\AccountRecoveryController::class, 'sendUsername'])->middleware('throttle:5,10');
    Route::get('/forgot-password',  [\App\Http\Controllers\Auth\AccountRecoveryController::class, 'showForgotPassword']);
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\AccountRecoveryController::class, 'sendPasswordReset'])->middleware('throttle:5,10');
    Route::get('/reset-password',   [\App\Http\Controllers\Auth\AccountRecoveryController::class, 'showResetPassword']);
    Route::post('/reset-password',  [\App\Http\Controllers\Auth\AccountRecoveryController::class, 'resetPassword'])->middleware('throttle:10,10');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

// Social OAuth routes
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

// Profile
Route::get('/profile/{username}', [\App\Http\Controllers\ProfileController::class, 'show']);
Route::post('/follow/{id}', [\App\Http\Controllers\ProfileController::class, 'follow'])->middleware('auth');

// Reactions (works guest + auth)
Route::post('/react/{postId}', [\App\Http\Controllers\ReactionController::class, 'store'])->middleware('throttle:30,1');

// Bookmarks + following feed (auth only)
Route::middleware('auth')->group(function () {
    Route::get('/bookmarks', [BookmarkController::class, 'index']);
    Route::post('/bookmarks/toggle', [BookmarkController::class, 'toggle']);
    Route::delete('/bookmarks/{bookmark}', [BookmarkController::class, 'destroy']);
    Route::post('/bookmark/{postId}', [BookmarkController::class, 'togglePost']); // legacy
    Route::post('/bookmark-collections', [BookmarkController::class, 'createCollection']);
    Route::get('/following/feed', [\App\Http\Controllers\HomeController::class, 'followingFeed']);
});

// Notifications (auth only)
Route::middleware('auth')->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::get('/recent', [NotificationController::class, 'recent']);
    Route::post('/{id}/read', [NotificationController::class, 'markRead']);
    Route::get('/count', [NotificationController::class, 'unreadCount']);
});

// Old account routes — redirect to new account portal
Route::middleware('auth')->group(function () {
    Route::get('/account/settings', fn() => redirect('/account/personal-info'));
    Route::get('/account/password', fn() => redirect('/account/security'));
    Route::get('/two-factor/setup',    [\App\Http\Controllers\TwoFactorController::class, 'setup']);
    Route::post('/two-factor/confirm', [\App\Http\Controllers\TwoFactorController::class, 'confirm']);
    Route::post('/two-factor/disable', [\App\Http\Controllers\TwoFactorController::class, 'disable']);
});

// 2FA challenge (guest only — user is temporarily logged out)
Route::get('/two-factor-challenge',  [\App\Http\Controllers\TwoFactorController::class, 'challenge'])->middleware('guest');
Route::post('/two-factor-challenge', [\App\Http\Controllers\TwoFactorController::class, 'verify'])->middleware('guest');

// Admin panel
Route::middleware('auth')->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/', [AdminController::class, 'index']);
    Route::get('/analytics', [AdminController::class, 'analytics']);
    Route::get('/search-analytics', [AdminController::class, 'searchAnalytics']);

    // Users
    Route::get('/users',                          [AdminController::class, 'users']);
    Route::get('/users/create',                   [AdminController::class, 'createUser']);
    Route::post('/users',                         [AdminController::class, 'storeUser']);
    Route::get('/users/stop-impersonating',       [AdminController::class, 'stopImpersonating']);
    Route::get('/users/{id}',                     [AdminController::class, 'showUser']);
    Route::get('/users/{id}/edit',                [AdminController::class, 'editUser']);
    Route::put('/users/{id}',                     [AdminController::class, 'updateUser']);
    Route::delete('/users/{id}',                  [AdminController::class, 'deleteUser']);
    Route::post('/users/{id}/role',               [AdminController::class, 'updateUserRole']);
    Route::post('/users/{id}/ban',                [AdminController::class, 'banUser']);
    Route::post('/users/{id}/verify-email',       [AdminController::class, 'verifyEmail']);
    Route::post('/users/{id}/reward-system',      [AdminController::class, 'toggleRewardSystem']);
    Route::post('/users/{id}/impersonate',        [AdminController::class, 'impersonate']);
    Route::get('/users/{id}/permissions',         [AdminController::class, 'userPermissions']);
    Route::put('/users/{id}/permissions',         [AdminController::class, 'updateUserPermissions']);

    // Content
    Route::get('/categories',                [AdminController::class, 'categories']);
    Route::post('/categories',               [AdminController::class, 'storeCategory']);
    Route::post('/categories/reorder',       [AdminController::class, 'reorderCategories']);
    Route::put('/categories/{id}',           [AdminController::class, 'updateCategory']);
    Route::delete('/categories/{id}',        [AdminController::class, 'deleteCategory']);

    // Posts
    Route::get('/posts',                     [AdminController::class, 'posts']);
    Route::post('/posts/{id}/status',        [AdminController::class, 'updatePostStatus']);
    Route::post('/posts/{id}/toggle-pro',    [AdminController::class, 'togglePostPro']);
    Route::delete('/posts/{id}',             [AdminController::class, 'deletePost']);
    Route::post('/posts/bulk',               [AdminController::class, 'bulkPostAction']);

    // Comments
    Route::get('/comments',                  [AdminController::class, 'comments']);
    Route::post('/comments/{id}/approve',    [AdminController::class, 'approveComment']);
    Route::delete('/comments/{id}',          [AdminController::class, 'deleteComment']);

    // Content Reports
    Route::get('/reports',         [\App\Http\Controllers\ContentReportController::class, 'adminIndex']);
    Route::patch('/reports/{id}',  [\App\Http\Controllers\ContentReportController::class, 'adminAction']);

    // Tags
    Route::get('/tags',                      [AdminController::class, 'tags']);
    Route::post('/tags',                     [AdminController::class, 'storeTag']);
    Route::patch('/tags/{id}',               [AdminController::class, 'updateTag']);
    Route::delete('/tags/{id}',              [AdminController::class, 'deleteTag']);

    // Questions
    Route::get('/questions',                       [AdminController::class, 'questions']);
    Route::post('/questions/{id}/status',          [AdminController::class, 'updateQuestionStatus']);
    Route::delete('/questions/{id}',               [AdminController::class, 'deleteQuestion']);
    Route::delete('/answers/{id}',                 [AdminController::class, 'deleteAnswer']);

    // Polls
    Route::get('/polls',                     [AdminController::class, 'polls']);
    Route::post('/polls',                    [AdminController::class, 'storePoll']);
    Route::delete('/polls/{id}',             [AdminController::class, 'deletePoll']);

    // Ads
    Route::get('/ads',                       [AdminController::class, 'ads']);
    Route::post('/ads',                      [AdminController::class, 'storeAd']);
    Route::put('/ads/{id}',                  [AdminController::class, 'updateAd']);
    Route::delete('/ads/{id}',               [AdminController::class, 'deleteAd']);

    // Widgets
    Route::get('/widgets',                   [AdminController::class, 'widgets']);
    Route::post('/widgets',                  [AdminController::class, 'storeWidget']);
    Route::get('/widgets/{id}/edit',         [AdminController::class, 'editWidget']);
    Route::put('/widgets/{id}',              [AdminController::class, 'updateWidget']);
    Route::delete('/widgets/{id}',           [AdminController::class, 'deleteWidget']);

    // Media
    Route::get('/media',                     [AdminController::class, 'media']);
    Route::post('/media/upload',             [AdminController::class, 'uploadMedia']);
    Route::delete('/media/{filename}',       [AdminController::class, 'deleteMedia'])->where('filename', '.*');

    // Contact messages
    Route::get('/contacts',                  [AdminController::class, 'contacts']);
    Route::post('/contacts/{id}/read',       [AdminController::class, 'markContactRead']);
    Route::delete('/contacts/{id}',          [AdminController::class, 'deleteContact']);

    // Newsletter
    Route::get('/newsletter',                [AdminController::class, 'newsletter']);
    Route::post('/newsletter/send',          [AdminController::class, 'sendNewsletter']);
    Route::get('/newsletter/export',         [AdminController::class, 'exportSubscribers']);
    Route::delete('/newsletter/{id}',        [AdminController::class, 'deleteSubscriber']);

    // Content Settings
    Route::get('/content-settings',          [AdminController::class, 'contentSettings']);
    Route::post('/content-settings',         [AdminController::class, 'updateContentSettings']);
    Route::post('/content-settings/ai',      [AdminController::class, 'updateAiSettings']);
    Route::post('/content-settings/auto-delete', [AdminController::class, 'updateAutoDelete']);

    // Settings hub overview
    Route::get('/settings-hub',                         [AdminController::class, 'settingsHub']);
    // Settings — main + granular sub-routes
    Route::get('/settings',                             [AdminController::class, 'settings']);
    Route::post('/settings',                            [AdminController::class, 'updateSettings']);
    Route::post('/settings/url',                        [AdminController::class, 'updateSettingsUrl']);
    Route::post('/settings/name',                       [AdminController::class, 'updateSettingsName']);
    Route::post('/settings/tagline',                    [AdminController::class, 'updateSettingsTagline']);
    Route::post('/settings/contact',                    [AdminController::class, 'updateSettingsContact']);
    Route::post('/settings/social',                     [AdminController::class, 'updateSettingsSocial']);
    Route::post('/settings/analytics',                  [AdminController::class, 'updateSettingsAnalytics']);
    Route::post('/settings/behaviour',                  [AdminController::class, 'updateSettingsBehaviour']);
    Route::post('/settings/favicon',                    [AdminController::class, 'uploadFavicon']);
    Route::post('/settings/logo-dark',                  [AdminController::class, 'uploadLogoDark']);
    Route::post('/settings/logo-light',                 [AdminController::class, 'uploadLogoLight']);
    Route::post('/settings/logo-compact-dark',          [AdminController::class, 'uploadLogoCompactDark']);
    Route::post('/settings/logo-compact-light',         [AdminController::class, 'uploadLogoCompactLight']);
    Route::get('/settings/{asset}/remove',              [AdminController::class, 'removeBrandAsset'])->where('asset', '[a-z\-]+');

    // SEO
    Route::get('/seo',                       [AdminController::class, 'seo']);
    Route::post('/seo/meta',                 [AdminController::class, 'updateSeoMeta']);
    Route::post('/seo/robots',               [AdminController::class, 'updateRobots']);
    Route::post('/seo/settings',             [AdminController::class, 'updateSeoSettings']);

    // Roles & Permissions
    Route::get('/roles',                     [AdminController::class, 'roles']);
    Route::post('/roles',                    [AdminController::class, 'storeRole']);
    Route::put('/roles/{id}',                [AdminController::class, 'updateRole']);
    Route::delete('/roles/{id}',             [AdminController::class, 'deleteRole']);

    // Email Settings
    Route::get('/email-settings',            [AdminController::class, 'emailSettings']);
    Route::post('/email-settings',           [AdminController::class, 'updateEmailSettings']);
    Route::post('/email-settings/template',  [AdminController::class, 'updateEmailTemplate']);
    Route::post('/email-settings/test',      [AdminController::class, 'sendTestEmail']);

    // Security
    Route::get('/security',                  [AdminController::class, 'security']);
    Route::post('/security',                 [AdminController::class, 'updateSecurity']);
    Route::post('/security/captcha',         [AdminController::class, 'updateCaptcha']);
    Route::post('/security/cron/generate',   [AdminController::class, 'generateCronToken']);
    Route::post('/security/cron/revoke',     [AdminController::class, 'revokeCronToken']);

    // Storage
    Route::get('/storage',                   [AdminController::class, 'storage']);
    Route::post('/storage',                  [AdminController::class, 'updateStorage']);

    // Cache & Backup
    Route::get('/cache',                     [AdminController::class, 'cache']);
    Route::post('/cache/clear',              [AdminController::class, 'clearCache']);
    Route::get('/queue-settings',            [AdminController::class, 'queueSettings']);
    Route::post('/queue-settings',           [AdminController::class, 'updateQueueSettings']);
    Route::get('/backup',                    [AdminController::class, 'backup']);

    // Deploy
    Route::get('/deploy',                    [AdminController::class, 'deploy']);
    Route::post('/deploy/run',               [AdminController::class, 'runDeploy']);

    // Navigation
    Route::get('/navigation',                [\App\Http\Controllers\NavigationController::class, 'index']);
    Route::post('/navigation',               [\App\Http\Controllers\NavigationController::class, 'store']);
    Route::post('/navigation/settings',      [\App\Http\Controllers\NavigationController::class, 'updateSettings']);
    Route::post('/navigation/reorder',       [\App\Http\Controllers\NavigationController::class, 'reorder']);
    Route::post('/navigation/quick-add',     [\App\Http\Controllers\NavigationController::class, 'quickAdd']);
    Route::put('/navigation/{id}',           [\App\Http\Controllers\NavigationController::class, 'update']);
    Route::delete('/navigation/{id}',        [\App\Http\Controllers\NavigationController::class, 'destroy']);

    // Pages
    Route::get('/pages',                     [\App\Http\Controllers\PageController::class, 'index']);
    Route::get('/pages/create',              [\App\Http\Controllers\PageController::class, 'create']);
    Route::post('/pages',                    [\App\Http\Controllers\PageController::class, 'store']);
    Route::get('/pages/{id}/edit',           [\App\Http\Controllers\PageController::class, 'edit']);
    Route::put('/pages/{id}',                [\App\Http\Controllers\PageController::class, 'update']);
    Route::delete('/pages/{id}',             [\App\Http\Controllers\PageController::class, 'destroy']);

    // Themes
    Route::get('/themes',                    [\App\Http\Controllers\ThemeController::class, 'index']);
    Route::post('/themes/{theme}/activate',  [\App\Http\Controllers\ThemeController::class, 'activate']);

    // Badges
    Route::get('/badges',                    [\App\Http\Controllers\BadgeController::class, 'index']);
    Route::post('/badges',                   [\App\Http\Controllers\BadgeController::class, 'store']);
    Route::put('/badges/{id}',               [\App\Http\Controllers\BadgeController::class, 'update']);
    Route::delete('/badges/{id}',            [\App\Http\Controllers\BadgeController::class, 'destroy']);

    // Google News
    Route::get('/google-news',               [\App\Http\Controllers\GoogleNewsController::class, 'index']);
    Route::post('/google-news',              [\App\Http\Controllers\GoogleNewsController::class, 'update']);

    // RSS Feeds
    Route::get('/rss-feeds',                 [\App\Http\Controllers\RssFeedController::class, 'index']);
    Route::get('/rss-feeds/create',          [\App\Http\Controllers\RssFeedController::class, 'create']);
    Route::post('/rss-feeds',                [\App\Http\Controllers\RssFeedController::class, 'store']);
    Route::get('/rss-feeds/{id}/edit',       [\App\Http\Controllers\RssFeedController::class, 'edit']);
    Route::put('/rss-feeds/{id}',            [\App\Http\Controllers\RssFeedController::class, 'update']);
    Route::delete('/rss-feeds/{id}',         [\App\Http\Controllers\RssFeedController::class, 'destroy']);
    Route::post('/rss-feeds/{id}/import',    [\App\Http\Controllers\RssFeedController::class, 'importPosts']);

    // Localized Settings
    Route::get('/localized-settings',        [\App\Http\Controllers\LocalizedSettingsController::class, 'index']);
    Route::post('/localized-settings/general',  [\App\Http\Controllers\LocalizedSettingsController::class, 'updateGeneral']);
    Route::post('/localized-settings/contact',  [\App\Http\Controllers\LocalizedSettingsController::class, 'updateContact']);
    Route::post('/localized-settings/social',   [\App\Http\Controllers\LocalizedSettingsController::class, 'updateSocial']);
    Route::post('/localized-settings/cookies',  [\App\Http\Controllers\LocalizedSettingsController::class, 'updateCookies']);

    // Languages
    Route::get('/languages',                 [\App\Http\Controllers\LanguageController::class, 'index']);
    Route::post('/languages',                [\App\Http\Controllers\LanguageController::class, 'store']);
    Route::put('/languages/{id}',            [\App\Http\Controllers\LanguageController::class, 'update']);
    Route::delete('/languages/{id}',         [\App\Http\Controllers\LanguageController::class, 'destroy']);
    Route::post('/languages/default',        [\App\Http\Controllers\LanguageController::class, 'updateDefault']);
    Route::post('/languages/import',         [\App\Http\Controllers\LanguageController::class, 'import']);

    // Memberships (admin)
    Route::get('/memberships',                                      [AdminController::class, 'memberships']);
    Route::post('/memberships/plans',                               [AdminController::class, 'storePlan']);
    Route::put('/memberships/plans/{plan}',                         [AdminController::class, 'updatePlan']);
    Route::post('/memberships/plans/{plan}/toggle',                 [AdminController::class, 'togglePlan']);
    Route::delete('/memberships/plans/{plan}',                      [AdminController::class, 'deletePlan']);
    Route::post('/memberships/subscriptions/{subscription}/revoke',   [AdminController::class, 'revokeSubscription']);
    Route::post('/memberships/subscriptions/{subscription}/activate', [AdminController::class, 'activateSubscription']);
    Route::post('/memberships/stripe-settings',                       [AdminController::class, 'updateStripeSettings']);
    Route::post('/memberships/paypal-settings',                       [AdminController::class, 'updatePaypalSettings']);
    Route::post('/memberships/bank-settings',                         [AdminController::class, 'updateBankSettings']);
    Route::post('/memberships/premium-settings',                      [AdminController::class, 'updatePremiumSettings']);

    // Support tickets (admin)
    Route::get('/support',                              [AdminController::class, 'supportTickets']);
    Route::get('/support/{ticket}',                     [AdminController::class, 'supportShow']);
    Route::post('/support/{ticket}/reply',              [AdminController::class, 'supportReply']);
    Route::post('/support/{ticket}/assign',             [AdminController::class, 'supportAssign']);
    Route::post('/support/{ticket}/status',             [AdminController::class, 'supportStatus']);
    Route::delete('/support/{ticket}',                  [AdminController::class, 'supportDelete']);

    // AI Content
    Route::get('/ai-content',                           [AdminController::class, 'aiContent']);
    Route::post('/ai-content/topics',                   [AdminController::class, 'storeAiTopic']);
    Route::post('/ai-content/topics/{topic}/toggle',    [AdminController::class, 'toggleAiTopic']);
    Route::delete('/ai-content/topics/{topic}',         [AdminController::class, 'deleteAiTopic']);
    Route::post('/ai-content/topics/{topic}/run',       [AdminController::class, 'runAiTopic']);
    Route::post('/ai-content/run-all',                  [AdminController::class, 'runAllAiTopics']);
    Route::post('/ai-content/settings',                 [AdminController::class, 'updateGeminiSettings']);
    Route::post('/ai-content/drafts/{post}/publish',    [AdminController::class, 'publishAiDraft']);
    Route::delete('/ai-content/drafts/{post}',          [AdminController::class, 'deleteAiDraft']);

    // Social Pages (admin module)
    Route::get('/social-pages',                          [AdminController::class, 'adminSocialPages']);
    Route::post('/social-pages/{id}/action',             [AdminController::class, 'adminSocialPageAction']);

    // Page Categories
    Route::get('/page-categories',                       [AdminController::class, 'adminPageCategories']);
    Route::post('/page-categories',                      [AdminController::class, 'adminPageCategoryStore']);
    Route::post('/page-categories/{id}/toggle',          [AdminController::class, 'adminPageCategoryToggle']);
    Route::delete('/page-categories/{id}',               [AdminController::class, 'adminPageCategoryDelete']);
});

// ── Account Portal (account.dewanlabung.com.np OR /account/*) ──────────────
// Subdomain routing (requires DNS + Nginx setup for the subdomain)
Route::domain('account.' . parse_url(config('app.url'), PHP_URL_HOST))->middleware(['auth'])->group(function () {
    Route::get('/',                  [AccountController::class, 'home']);
    Route::get('/personal-info',     [AccountController::class, 'personalInfo']);
    Route::patch('/personal-info',   [AccountController::class, 'updatePersonalInfo']);
    Route::post('/avatar',           [AccountController::class, 'updateAvatar']);
    Route::get('/security',          [AccountController::class, 'security']);
    Route::patch('/security/password', [AccountController::class, 'changePassword']);
    Route::get('/subscriptions',     [AccountController::class, 'subscriptions']);
    Route::get('/privacy',           [AccountController::class, 'privacy']);
    Route::delete('/delete',         [AccountController::class, 'deleteAccount']);
});

// Path-based fallback (always works, same server)
Route::middleware(['auth'])->prefix('account')->group(function () {
    Route::get('/',                    [AccountController::class, 'home']);
    Route::get('/personal-info',       [AccountController::class, 'personalInfo']);
    Route::patch('/personal-info',     [AccountController::class, 'updatePersonalInfo']);
    Route::post('/avatar',             [AccountController::class, 'updateAvatar']);
    Route::get('/security',            [AccountController::class, 'security']);
    Route::patch('/security/password', [AccountController::class, 'changePassword']);
    Route::get('/subscriptions',       [AccountController::class, 'subscriptions']);
    Route::get('/privacy',             [AccountController::class, 'privacy']);
    Route::delete('/delete',           [AccountController::class, 'deleteAccount']);

    // API Tokens
    Route::get('/tokens',              [\App\Http\Controllers\Account\TokenController::class, 'index']);
    Route::post('/tokens',             [\App\Http\Controllers\Account\TokenController::class, 'store']);
    Route::delete('/tokens/{id}',      [\App\Http\Controllers\Account\TokenController::class, 'destroy']);
    Route::get('/tokens/{id}/activity',[\App\Http\Controllers\Account\TokenController::class, 'activity']);

    // Active Sessions
    Route::get('/sessions',            [\App\Http\Controllers\Account\SessionsController::class, 'index']);
    Route::delete('/sessions/{id}',    [\App\Http\Controllers\Account\SessionsController::class, 'destroy']);
    Route::delete('/sessions',         [\App\Http\Controllers\Account\SessionsController::class, 'destroyAll']);

    // Notification Preferences
    Route::get('/notifications',       [\App\Http\Controllers\Account\NotificationPreferenceController::class, 'index']);
    Route::post('/notifications',      [\App\Http\Controllers\Account\NotificationPreferenceController::class, 'update']);

    // Data & Privacy (extended)
    Route::get('/data-privacy',        [\App\Http\Controllers\Account\DataPrivacyController::class, 'index']);
    Route::post('/data-privacy/export',[\App\Http\Controllers\Account\DataPrivacyController::class, 'requestExport']);
    Route::get('/export/download',     [\App\Http\Controllers\Account\DataPrivacyController::class, 'download']);
    Route::post('/data-privacy/delete',[\App\Http\Controllers\Account\DataPrivacyController::class, 'requestDeletion']);
    Route::post('/data-privacy/cancel-deletion', [\App\Http\Controllers\Account\DataPrivacyController::class, 'cancelDeletion']);

    // Account recovery settings
    Route::get('/recovery',              [\App\Http\Controllers\Auth\AccountRecoveryController::class, 'showRecoverySettings']);
    Route::post('/recovery/email',       [\App\Http\Controllers\Auth\AccountRecoveryController::class, 'saveRecoveryEmail']);
    Route::get('/recovery/verify-email', [\App\Http\Controllers\Auth\AccountRecoveryController::class, 'verifyRecoveryEmail']);
    Route::delete('/recovery/email',     [\App\Http\Controllers\Auth\AccountRecoveryController::class, 'removeRecoveryEmail']);
});

// Membership (frontend)
Route::get('/membership', [MembershipController::class, 'plans']);
Route::get('/membership/success', [MembershipController::class, 'success']);
Route::get('/membership/pending', [MembershipController::class, 'pending'])->middleware('auth');
Route::get('/membership/{plan}/checkout', [MembershipController::class, 'checkout'])->middleware('auth');
Route::post('/membership/{plan}/process', [MembershipController::class, 'processCheckout'])->middleware('auth');
Route::post('/membership/cancel', [MembershipController::class, 'cancel'])->middleware('auth');

// Stripe webhook (no auth/csrf)
Route::post('/webhook/stripe', [MembershipController::class, 'webhook'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Support Center (frontend — auth required)
Route::middleware('auth')->prefix('support')->group(function () {
    Route::get('/',              [\App\Http\Controllers\SupportController::class, 'index']);
    Route::get('/create',        [\App\Http\Controllers\SupportController::class, 'create']);
    Route::post('/',             [\App\Http\Controllers\SupportController::class, 'store']);
    Route::get('/{ticket}',      [\App\Http\Controllers\SupportController::class, 'show']);
    Route::post('/{ticket}/reply', [\App\Http\Controllers\SupportController::class, 'reply']);
    Route::post('/{ticket}/close', [\App\Http\Controllers\SupportController::class, 'close']);
});

// GitHub deploy webhook (no auth, verified by HMAC secret)
Route::post('/webhook/deploy', [AdminController::class, 'webhookDeploy'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Post Series
Route::get('/series', [\App\Http\Controllers\PostSeriesController::class, 'index']);
Route::get('/series/create', [\App\Http\Controllers\PostSeriesController::class, 'create'])->middleware('auth');
Route::post('/series', [\App\Http\Controllers\PostSeriesController::class, 'store'])->middleware('auth');
Route::get('/series/{series:slug}', [\App\Http\Controllers\PostSeriesController::class, 'show']);

// Questions (public)
Route::get('/questions',                          [\App\Http\Controllers\QuestionController::class, 'index']);
Route::get('/ask-question',                       [\App\Http\Controllers\QuestionController::class, 'create']);
Route::post('/ask-question',                      [\App\Http\Controllers\QuestionController::class, 'store'])->middleware('auth');
Route::get('/questions/{slug}',                   [\App\Http\Controllers\QuestionController::class, 'show']);
Route::post('/questions/{id}/answers',            [\App\Http\Controllers\QuestionController::class, 'storeAnswer'])->middleware('auth');
Route::post('/questions/{id}/best/{answer}',      [\App\Http\Controllers\QuestionController::class, 'markBestAnswer'])->middleware('auth');
Route::post('/questions/{id}/vote',               [\App\Http\Controllers\QuestionController::class, 'voteQuestion'])->middleware('auth');
Route::post('/questions/{id}/follow',             [\App\Http\Controllers\QuestionController::class, 'toggleFollow'])->middleware('auth');
Route::post('/questions/{id}/bookmark',           [\App\Http\Controllers\QuestionController::class, 'toggleBookmark'])->middleware('auth');
Route::post('/questions/{id}/flag',               [\App\Http\Controllers\QuestionController::class, 'flagQuestion'])->middleware('auth');
Route::post('/questions/{id}/close',              [\App\Http\Controllers\QuestionController::class, 'closeQuestion'])->middleware('auth');
Route::post('/questions/{id}/reopen',             [\App\Http\Controllers\QuestionController::class, 'reopenQuestion'])->middleware('auth');
Route::get('/questions/{id}/edit',                [\App\Http\Controllers\QuestionController::class, 'edit'])->middleware('auth');
Route::put('/questions/{id}',                     [\App\Http\Controllers\QuestionController::class, 'update'])->middleware('auth');
Route::post('/answers/{id}/vote',                 [\App\Http\Controllers\QuestionController::class, 'voteAnswer'])->middleware('auth');
Route::post('/answers/{id}/comments',             [\App\Http\Controllers\QuestionController::class, 'storeComment'])->middleware('auth');
Route::put('/answers/{id}',                       [\App\Http\Controllers\QuestionController::class, 'updateAnswer'])->middleware('auth');

// Social Pages (Facebook-style pages)
Route::get('/pages',                                [SocialPageController::class, 'index']);
Route::get('/pages/map',                            [SocialPageController::class, 'mapView']);
Route::get('/api/pages/map-pins',                   [SocialPageController::class, 'mapPins']);
Route::get('/pages/start',                          [SocialPageController::class, 'intro']);
Route::middleware('auth')->group(function () {
    // Create
    Route::get('/pages/create',                              [SocialPageController::class, 'create']);
    Route::post('/pages',                                    [SocialPageController::class, 'store']);
    Route::get('/api/pages/check-username',                  [SocialPageController::class, 'checkUsername']);
    // Follow
    Route::post('/pages/{slug}/follow',                      [SocialPageController::class, 'follow'])->name('pages.follow');
    // Posts
    Route::post('/pages/{slug}/posts',                       [SocialPageController::class, 'storePost']);
    Route::delete('/pages/{slug}/posts/{postId}',            [SocialPageController::class, 'deletePost']);
    Route::post('/pages/{slug}/posts/{postId}/like',         [SocialPageController::class, 'likePost']);
    Route::post('/pages/{slug}/posts/{postId}/react',        [SocialPageController::class, 'reactPost']);
    Route::get('/pages/{slug}/posts/{postId}/comments',      [SocialPageController::class, 'loadComments']);
    Route::post('/pages/{slug}/posts/{postId}/comments',     [SocialPageController::class, 'storeComment']);
    Route::delete('/pages/{slug}/posts/{postId}/comments/{commentId}', [SocialPageController::class, 'deleteComment']);
    Route::post('/pages/{slug}/posts/{postId}/pin',          [SocialPageController::class, 'pinPost']);
    Route::delete('/pages/{slug}/posts/{postId}/pin',        [SocialPageController::class, 'unpinPost']);
    Route::post('/pages/{slug}/posts/{entityId}/report',     [SocialPageController::class, 'report'])->defaults('type', 'post');
    // Reviews
    Route::post('/pages/{slug}/reviews',                     [SocialPageController::class, 'storeReview']);
    Route::delete('/pages/{slug}/reviews',                   [SocialPageController::class, 'deleteReview']);
    Route::post('/pages/{slug}/reviews/{entityId}/report',   [SocialPageController::class, 'report'])->defaults('type', 'review');
    // Reports
    Route::post('/pages/{slug}/report',                      [SocialPageController::class, 'report']);
    // Moderation queue
    Route::get('/pages/{slug}/moderation',                   [SocialPageController::class, 'moderationQueue']);
    Route::post('/pages/{slug}/moderation/{reportId}',       [SocialPageController::class, 'moderationAction']);
    // Admin management
    Route::get('/pages/{slug}/admins',                       [SocialPageController::class, 'manageAdmins']);
    Route::post('/pages/{slug}/admins/invite',               [SocialPageController::class, 'inviteAdmin']);
    Route::post('/pages/{slug}/admins/accept',               [SocialPageController::class, 'acceptAdminInvite']);
    Route::delete('/pages/{slug}/admins/{userId}',           [SocialPageController::class, 'removeAdmin']);
    // Verification
    Route::post('/pages/{slug}/request-verification',        [SocialPageController::class, 'requestVerification']);
    // Status
    Route::post('/pages/{slug}/disable',                     [SocialPageController::class, 'disable']);
    Route::post('/pages/{slug}/enable',                      [SocialPageController::class, 'enable']);
    Route::delete('/pages/{slug}',                           [SocialPageController::class, 'destroy']);
    // Dashboard & Settings
    Route::get('/pages/{slug}/dashboard',                    [SocialPageController::class, 'dashboard']);
    Route::get('/pages/{slug}/settings',                     [SocialPageController::class, 'settings']);
    Route::put('/pages/{slug}/settings',                     [SocialPageController::class, 'updateSettings']);
    // Poll voting
    Route::post('/pages/{slug}/posts/{postId}/vote',         [SocialPageController::class, 'votePoll']);
    // Q&A
    Route::post('/pages/{slug}/qna',                         [SocialPageController::class, 'storeQna']);
    Route::post('/pages/{slug}/qna/{qnaId}/answer',          [SocialPageController::class, 'answerQna']);
    Route::delete('/pages/{slug}/qna/{qnaId}',               [SocialPageController::class, 'deleteQna']);
    // Products
    Route::post('/pages/{slug}/products',                    [SocialPageController::class, 'storeProduct']);
    Route::delete('/pages/{slug}/products/{productId}',      [SocialPageController::class, 'deleteProduct']);
    // Announcement & Highlights
    Route::post('/pages/{slug}/announcement',                [SocialPageController::class, 'updateAnnouncement']);
    Route::post('/pages/{slug}/highlights',                  [SocialPageController::class, 'updateHighlights']);
    // Archive
    Route::post('/pages/{slug}/archive',                     [SocialPageController::class, 'archive']);
    Route::post('/pages/{slug}/unarchive',                   [SocialPageController::class, 'unarchive']);
    // Blocking
    Route::get('/pages/{slug}/blocked-users',                [SocialPageController::class, 'blockedUsers']);
    Route::post('/pages/{slug}/block/{userId}',              [SocialPageController::class, 'blockUser']);
    Route::delete('/pages/{slug}/block/{userId}',            [SocialPageController::class, 'unblockUser']);
    // Activity log
    Route::get('/pages/{slug}/activity-log',                 [SocialPageController::class, 'activityLog']);
    // Stories
    Route::get('/pages/{slug}/stories',                      [SocialPageController::class, 'stories']);
    Route::post('/pages/{slug}/stories',                     [SocialPageController::class, 'storeStory']);
    Route::delete('/pages/{slug}/stories/{storyId}',         [SocialPageController::class, 'deleteStory']);
    // Notification preferences (for followers)
    Route::post('/pages/{slug}/notification-prefs',          [SocialPageController::class, 'updateNotificationPrefs']);
    // Comments manager
    Route::get('/pages/{slug}/comments-manager',             [SocialPageController::class, 'commentsManager']);
    // Follow suggestions API
    Route::get('/api/pages/{slug}/suggestions',              [SocialPageController::class, 'followSuggestions']);
    // FAQ
    Route::post('/pages/{slug}/faq',                         [SocialPageController::class, 'storeFaq']);
    Route::put('/pages/{slug}/faq/{faqId}',                  [SocialPageController::class, 'updateFaq']);
    Route::delete('/pages/{slug}/faq/{faqId}',               [SocialPageController::class, 'deleteFaq']);
    // Data export (owner only)
    Route::get('/pages/{slug}/export',                       [SocialPageController::class, 'exportData']);
});
Route::get('/pages/{slug}',                         [SocialPageController::class, 'show']);

// Dashboard (auth required)
Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/analytics', [DashboardController::class, 'analytics']);
    Route::get('/posts/create', [DashboardController::class, 'create']);
    Route::post('/posts', [DashboardController::class, 'store']);
    Route::get('/posts/{id}/edit', [DashboardController::class, 'edit']);
    Route::put('/posts/{id}', [DashboardController::class, 'update']);
    Route::get('/pages', [SocialPageController::class, 'myPages']);
});

// Content reporting
Route::middleware('auth')->post('/report', [\App\Http\Controllers\ContentReportController::class, 'store'])->middleware('throttle:10,1');

// Recipes community
Route::get('/recipe',                    [\App\Http\Controllers\RecipeController::class, 'index']);
Route::get('/recipe/create',             [\App\Http\Controllers\RecipeController::class, 'create'])->middleware('auth');
Route::post('/recipe',                   [\App\Http\Controllers\RecipeController::class, 'store'])->middleware('auth');
Route::get('/recipe/{slug}',             [\App\Http\Controllers\RecipeController::class, 'show']);
Route::post('/recipe/{recipe}/like',     [\App\Http\Controllers\RecipeController::class, 'like'])->middleware('auth');
Route::post('/recipe/{recipe}/rate',     [\App\Http\Controllers\RecipeController::class, 'rate'])->middleware('throttle:5,1')->middleware('auth');

// Events community
Route::get('/events',                    [\App\Http\Controllers\EventController::class, 'index']);
Route::get('/events/create',             [\App\Http\Controllers\EventController::class, 'create'])->middleware('auth');
Route::post('/events',                   [\App\Http\Controllers\EventController::class, 'store'])->middleware('auth');
Route::get('/events/{slug}',             [\App\Http\Controllers\EventController::class, 'show']);
Route::post('/events/{event}/attend',    [\App\Http\Controllers\EventController::class, 'attend'])->middleware('auth');
Route::get('/events/{event}/attendees/export', [\App\Http\Controllers\EventController::class, 'exportAttendees'])->middleware('auth');

// ─── Live Streaming ───────────────────────────────────────────────────────────
Route::get('/live',                        [\App\Http\Controllers\LiveStreamController::class, 'index']);
Route::get('/live/create',                 [\App\Http\Controllers\LiveStreamController::class, 'create'])->middleware('auth');
Route::post('/live',                       [\App\Http\Controllers\LiveStreamController::class, 'store'])->middleware('auth');
Route::get('/live/{liveStream}',           [\App\Http\Controllers\LiveStreamController::class, 'show']);
Route::post('/live/{liveStream}/end',      [\App\Http\Controllers\LiveStreamController::class, 'end'])->middleware('auth');
Route::post('/live/{liveStream}/chat',     [\App\Http\Controllers\LiveStreamController::class, 'chat'])->middleware('throttle:30,1');
Route::get('/live/{liveStream}/poll',      [\App\Http\Controllers\LiveStreamController::class, 'poll']);

// ─── Reels ────────────────────────────────────────────────────────────────────
Route::get('/reels',                    [\App\Http\Controllers\ReelController::class, 'index']);
Route::get('/reels/create',             [\App\Http\Controllers\ReelController::class, 'create'])->middleware('auth');
Route::post('/reels',                   [\App\Http\Controllers\ReelController::class, 'store'])->middleware('auth');
Route::post('/reels/{reel}/like',       [\App\Http\Controllers\ReelController::class, 'like'])->middleware('auth');
Route::post('/reels/{reel}/comment',    [\App\Http\Controllers\ReelController::class, 'comment'])->middleware('auth');
Route::get('/reels/{reel}/comments',    [\App\Http\Controllers\ReelController::class, 'comments']);

// ─── Watch Party ──────────────────────────────────────────────────────────────
Route::get('/watch-party',                          [\App\Http\Controllers\WatchPartyController::class, 'index']);
Route::get('/watch-party/create',                   [\App\Http\Controllers\WatchPartyController::class, 'create'])->middleware('auth');
Route::post('/watch-party',                         [\App\Http\Controllers\WatchPartyController::class, 'store'])->middleware('auth');
Route::get('/watch-party/{code}',                   [\App\Http\Controllers\WatchPartyController::class, 'show']);
Route::post('/watch-party/{code}/join',             [\App\Http\Controllers\WatchPartyController::class, 'join'])->middleware('auth');
Route::middleware('auth')->group(function () {
    Route::post('/watch-party/{watchParty}/sync',    [\App\Http\Controllers\WatchPartyController::class, 'sync']);
    Route::get('/watch-party/{watchParty}/sync',     [\App\Http\Controllers\WatchPartyController::class, 'getSync']);
    Route::post('/watch-party/{watchParty}/message', [\App\Http\Controllers\WatchPartyController::class, 'sendMessage'])->middleware('throttle:30,1');
    Route::get('/watch-party/{watchParty}/messages', [\App\Http\Controllers\WatchPartyController::class, 'pollMessages']);
});

// ─── Virtual Gifts / Wallet ───────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/wallet',               [\App\Http\Controllers\GiftController::class, 'wallet']);
    Route::post('/gifts/{user}',        [\App\Http\Controllers\GiftController::class, 'send'])->middleware('throttle:20,1');
    Route::post('/wallet/buy',          [\App\Http\Controllers\GiftController::class, 'buyCoins']);
});

// ─── Inbox / Disappearing DMs ─────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/inbox',                                [\App\Http\Controllers\InboxController::class, 'index']);
    Route::get('/inbox/{conversation}',                 [\App\Http\Controllers\InboxController::class, 'show']);
    Route::post('/inbox/start',                         [\App\Http\Controllers\InboxController::class, 'start']);
    Route::post('/inbox/{conversation}/send',           [\App\Http\Controllers\InboxController::class, 'send'])->middleware('throttle:60,1');
    Route::get('/inbox/{conversation}/poll',            [\App\Http\Controllers\InboxController::class, 'poll']);
});

// ─── Stories & Highlights ─────────────────────────────────────────────────────
Route::get('/stories',                                      [\App\Http\Controllers\StoryController::class, 'index']);
Route::get('/stories/{story}',                              [\App\Http\Controllers\StoryController::class, 'show']);
Route::middleware('auth')->group(function () {
    Route::post('/stories',                                 [\App\Http\Controllers\StoryController::class, 'store'])->middleware('throttle:20,1');
    Route::delete('/stories/{story}',                       [\App\Http\Controllers\StoryController::class, 'destroy']);
    Route::post('/highlights',                              [\App\Http\Controllers\StoryController::class, 'storeHighlight']);
    Route::post('/highlights/{highlight}/add',              [\App\Http\Controllers\StoryController::class, 'addToHighlight']);
    Route::delete('/highlights/{highlight}',                [\App\Http\Controllers\StoryController::class, 'destroyHighlight']);
});

// ─── Trending Topics ──────────────────────────────────────────────────────────
Route::get('/trending',                                     [\App\Http\Controllers\TrendingController::class, 'index']);

// ─── Broadcast Channels ───────────────────────────────────────────────────────
Route::get('/channels',                                     [\App\Http\Controllers\BroadcastChannelController::class, 'index']);
Route::get('/channels/create',                              [\App\Http\Controllers\BroadcastChannelController::class, 'create'])->middleware('auth');
Route::post('/channels',                                    [\App\Http\Controllers\BroadcastChannelController::class, 'store'])->middleware('auth');
Route::get('/channels/{broadcastChannel:slug}',             [\App\Http\Controllers\BroadcastChannelController::class, 'show']);
Route::middleware('auth')->group(function () {
    Route::post('/channels/{broadcastChannel:slug}/subscribe', [\App\Http\Controllers\BroadcastChannelController::class, 'subscribe']);
    Route::post('/channels/{broadcastChannel:slug}/broadcast', [\App\Http\Controllers\BroadcastChannelController::class, 'broadcast'])->middleware('throttle:20,1');
    Route::post('/channel-messages/{channelMessage}/react',    [\App\Http\Controllers\BroadcastChannelController::class, 'react']);
});

// ─── QR Profile Card ──────────────────────────────────────────────────────────
Route::get('/profile/{username}/qr-card',                   [\App\Http\Controllers\QrCardController::class, 'show']);
