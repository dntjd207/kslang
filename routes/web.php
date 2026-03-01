<?php

use App\Http\Controllers\Admin\AppSettingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\SlangController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// === 공개 라우트 ===
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/privacy', [PublicController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PublicController::class, 'terms'])->name('terms');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// === 관리자 라우트 ===
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login'])->middleware('throttle:5,1');
    });

    Route::post('logout', [LoginController::class, 'logout'])
        ->middleware('auth')
        ->name('logout');

    Route::middleware('auth')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');

        Route::post('slangs/reorder', [SlangController::class, 'reorder'])->name('slangs.reorder');
        Route::resource('slangs', SlangController::class)->except(['show']);
        Route::patch('slangs/{slang}/toggle', [SlangController::class, 'toggle'])->name('slangs.toggle');
        Route::delete('slangs/{slang}/audio', [SlangController::class, 'destroyAudio'])->name('slangs.destroyAudio');

        Route::get('pages/{slug}/edit', [PageController::class, 'edit'])->name('pages.edit')->where('slug', 'privacy|terms');
        Route::put('pages/{slug}', [PageController::class, 'update'])->name('pages.update')->where('slug', 'privacy|terms');

        Route::get('app-settings', [AppSettingController::class, 'edit'])->name('app-settings.edit');
        Route::put('app-settings', [AppSettingController::class, 'update'])->name('app-settings.update');
    });
});
