<?php

use App\Http\Controllers\Admin\ApiPlaygroundController;
use App\Http\Controllers\Admin\AppSettingController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\SlangController;
use App\Http\Controllers\Admin\SupertoneTtsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PublicSlangController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

// === 공개 라우트 ===
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{blogPost:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/korean-slang', [PublicSlangController::class, 'index'])->name('slangs.public.index');
Route::get('/korean-slang/{slang:public_slug}', [PublicSlangController::class, 'show'])->name('slangs.public.show');
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

        Route::post('blog-posts/generate-draft', [BlogPostController::class, 'generateDraft'])->name('blog-posts.generate-draft');
        Route::post('blog-posts/translate', [BlogPostController::class, 'translate'])->name('blog-posts.translate');
        Route::post('blog-posts/autosave', [BlogPostController::class, 'autosave'])->name('blog-posts.autosave');
        Route::resource('blog-posts', BlogPostController::class)->except(['show']);

        Route::post('slangs/reorder', [SlangController::class, 'reorder'])->name('slangs.reorder');
        Route::post('slangs/detailed-store', [SlangController::class, 'detailedStore'])->name('slangs.detailedStore');
        Route::post('slangs/quick-store', [SlangController::class, 'quickStore'])->name('slangs.quickStore');
        Route::get('slangs/{slang}/thread-posts', [SlangController::class, 'showThreadPosts'])->name('slangs.threadPosts.show');
        Route::post('slangs/{slang}/generate-thread-posts', [SlangController::class, 'generateThreadPosts'])->name('slangs.threadPosts.generate');
        Route::post('slangs/{slang}/generate-audio', [SlangController::class, 'generateAudio'])->name('slangs.generateAudio');
        Route::post('slangs/{slang}/generate-example-audio', [SlangController::class, 'generateExampleAudio'])->name('slangs.generateExampleAudio');
        Route::resource('slangs', SlangController::class)->except(['show']);
        Route::post('slangs/{slang}/regenerate-section', [SlangController::class, 'regenerateSection'])->name('slangs.regenerateSection');
        Route::patch('slangs/{slang}/toggle', [SlangController::class, 'toggle'])->name('slangs.toggle');
        Route::patch('slangs/{slang}/approve', [SlangController::class, 'approve'])->name('slangs.approve');
        Route::patch('slangs/{slang}/reject', [SlangController::class, 'reject'])->name('slangs.reject');
        Route::delete('slangs/{slang}/audio', [SlangController::class, 'destroyAudio'])->name('slangs.destroyAudio');

        Route::get('pages/{slug}/edit', [PageController::class, 'edit'])->name('pages.edit')->where('slug', 'terms');
        Route::put('pages/{slug}', [PageController::class, 'update'])->name('pages.update')->where('slug', 'terms');

        Route::get('app-settings', [AppSettingController::class, 'edit'])->name('app-settings.edit');
        Route::put('app-settings', [AppSettingController::class, 'update'])->name('app-settings.update');

        Route::get('api-playground', [ApiPlaygroundController::class, 'index'])->name('api-playground.index');
        Route::post('api-playground/request', [ApiPlaygroundController::class, 'execute'])->name('api-playground.execute');

        Route::get('supertone-tts', [SupertoneTtsController::class, 'index'])->name('supertone-tts.index');
        Route::post('supertone-tts/generate', [SupertoneTtsController::class, 'generate'])->name('supertone-tts.generate');
    });
});
