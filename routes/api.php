<?php

use App\Http\Controllers\Api\V1\AppController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\SlangController;
use App\Http\Middleware\ApiKeyMiddleware;

Route::prefix('v1')->middleware(ApiKeyMiddleware::class)->group(function () {
    Route::get('slangs/search', [SlangController::class, 'search']);
    Route::get('slangs/random', [SlangController::class, 'random']);
    Route::get('slangs/daily', [SlangController::class, 'daily']);
    Route::get('slangs', [SlangController::class, 'index']);
    Route::get('slangs/{slang}', [SlangController::class, 'show']);

    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);

    Route::get('app/version', [AppController::class, 'version']);
    Route::get('app/sync', [AppController::class, 'sync']);

    Route::get('pages/{slug}', [PageController::class, 'show'])
        ->where('slug', 'privacy|terms');
});
