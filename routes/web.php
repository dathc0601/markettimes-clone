<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RssController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// Public Frontend Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [SearchController::class, 'index'])->name('search');

// RSS Feeds
Route::get('/rss', [RssController::class, 'index'])->name('rss');
Route::get('/rss/{category:slug}', [RssController::class, 'category'])->name('rss.category');

// Static pages
Route::get('/page/{page:slug}', [PageController::class, 'show'])->name('page.show');

// Profile (Authenticated)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Comment Routes (with rate limiting)
Route::post('/articles/{articleId}/comments', [CommentController::class, 'store'])
    ->middleware('throttle:10,60')
    ->name('comments.store');

Route::patch('/comments/{comment}', [CommentController::class, 'update'])
    ->middleware('throttle:5,60')
    ->name('comments.update');

Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
    ->middleware('throttle:5,60')
    ->name('comments.destroy');

Route::post('/comments/{comment}/like', [CommentController::class, 'like'])
    ->middleware('throttle:30,60')
    ->name('comments.like');

// Article pages (matches: /article-slug-123.html)
Route::get('/{slug}.html', [ArticleController::class, 'show'])->name('article.show');

// Category pages (after article route - .html extension differentiates them)
Route::get('/{category:slug}', [CategoryController::class, 'show'])->name('category.show');
