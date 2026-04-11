<?php

use Illuminate\Support\Facades\Schema;
use YourVendor\Blog\Tests\TestCase;


it('creates all blog tables', function () {
    expect(Schema::hasTable('blog_categories'))->toBeTrue();
    expect(Schema::hasTable('blog_posts'))->toBeTrue();
    expect(Schema::hasTable('post_tags'))->toBeTrue();
    expect(Schema::hasTable('post_tag'))->toBeTrue();
    expect(Schema::hasTable('blog_comments'))->toBeTrue();
    expect(Schema::hasTable('blog_post_ratings'))->toBeTrue();
    expect(Schema::hasTable('blog_post_favorites'))->toBeTrue();
    expect(Schema::hasTable('blog_rss_imports'))->toBeTrue();
});

it('blog_posts table has all required columns', function () {
    $columns = Schema::getColumnListing('blog_posts');

    expect($columns)->toContain('id');
    expect($columns)->toContain('author_id');
    expect($columns)->toContain('category_id');
    expect($columns)->toContain('slug');
    expect($columns)->toContain('title');
    expect($columns)->toContain('content');
    expect($columns)->toContain('excerpt');
    expect($columns)->toContain('status');
    expect($columns)->toContain('original_locale');
    expect($columns)->toContain('published_at');
    expect($columns)->toContain('views_count');
    expect($columns)->toContain('rating_average');
    expect($columns)->toContain('rating_count');
    expect($columns)->toContain('is_featured');
    expect($columns)->toContain('is_external');
    expect($columns)->toContain('external_source_name');
    expect($columns)->toContain('external_source_url');
    expect($columns)->toContain('generated_by_ai');
    expect($columns)->toContain('ai_model_used');
    expect($columns)->toContain('generation_prompt_version');
    expect($columns)->toContain('is_demo');
    expect($columns)->toContain('deleted_at'); // soft deletes
    expect($columns)->toContain('created_at');
    expect($columns)->toContain('updated_at');
});

it('blog_categories table has all required columns', function () {
    $columns = Schema::getColumnListing('blog_categories');

    expect($columns)->toContain('id');
    expect($columns)->toContain('parent_id');
    expect($columns)->toContain('slug');
    expect($columns)->toContain('name');
    expect($columns)->toContain('description');
    expect($columns)->toContain('sort_order');
    expect($columns)->toContain('is_active');
    expect($columns)->toContain('created_at');
    expect($columns)->toContain('updated_at');
});

it('post_tags table has all required columns', function () {
    $columns = Schema::getColumnListing('post_tags');

    expect($columns)->toContain('id');
    expect($columns)->toContain('name');
    expect($columns)->toContain('slug');
    expect($columns)->toContain('usage_count');
    expect($columns)->toContain('created_at');
    expect($columns)->toContain('updated_at');
});

it('blog_comments table has all required columns', function () {
    $columns = Schema::getColumnListing('blog_comments');

    expect($columns)->toContain('id');
    expect($columns)->toContain('blog_post_id');
    expect($columns)->toContain('author_id');
    expect($columns)->toContain('parent_id');
    expect($columns)->toContain('content');
    expect($columns)->toContain('status');
    expect($columns)->toContain('approved_at');
    expect($columns)->toContain('deleted_at'); // soft deletes
    expect($columns)->toContain('created_at');
    expect($columns)->toContain('updated_at');
});

it('blog_post_ratings table has unique constraint', function () {
    $user = $this->createUser();
    $post = \YourVendor\Blog\Models\BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    // First rating should succeed
    \YourVendor\Blog\Models\BlogPostRating::create([
        'user_id' => $user->id,
        'blog_post_id' => $post->id,
        'rating' => 5,
    ]);

    // Second rating by same user should fail due to unique constraint
    expect(fn () => \YourVendor\Blog\Models\BlogPostRating::create([
        'user_id' => $user->id,
        'blog_post_id' => $post->id,
        'rating' => 4,
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});
