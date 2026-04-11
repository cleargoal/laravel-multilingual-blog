<?php

use Cleargoal\Blog\Models\BlogComment;
use Cleargoal\Blog\Models\BlogPost;
use Cleargoal\Blog\Models\BlogPostRating;
use Cleargoal\Blog\Tests\TestCase;


it('user has blogPosts relationship', function () {
    $user = $this->createUser();

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Post 1'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Post 2'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    expect($user->blogPosts)->toHaveCount(2);
});

it('user has publishedBlogPosts relationship', function () {
    $user = $this->createUser();

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Draft'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Published'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    expect($user->publishedBlogPosts)->toHaveCount(1);
    expect($user->publishedBlogPosts->first()->getTranslations('title'))->toBe(['en' => 'Published']);
});

it('user has blogComments relationship', function () {
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
        'content' => 'Comment 1',
        'status' => 'approved',
    ]);

    BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Comment 2',
        'status' => 'pending',
    ]);

    expect($user->blogComments)->toHaveCount(2);
});

it('user has favoriteBlogPosts relationship', function () {
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

    $user->favoriteBlogPosts()->attach([$post1->id, $post2->id]);

    expect($user->favoriteBlogPosts)->toHaveCount(2);
});

it('checks if user has favorited a post', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    expect($user->hasFavoritedBlogPost($post->id))->toBeFalse();

    $user->favoriteBlogPosts()->attach($post->id);

    expect($user->hasFavoritedBlogPost($post->id))->toBeTrue();
});

it('gets user rating for a post', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    expect($user->getBlogPostRating($post->id))->toBeNull();

    BlogPostRating::create([
        'user_id' => $user->id,
        'blog_post_id' => $post->id,
        'rating' => 5,
    ]);

    expect($user->getBlogPostRating($post->id))->toBe(5);
});
