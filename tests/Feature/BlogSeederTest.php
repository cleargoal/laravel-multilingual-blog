<?php

use Cleargoal\Blog\Database\Seeders\BlogSeeder;
use Cleargoal\Blog\Models\BlogCategory;
use Cleargoal\Blog\Models\BlogComment;
use Cleargoal\Blog\Models\BlogPost;
use Cleargoal\Blog\Models\PostTag;
use Cleargoal\Blog\Tests\TestCase;


it('creates sample categories', function () {
    $this->createUser(); // Ensure at least one user exists

    $seeder = new BlogSeeder();
    $seeder->run();

    expect(BlogCategory::count())->toBeGreaterThan(0);
});

it('creates sample blog posts', function () {
    $this->createUser();

    $seeder = new BlogSeeder();
    $seeder->run();

    expect(BlogPost::count())->toBeGreaterThan(0);
});

it('creates sample tags', function () {
    $this->createUser();

    $seeder = new BlogSeeder();
    $seeder->run();

    expect(PostTag::count())->toBeGreaterThan(0);
});

it('creates sample comments', function () {
    $this->createUser();

    $seeder = new BlogSeeder();
    $seeder->run();

    expect(BlogComment::count())->toBeGreaterThan(0);
});

it('assigns categories to posts', function () {
    $this->createUser();

    $seeder = new BlogSeeder();
    $seeder->run();

    $postsWithCategories = BlogPost::whereNotNull('category_id')->count();
    expect($postsWithCategories)->toBeGreaterThan(0);
});

it('assigns tags to posts', function () {
    $this->createUser();

    $seeder = new BlogSeeder();
    $seeder->run();

    $post = BlogPost::first();
    expect($post->tags->count())->toBeGreaterThan(0);
});

it('creates posts in multiple statuses', function () {
    $this->createUser();

    $seeder = new BlogSeeder();
    $seeder->run();

    $publishedPosts = BlogPost::where('status', 'published')->count();
    $draftPosts = BlogPost::where('status', 'draft')->count();

    expect($publishedPosts)->toBeGreaterThan(0);
    expect($draftPosts)->toBeGreaterThan(0);
});

it('creates multilingual content', function () {
    $this->createUser();

    $seeder = new BlogSeeder();
    $seeder->run();

    $post = BlogPost::first();
    $translations = $post->getTranslations('title');

    expect(count($translations))->toBeGreaterThan(1);
});

it('sets published_at for published posts', function () {
    $this->createUser();

    $seeder = new BlogSeeder();
    $seeder->run();

    $publishedPosts = BlogPost::where('status', 'published')->get();

    foreach ($publishedPosts as $post) {
        expect($post->published_at)->not()->toBeNull();
    }
});

it('creates hierarchical categories', function () {
    $this->createUser();

    $seeder = new BlogSeeder();
    $seeder->run();

    $categoriesWithParent = BlogCategory::whereNotNull('parent_id')->count();
    expect($categoriesWithParent)->toBeGreaterThan(0);
});

it('creates threaded comments', function () {
    $this->createUser();

    $seeder = new BlogSeeder();
    $seeder->run();

    $repliesCount = BlogComment::whereNotNull('parent_id')->count();
    expect($repliesCount)->toBeGreaterThan(0);
});

it('marks some posts as demo posts', function () {
    $this->createUser();

    $seeder = new BlogSeeder();
    $seeder->run();

    $demoPosts = BlogPost::where('is_demo', true)->count();
    expect($demoPosts)->toBeGreaterThan(0);
});
