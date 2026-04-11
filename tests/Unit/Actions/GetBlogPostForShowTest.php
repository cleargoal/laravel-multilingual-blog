<?php

use YourVendor\Blog\Actions\Blog\GetBlogPostForShow;
use YourVendor\Blog\Models\BlogComment;
use YourVendor\Blog\Models\BlogPost;
use YourVendor\Blog\Models\PostTag;
use YourVendor\Blog\Tests\TestCase;


it('retrieves post by slug with all relationships', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'slug' => 'test-post',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $tag = PostTag::create([
        'name' => ['en' => 'Laravel'],
        'slug' => 'laravel',
    ]);
    $post->tags()->attach($tag->id);

    $action = new GetBlogPostForShow();
    $result = $action->execute('test-post');

    expect($result['post']->id)->toBe($post->id);
    expect($result['post']->relationLoaded('author'))->toBeTrue();
    expect($result['post']->relationLoaded('category'))->toBeTrue();
    expect($result['post']->relationLoaded('tags'))->toBeTrue();
});

it('increments view count when viewing post', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'slug' => 'test-post',
        'status' => 'published',
        'published_at' => now(),
        'views_count' => 10,
    ]);

    $action = new GetBlogPostForShow();
    $action->execute('test-post');

    $post->refresh();
    expect($post->views_count)->toBe(11);
});

it('returns approved comments only', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'slug' => 'test-post',
        'status' => 'published',
        'published_at' => now(),
    ]);

    BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Approved comment',
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Pending comment',
        'status' => 'pending',
    ]);

    BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Rejected comment',
        'status' => 'rejected',
    ]);

    $action = new GetBlogPostForShow();
    $result = $action->execute('test-post');

    expect($result['comments'])->toHaveCount(1);
    expect($result['comments']->first()->content)->toBe('Approved comment');
});

it('returns related posts from same category', function () {
    $user = $this->createUser();
    $category = \YourVendor\Blog\Models\BlogCategory::create([
        'name' => ['en' => 'Laravel'],
        'slug' => 'laravel',
    ]);

    $mainPost = BlogPost::create([
        'author_id' => $user->id,
        'category_id' => $category->id,
        'title' => ['en' => 'Main Post'],
        'content' => ['en' => 'Content'],
        'slug' => 'main-post',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $relatedPost = BlogPost::create([
        'author_id' => $user->id,
        'category_id' => $category->id,
        'title' => ['en' => 'Related Post'],
        'content' => ['en' => 'Content'],
        'slug' => 'related-post',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $action = new GetBlogPostForShow();
    $result = $action->execute('main-post');

    expect($result['relatedPosts'])->toHaveCount(1);
    expect($result['relatedPosts']->first()->id)->toBe($relatedPost->id);
});

it('calculates average rating', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'slug' => 'test-post',
        'status' => 'published',
        'published_at' => now(),
    ]);

    \YourVendor\Blog\Models\BlogPostRating::create([
        'user_id' => $user->id,
        'blog_post_id' => $post->id,
        'rating' => 5,
    ]);

    $user2 = $this->createUser();
    \YourVendor\Blog\Models\BlogPostRating::create([
        'user_id' => $user2->id,
        'blog_post_id' => $post->id,
        'rating' => 3,
    ]);

    $action = new GetBlogPostForShow();
    $result = $action->execute('test-post');

    expect($result['averageRating'])->toBe(4.0);
    expect($result['ratingsCount'])->toBe(2);
});

it('returns null for non-existent post', function () {
    $action = new GetBlogPostForShow();
    $result = $action->execute('non-existent-slug');

    expect($result)->toBeNull();
});

it('does not return draft posts', function () {
    $user = $this->createUser();
    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Draft Post'],
        'content' => ['en' => 'Content'],
        'slug' => 'draft-post',
        'status' => 'draft',
    ]);

    $action = new GetBlogPostForShow();
    $result = $action->execute('draft-post');

    expect($result)->toBeNull();
});
