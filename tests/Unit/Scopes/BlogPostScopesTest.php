<?php

use Cleargoal\Blog\Models\BlogCategory;
use Cleargoal\Blog\Models\BlogPost;
use Cleargoal\Blog\Models\PostTag;
use Cleargoal\Blog\Tests\TestCase;


it('filters published posts', function () {
    $user = $this->createUser();

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Published Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Draft Post'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Future Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now()->addDay(),
    ]);

    $publishedPosts = BlogPost::published()->get();

    expect($publishedPosts)->toHaveCount(1);
    expect($publishedPosts->first()->getTranslations('title'))->toBe(['en' => 'Published Post']);
});

it('filters draft posts', function () {
    $user = $this->createUser();

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Draft 1'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Draft 2'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Published'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $draftPosts = BlogPost::draft()->get();

    expect($draftPosts)->toHaveCount(2);
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

    $categoryPosts = BlogPost::byCategory($category->id)->get();

    expect($categoryPosts)->toHaveCount(1);
    expect($categoryPosts->first()->getTranslations('title'))->toBe(['en' => 'Laravel Post']);
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

    $taggedPosts = BlogPost::byTag($tag->id)->get();

    expect($taggedPosts)->toHaveCount(1);
    expect($taggedPosts->first()->getTranslations('title'))->toBe(['en' => 'PHP Post']);
});

it('searches posts by keyword in title and content', function () {
    $user = $this->createUser();

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Laravel Tutorial'],
        'content' => ['en' => 'Learn Laravel framework basics'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'PHP Guide'],
        'content' => ['en' => 'Introduction to PHP programming'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $searchResults = BlogPost::search('Laravel')->get();

    expect($searchResults)->toHaveCount(1);
    expect($searchResults->first()->getTranslations('title'))->toBe(['en' => 'Laravel Tutorial']);
});

it('filters posts by author', function () {
    $user1 = $this->createUser();
    $user2 = $this->createUser();

    BlogPost::create([
        'author_id' => $user1->id,
        'title' => ['en' => 'User 1 Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    BlogPost::create([
        'author_id' => $user2->id,
        'title' => ['en' => 'User 2 Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $user1Posts = BlogPost::byAuthor($user1->id)->get();

    expect($user1Posts)->toHaveCount(1);
    expect($user1Posts->first()->getTranslations('title'))->toBe(['en' => 'User 1 Post']);
});

it('excludes demo posts', function () {
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

    $realPosts = BlogPost::excludeDemo()->get();

    expect($realPosts)->toHaveCount(1);
    expect($realPosts->first()->getTranslations('title'))->toBe(['en' => 'Real Post']);
});

it('orders posts by latest published first', function () {
    $user = $this->createUser();

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Old Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now()->subWeek(),
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Recent Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Newest Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $posts = BlogPost::latest('published_at')->get();

    expect($posts->first()->getTranslations('title'))->toBe(['en' => 'Newest Post']);
    expect($posts->last()->getTranslations('title'))->toBe(['en' => 'Old Post']);
});

it('orders posts by most viewed', function () {
    $user = $this->createUser();

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Low Views'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
        'views_count' => 10,
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'High Views'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
        'views_count' => 1000,
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Medium Views'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
        'views_count' => 100,
    ]);

    $posts = BlogPost::orderBy('views_count', 'desc')->get();

    expect($posts->first()->getTranslations('title'))->toBe(['en' => 'High Views']);
    expect($posts->last()->getTranslations('title'))->toBe(['en' => 'Low Views']);
});

it('chains multiple scopes together', function () {
    $user = $this->createUser();
    $category = BlogCategory::create([
        'name' => ['en' => 'Laravel'],
        'slug' => 'laravel',
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'category_id' => $category->id,
        'title' => ['en' => 'Published Laravel Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
        'is_demo' => false,
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'category_id' => $category->id,
        'title' => ['en' => 'Draft Laravel Post'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
        'is_demo' => false,
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'category_id' => $category->id,
        'title' => ['en' => 'Demo Laravel Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
        'is_demo' => true,
    ]);

    $posts = BlogPost::published()
        ->byCategory($category->id)
        ->excludeDemo()
        ->get();

    expect($posts)->toHaveCount(1);
    expect($posts->first()->getTranslations('title'))->toBe(['en' => 'Published Laravel Post']);
});
