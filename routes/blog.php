<?php

use Cleargoal\Blog\Http\Controllers\BlogController;
use Cleargoal\Blog\Http\Controllers\UserBlogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Blog Package Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the BlogServiceProvider and are automatically
| registered with your application. You can customize the prefix and
| middleware in config/blog.php.
|
*/

$prefix = config('blog.routes.prefix', 'blog');
$namePrefix = config('blog.routes.name_prefix', 'blog.');
$publicMiddleware = config('blog.routes.middleware', ['web']);
$userMiddleware = config('blog.authorization.middleware.user', ['web', 'auth', 'verified']);

// Public Blog Routes
Route::middleware($publicMiddleware)
    ->prefix($prefix)
    ->name($namePrefix)
    ->group(function () {
        // Blog index/listing
        Route::get('/', [BlogController::class, 'index'])->name('index');

        // Category filter
        Route::get('/category/{category:slug}', [BlogController::class, 'category'])->name('category');

        // Tag filter - IMPORTANT: Place before /{slug} to avoid route conflicts
        Route::get('/tag/{slug}', [BlogController::class, 'tag'])->name('tag');

        // Single post view
        Route::get('/{slug}', [BlogController::class, 'show'])->name('show');
    });

// User-specific Blog Routes (requires authentication)
Route::middleware($userMiddleware)
    ->prefix($prefix)
    ->name($namePrefix)
    ->group(function () {
        // User blog management
        Route::get('/my-posts', [UserBlogController::class, 'index'])->name('my-posts');
        Route::get('/favorites', [UserBlogController::class, 'favorites'])->name('favorites');
        Route::get('/analytics', [UserBlogController::class, 'analytics'])->name('analytics');

        // Post interactions (like, favorite, rate)
        if (config('blog.features.ratings', true)) {
            Route::post('/{post}/rate', [BlogController::class, 'rate'])->name('rate');
        }

        if (config('blog.features.favorites', true)) {
            Route::post('/{post}/favorite', [BlogController::class, 'toggleFavorite'])->name('favorite.toggle');
        }

        // Post CRUD operations
        Route::get('/create', [BlogController::class, 'create'])->name('create');
        Route::post('/', [BlogController::class, 'store'])->name('store');
        Route::get('/{post}/edit', [BlogController::class, 'edit'])->name('edit');
        Route::put('/{post}', [BlogController::class, 'update'])->name('update');
        Route::delete('/{post}', [BlogController::class, 'destroy'])->name('destroy');

        // Image upload for blog posts
        Route::post('/upload-image', [BlogController::class, 'uploadImage'])->name('upload-image');

        // Comments (if enabled)
        if (config('blog.features.comments', true)) {
            Route::post('/{post}/comments', [BlogController::class, 'storeComment'])->name('comment.store');
            Route::delete('/comments/{comment}', [BlogController::class, 'destroyComment'])->name('comment.destroy');
        }
    });
