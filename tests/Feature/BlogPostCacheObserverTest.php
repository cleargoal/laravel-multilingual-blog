<?php

use Cleargoal\Blog\Models\BlogPost;
use Cleargoal\Blog\Observers\BlogPostCacheObserver;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // Enable cache for these tests
    config()->set('blog.cache.enabled', true);
    config()->set('blog.cache.prefix', 'blog');

    // Register observer
    BlogPost::observe(BlogPostCacheObserver::class);
});

it('clears popular posts cache when post is saved', function () {
    $user = $this->createUser();

    // Set some cache
    Cache::put('blog.popular_posts.7days.5', ['test'], 60);
    Cache::put('blog.popular_posts.30days.10', ['test'], 60);
    Cache::put('blog.popular_posts.alltime.3', ['test'], 60);

    expect(Cache::has('blog.popular_posts.7days.5'))->toBeTrue();

    // Create a post (triggers observer)
    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'New Post'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    // Cache should be cleared
    expect(Cache::has('blog.popular_posts.7days.5'))->toBeFalse();
    expect(Cache::has('blog.popular_posts.30days.10'))->toBeFalse();
    expect(Cache::has('blog.popular_posts.alltime.3'))->toBeFalse();
});

it('clears popular posts cache when post is deleted', function () {
    $user = $this->createUser();

    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    // Set cache
    Cache::put('blog.popular_posts.7days.5', ['test'], 60);

    expect(Cache::has('blog.popular_posts.7days.5'))->toBeTrue();

    // Delete post
    $post->delete();

    // Cache should be cleared
    expect(Cache::has('blog.popular_posts.7days.5'))->toBeFalse();
});

it('clears popular tags cache on post save', function () {
    $user = $this->createUser();

    Cache::put('blog.popular_tags', ['test'], 60);

    expect(Cache::has('blog.popular_tags'))->toBeTrue();

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'New Post'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    expect(Cache::has('blog.popular_tags'))->toBeFalse();
});
