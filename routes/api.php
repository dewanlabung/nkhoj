<?php

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
    // Auth — issue / revoke tokens
    Route::post('/auth/token',  [V1\AuthController::class, 'token'])->middleware('throttle:10,1');
    Route::delete('/auth/token',[V1\AuthController::class, 'revoke'])->middleware('auth:sanctum');
    Route::get('/auth/me',      [V1\AuthController::class, 'me'])->middleware('auth:sanctum');

    // Authenticated routes
    Route::middleware(['auth:sanctum', \App\Http\Middleware\LogApiRequest::class])->group(function () {
        // Device tokens (push notifications)
        Route::post('/device-tokens',          [V1\DeviceTokenController::class, 'register']);
        Route::delete('/device-tokens',        [V1\DeviceTokenController::class, 'unregister']);
    });
});
