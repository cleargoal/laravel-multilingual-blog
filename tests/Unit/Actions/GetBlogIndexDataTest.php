<?php

use YourVendor\Blog\Actions\Blog\GetBlogIndexData;
use YourVendor\Blog\Models\BlogCategory;
use YourVendor\Blog\Models\BlogPost;
use YourVendor\Blog\Models\PostTag;
use YourVendor\Blog\Tests\TestCase;


it('returns paginated published posts', function () {
    $user = $this->createUser();

    // Create published posts
    for ($i = 0; $i < 15; $i++) {
        BlogPost::create([
            'author_id' => $user->id,
            'title' => ['en' => "Post {$i}"],
            'content' => ['en' => 'Content'],
            'status' => 'published',
            'published_at' => now()->subDays($i),
        ]);
    }

    // Create draft post (should not be included)
    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Draft Post'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    $action = new GetBlogIndexData();
    $result = $action->execute();

    expect($result['posts'])->toHaveCount(config('blog.posts_per_page', 10));
    expect($result['posts']->total())->toBe(15);
});

it('filters posts by category', function () {
    $user = $this->createUser();
    $category = BlogCategory::create([
        'name' => ['en' => 'Laravel'],
        'slug' => 'laravel',
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'category_id' => $category->id,
        'title' => ['en' => 'Laravel Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Other Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $action = new GetBlogIndexData();
    $result = $action->execute(categorySlug: 'laravel');

    expect($result['posts'])->toHaveCount(1);
    expect($result['posts']->first()->getTranslations('title'))->toBe(['en' => 'Laravel Post']);
});

it('filters posts by tag', function () {
    $user = $this->createUser();
    $tag = PostTag::create([
        'name' => ['en' => 'PHP'],
        'slug' => 'php',
    ]);

    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'PHP Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);
    $post->tags()->attach($tag->id);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Other Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $action = new GetBlogIndexData();
    $result = $action->execute(tagSlug: 'php');

    expect($result['posts'])->toHaveCount(1);
    expect($result['posts']->first()->getTranslations('title'))->toBe(['en' => 'PHP Post']);
});

it('searches posts by keyword', function () {
    $user = $this->createUser();

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Laravel Tutorial'],
        'content' => ['en' => 'Learn Laravel framework'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'PHP Basics'],
        'content' => ['en' => 'Content about PHP'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $action = new GetBlogIndexData();
    $result = $action->execute(search: 'Laravel');

    expect($result['posts'])->toHaveCount(1);
    expect($result['posts']->first()->getTranslations('title'))->toBe(['en' => 'Laravel Tutorial']);
});

it('returns popular tags', function () {
    PostTag::create([
        'name' => ['en' => 'Popular'],
        'slug' => 'popular',
        'usage_count' => 50,
    ]);

    PostTag::create([
        'name' => ['en' => 'Unpopular'],
        'slug' => 'unpopular',
        'usage_count' => 1,
    ]);

    $action = new GetBlogIndexData();
    $result = $action->execute();

    expect($result['popularTags'])->toHaveCount(2);
    expect($result['popularTags']->first()->slug)->toBe('popular');
});

it('returns categories with post counts', function () {
    $user = $this->createUser();
    $category = BlogCategory::create([
        'name' => ['en' => 'Laravel'],
        'slug' => 'laravel',
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'category_id' => $category->id,
        'title' => ['en' => 'Post 1'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'category_id' => $category->id,
        'title' => ['en' => 'Post 2'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $action = new GetBlogIndexData();
    $result = $action->execute();

    expect($result['categories'])->toHaveCount(1);
    expect($result['categories']->first()->posts_count)->toBe(2);
});

it('excludes demo posts from results', function () {
    $user = $this->createUser();

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Real Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
        'is_demo' => false,
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Demo Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
        'is_demo' => true,
    ]);

    $action = new GetBlogIndexData();
    $result = $action->execute();

    expect($result['posts'])->toHaveCount(1);
    expect($result['posts']->first()->getTranslations('title'))->toBe(['en' => 'Real Post']);
});
