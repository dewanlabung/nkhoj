<?php

use App\Http\Controllers\Api\AIController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function () {
    Route::post('/ai/author',       [AIController::class, 'author']);
    Route::post('/ai/moderation',   [AIController::class, 'moderation']);
    Route::post('/ai/search',       [AIController::class, 'search']);
    Route::post('/ai/survey/{id}',  [AIController::class, 'survey']);
    Route::post('/ai/newsletter',   [AIController::class, 'newsletter']);
});
