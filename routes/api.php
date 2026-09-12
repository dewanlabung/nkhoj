<?php

use App\Domains\Account\Http\Controllers\Api\V1\AccountController as AccountApiController;
use App\Http\Controllers\Api\AIController;
use App\Http\Controllers\Api\V1;
use Illuminate\Support\Facades\Route;

// Legacy AI routes
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/ai/author',       [AIController::class, 'author']);
    Route::post('/ai/moderation',   [AIController::class, 'moderation']);
    Route::post('/ai/search',       [AIController::class, 'search']);
    Route::post('/ai/survey/{id}',  [AIController::class, 'survey']);
    Route::post('/ai/newsletter',   [AIController::class, 'newsletter']);
});

// API v1
Route::prefix('v1')->name('api.v1.')->group(function () {
    // Auth — register, issue / revoke tokens
    Route::post('/auth/register', [V1\AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('/auth/token',    [V1\AuthController::class, 'token'])->middleware('throttle:10,1');
    Route::delete('/auth/token',  [V1\AuthController::class, 'revoke'])->middleware('auth:sanctum');
    Route::get('/auth/me',        [V1\AuthController::class, 'me'])->middleware('auth:sanctum');

    // Public content endpoints (read-only, throttled)
    Route::middleware('throttle:120,1')->group(function () {
        Route::get('/posts',              [V1\PostController::class, 'index']);
        Route::get('/posts/{slug}',       [V1\PostController::class, 'show']);
        Route::get('/events',             [V1\EventController::class, 'index']);
        Route::get('/events/{slug}',      [V1\EventController::class, 'show']);
        Route::get('/recipes',            [V1\RecipeController::class, 'index']);
        Route::get('/recipes/{slug}',     [V1\RecipeController::class, 'show']);
        Route::get('/search',             [V1\SearchController::class, 'index']);
        Route::get('/users/{username}',   [AccountApiController::class, 'profile']);
    });

    // Authenticated routes
    Route::middleware(['auth:sanctum', \App\Http\Middleware\LogApiRequest::class])->group(function () {
        // Device tokens (push notifications)
        Route::post('/device-tokens',    [V1\DeviceTokenController::class, 'register']);
        Route::delete('/device-tokens',  [V1\DeviceTokenController::class, 'unregister']);

        // Account
        Route::get('/account/me',        [AccountApiController::class, 'me']);
        Route::patch('/account/me',      [AccountApiController::class, 'update']);
        Route::post('/account/avatar',   [AccountApiController::class, 'updateAvatar']);
        Route::patch('/account/password',[AccountApiController::class, 'changePassword']);

        // Social graph
        Route::post('/users/{id}/follow',[AccountApiController::class, 'follow']);
    });
});
