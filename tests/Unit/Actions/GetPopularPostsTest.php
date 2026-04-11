<?php

use Cleargoal\Blog\Actions\Blog\GetPopularPosts;
use Cleargoal\Blog\Models\BlogPost;
use Cleargoal\Blog\Tests\TestCase;


it('returns popular posts ordered by views', function () {
    $user = $this->createUser();

    // Create posts with different view counts
    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Post 1'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now()->subDays(2),
        'views_count' => 100,
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Post 2'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now()->subDays(3),
        'views_count' => 500,
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Post 3'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now()->subDays(1),
        'views_count' => 250,
    ]);

    $action = new GetPopularPosts();
    $result = $action->execute(limit: 3, period: 'alltime');

    expect($result)->toHaveCount(3);
    expect($result[0]['title'])->toBe('Post 2');
    expect($result[0]['views_count'])->toBe(500);
    expect($result[1]['title'])->toBe('Post 3');
    expect($result[2]['title'])->toBe('Post 1');
});

it('filters posts by time period for 7days', function () {
    $user = $this->createUser();

    // Old post (outside 7 days)
    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Old Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now()->subDays(10),
        'views_count' => 1000,
    ]);

    // Recent post (within 7 days)
    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Recent Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now()->subDays(3),
        'views_count' => 100,
    ]);

    $action = new GetPopularPosts();
    $result = $action->execute(limit: 10, period: '7days');

    expect($result)->toHaveCount(1);
    expect($result[0]['title'])->toBe('Recent Post');
});

it('excludes demo posts', function () {
    $user = $this->createUser();

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Real Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
        'views_count' => 100,
        'is_demo' => false,
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Demo Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
        'views_count' => 500,
        'is_demo' => true,
    ]);

    $action = new GetPopularPosts();
    $result = $action->execute(limit: 10, period: 'alltime');

    expect($result)->toHaveCount(1);
    expect($result[0]['title'])->toBe('Real Post');
});
