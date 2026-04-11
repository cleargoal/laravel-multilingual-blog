<?php

use Cleargoal\Blog\Actions\Blog\GetBlogAnalytics;
use Cleargoal\Blog\Models\BlogComment;
use Cleargoal\Blog\Models\BlogPost;
use Cleargoal\Blog\Models\BlogPostRating;

it('returns total posts count', function () {
    $user = $this->createUser();

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Post 1'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Post 2'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    $action = new GetBlogAnalytics;
    $result = $action->execute($user);

    expect($result['totalPosts'])->toBe(2);
    expect($result['publishedPosts'])->toBe(1);
    expect($result['draftPosts'])->toBe(1);
});

it('calculates total views', function () {
    $user = $this->createUser();

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Post 1'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
        'views_count' => 100,
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Post 2'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
        'views_count' => 250,
    ]);

    $action = new GetBlogAnalytics;
    $result = $action->execute($user);

    expect($result['totalViews'])->toBe(350);
});

it('counts comments by status', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Approved 1',
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Approved 2',
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Pending',
        'status' => 'pending',
    ]);

    $action = new GetBlogAnalytics;
    $result = $action->execute($user);

    expect($result['totalComments'])->toBe(3);
    expect($result['approvedComments'])->toBe(2);
    expect($result['pendingComments'])->toBe(1);
});

it('calculates average rating', function () {
    $user = $this->createUser();

    $post1 = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Post 1'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $post2 = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Post 2'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    BlogPostRating::create([
        'user_id' => $user->id,
        'blog_post_id' => $post1->id,
        'rating' => 5,
    ]);

    $user2 = $this->createUser();
    BlogPostRating::create([
        'user_id' => $user2->id,
        'blog_post_id' => $post2->id,
        'rating' => 3,
    ]);

    $action = new GetBlogAnalytics;
    $result = $action->execute($user);

    expect($result['averageRating'])->toBe(4.0);
    expect($result['totalRatings'])->toBe(2);
});

it('returns most viewed posts', function () {
    $user = $this->createUser();

    $post1 = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Popular Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
        'views_count' => 1000,
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Less Popular'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
        'views_count' => 50,
    ]);

    $action = new GetBlogAnalytics;
    $result = $action->execute($user);

    expect($result['mostViewedPosts'])->toHaveCount(2);
    expect($result['mostViewedPosts']->first()->id)->toBe($post1->id);
});

it('returns recent posts', function () {
    $user = $this->createUser();

    $recentPost = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Recent Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Old Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now()->subMonths(6),
    ]);

    $action = new GetBlogAnalytics;
    $result = $action->execute($user);

    expect($result['recentPosts'])->toHaveCount(2);
    expect($result['recentPosts']->first()->id)->toBe($recentPost->id);
});

it('excludes demo posts from analytics', function () {
    $user = $this->createUser();

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Real Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
        'is_demo' => false,
        'views_count' => 100,
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Demo Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
        'is_demo' => true,
        'views_count' => 500,
    ]);

    $action = new GetBlogAnalytics;
    $result = $action->execute($user);

    expect($result['totalPosts'])->toBe(1);
    expect($result['totalViews'])->toBe(100);
});
