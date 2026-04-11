<?php

use YourVendor\Blog\Models\BlogCategory;
use YourVendor\Blog\Models\BlogPost;
use YourVendor\Blog\Tests\TestCase;


it('has posts relationship', function () {
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

    expect($category->posts)->toHaveCount(2);
});

it('supports hierarchical categories with parent-child relationships', function () {
    $parent = BlogCategory::create([
        'name' => ['en' => 'Web Development'],
        'slug' => 'web-development',
    ]);

    $child = BlogCategory::create([
        'name' => ['en' => 'Laravel'],
        'slug' => 'laravel',
        'parent_id' => $parent->id,
    ]);

    expect($child->parent->id)->toBe($parent->id);
    expect($parent->children)->toHaveCount(1);
    expect($parent->children->first()->id)->toBe($child->id);
});

it('can have multiple child categories', function () {
    $parent = BlogCategory::create([
        'name' => ['en' => 'Programming'],
        'slug' => 'programming',
    ]);

    BlogCategory::create([
        'name' => ['en' => 'PHP'],
        'slug' => 'php',
        'parent_id' => $parent->id,
    ]);

    BlogCategory::create([
        'name' => ['en' => 'JavaScript'],
        'slug' => 'javascript',
        'parent_id' => $parent->id,
    ]);

    BlogCategory::create([
        'name' => ['en' => 'Python'],
        'slug' => 'python',
        'parent_id' => $parent->id,
    ]);

    expect($parent->children)->toHaveCount(3);
});

it('auto-generates slug from name', function () {
    $category = BlogCategory::create([
        'name' => ['en' => 'My Test Category'],
    ]);

    expect($category->slug)->toBe('my-test-category');
});

it('ensures unique slugs', function () {
    BlogCategory::create([
        'name' => ['en' => 'Laravel'],
        'slug' => 'laravel',
    ]);

    $category2 = BlogCategory::create([
        'name' => ['en' => 'Laravel'],
    ]);

    expect($category2->slug)->not()->toBe('laravel');
    expect($category2->slug)->toContain('laravel');
});

it('supports multilingual names', function () {
    $category = BlogCategory::create([
        'name' => [
            'en' => 'Web Development',
            'uk' => 'Веб Розробка',
            'de' => 'Webentwicklung',
        ],
        'slug' => 'web-development',
    ]);

    expect($category->getTranslation('name', 'en'))->toBe('Web Development');
    expect($category->getTranslation('name', 'uk'))->toBe('Веб Розробка');
    expect($category->getTranslation('name', 'de'))->toBe('Webentwicklung');
});

it('supports multilingual descriptions', function () {
    $category = BlogCategory::create([
        'name' => ['en' => 'Laravel'],
        'slug' => 'laravel',
        'description' => [
            'en' => 'Laravel framework articles',
            'uk' => 'Статті про Laravel',
        ],
    ]);

    expect($category->getTranslation('description', 'en'))->toBe('Laravel framework articles');
    expect($category->getTranslation('description', 'uk'))->toBe('Статті про Laravel');
});

it('can be ordered by sort_order', function () {
    BlogCategory::create([
        'name' => ['en' => 'Category C'],
        'slug' => 'category-c',
        'sort_order' => 3,
    ]);

    BlogCategory::create([
        'name' => ['en' => 'Category A'],
        'slug' => 'category-a',
        'sort_order' => 1,
    ]);

    BlogCategory::create([
        'name' => ['en' => 'Category B'],
        'slug' => 'category-b',
        'sort_order' => 2,
    ]);

    $ordered = BlogCategory::orderBy('sort_order')->get();

    expect($ordered[0]->slug)->toBe('category-a');
    expect($ordered[1]->slug)->toBe('category-b');
    expect($ordered[2]->slug)->toBe('category-c');
});

it('nullifies category_id when category is deleted', function () {
    $user = $this->createUser();
    $category = BlogCategory::create([
        'name' => ['en' => 'Laravel'],
        'slug' => 'laravel',
    ]);

    $post = BlogPost::create([
        'author_id' => $user->id,
        'category_id' => $category->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    expect($post->category_id)->toBe($category->id);

    $category->delete();
    $post->refresh();

    expect($post->category_id)->toBeNull();
});

it('returns active categories only', function () {
    BlogCategory::create([
        'name' => ['en' => 'Active Category'],
        'slug' => 'active-category',
        'is_active' => true,
    ]);

    BlogCategory::create([
        'name' => ['en' => 'Inactive Category'],
        'slug' => 'inactive-category',
        'is_active' => false,
    ]);

    $activeCategories = BlogCategory::where('is_active', true)->get();

    expect($activeCategories)->toHaveCount(1);
    expect($activeCategories->first()->slug)->toBe('active-category');
});
