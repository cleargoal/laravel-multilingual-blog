<?php

use Cleargoal\Blog\Models\BlogCategory;
use Cleargoal\Blog\Models\BlogComment;
use Cleargoal\Blog\Models\BlogPost;
use Cleargoal\Blog\Models\BlogPostRating;
use Cleargoal\Blog\Models\PostTag;

it('belongs to an author', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Test Content'],
        'status' => 'draft',
    ]);

    expect($post->author)->toBeInstanceOf(get_class($user));
    expect($post->author->id)->toBe($user->id);
});

it('belongs to a category', function () {
    $user = $this->createUser();
    $category = BlogCategory::create([
        'name' => ['en' => 'Tech'],
        'slug' => 'tech',
    ]);

    $post = BlogPost::create([
        'author_id' => $user->id,
        'category_id' => $category->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Test Content'],
        'status' => 'draft',
    ]);

    expect($post->category)->toBeInstanceOf(BlogCategory::class);
    expect($post->category->id)->toBe($category->id);
});

it('has many comments', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Test Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Great post!',
        'status' => 'approved',
    ]);

    BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Another comment',
        'status' => 'pending',
    ]);

    expect($post->comments)->toHaveCount(2);
    expect($post->comments->first())->toBeInstanceOf(BlogComment::class);
});

it('has many tags through pivot', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Test Content'],
        'status' => 'draft',
    ]);

    $tag1 = PostTag::create(['name' => ['en' => 'Laravel'], 'slug' => 'laravel']);
    $tag2 = PostTag::create(['name' => ['en' => 'PHP'], 'slug' => 'php']);

    $post->tags()->attach([$tag1->id, $tag2->id]);

    expect($post->tags)->toHaveCount(2);
    expect($post->tags->pluck('slug')->toArray())->toContain('laravel', 'php');
});

it('has many ratings', function () {
    $user = $this->createUser();
    $user2 = $this->createUser(['email' => 'user2@example.com']);

    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Test Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    BlogPostRating::create([
        'user_id' => $user->id,
        'blog_post_id' => $post->id,
        'rating' => 5,
    ]);

    BlogPostRating::create([
        'user_id' => $user2->id,
        'blog_post_id' => $post->id,
        'rating' => 4,
    ]);

    expect($post->ratings)->toHaveCount(2);
    expect($post->averageRating())->toBe(4.5);
    expect($post->ratingsCount())->toBe(2);
});

it('can be favorited by users', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Test Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $post->favoritedBy()->attach($user->id);

    expect($post->favoritedBy)->toHaveCount(1);
    expect($post->isFavoritedByUser($user->id))->toBeTrue();
    expect($post->favoritesCount())->toBe(1);
});

it('checks if post is published', function () {
    $user = $this->createUser();

    $draftPost = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Draft Post'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    $publishedPost = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Published Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    expect($draftPost->isPublished())->toBeFalse();
    expect($draftPost->isDraft())->toBeTrue();
    expect($publishedPost->isPublished())->toBeTrue();
    expect($publishedPost->isDraft())->toBeFalse();
});

it('checks if post is translated', function () {
    $user = $this->createUser();

    $untranslatedPost = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'English Only'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    $translatedPost = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'English', 'uk' => 'Українська'],
        'content' => ['en' => 'Content', 'uk' => 'Контент'],
        'status' => 'draft',
    ]);

    expect($untranslatedPost->isTranslated())->toBeFalse();
    expect($translatedPost->isTranslated())->toBeTrue();
});

it('uses published scope correctly', function () {
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

    expect(BlogPost::published()->count())->toBe(1);
});

it('uses featured scope correctly', function () {
    $user = $this->createUser();

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Regular Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
        'is_featured' => false,
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Featured Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
        'is_featured' => true,
    ]);

    expect(BlogPost::featured()->count())->toBe(1);
});
