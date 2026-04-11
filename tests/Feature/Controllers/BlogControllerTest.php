<?php

use Cleargoal\Blog\Models\BlogCategory;
use Cleargoal\Blog\Models\BlogComment;
use Cleargoal\Blog\Models\BlogPost;
use Cleargoal\Blog\Models\PostTag;
use Cleargoal\Blog\Tests\TestCase;


it('displays blog index page', function () {
    $user = $this->createUser();

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $response = $this->get(route('blog.index'));

    $response->assertStatus(200);
    $response->assertViewIs('blog::index');
    $response->assertViewHas('posts');
});

it('displays only published posts on index', function () {
    $user = $this->createUser();

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Published Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Draft Post'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    $response = $this->get(route('blog.index'));

    $response->assertStatus(200);
    $response->assertSee('Published Post');
    $response->assertDontSee('Draft Post');
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

    $response = $this->get(route('blog.category', ['category' => 'laravel']));

    $response->assertStatus(200);
    $response->assertSee('Laravel Post');
    $response->assertDontSee('Other Post');
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

    $response = $this->get(route('blog.tag', ['slug' => 'php']));

    $response->assertStatus(200);
    $response->assertSee('PHP Post');
    $response->assertDontSee('Other Post');
});

it('searches posts by keyword', function () {
    $user = $this->createUser();

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Laravel Tutorial'],
        'content' => ['en' => 'Content about Laravel'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'PHP Guide'],
        'content' => ['en' => 'Content about PHP'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $response = $this->get(route('blog.index', ['search' => 'Laravel']));

    $response->assertStatus(200);
    $response->assertSee('Laravel Tutorial');
    $response->assertDontSee('PHP Guide');
});

it('displays single blog post', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Post content here'],
        'slug' => 'test-post',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $response = $this->get(route('blog.show', ['slug' => 'test-post']));

    $response->assertStatus(200);
    $response->assertViewIs('blog::show');
    $response->assertSee('Test Post');
    $response->assertSee('Post content here');
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
        'views_count' => 5,
    ]);

    $this->get(route('blog.show', ['slug' => 'test-post']));

    $post->refresh();
    expect($post->views_count)->toBe(6);
});

it('returns 404 for non-existent post', function () {
    $response = $this->get(route('blog.show', ['slug' => 'non-existent']));

    $response->assertStatus(404);
});

it('returns 404 for draft post', function () {
    $user = $this->createUser();
    BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Draft Post'],
        'content' => ['en' => 'Content'],
        'slug' => 'draft-post',
        'status' => 'draft',
    ]);

    $response = $this->get(route('blog.show', ['slug' => 'draft-post']));

    $response->assertStatus(404);
});

it('displays approved comments on post page', function () {
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

    $response = $this->get(route('blog.show', ['slug' => 'test-post']));

    $response->assertStatus(200);
    $response->assertSee('Approved comment');
    $response->assertDontSee('Pending comment');
});

it('paginates posts on index page', function () {
    $user = $this->createUser();

    for ($i = 0; $i < 25; $i++) {
        BlogPost::create([
            'author_id' => $user->id,
            'title' => ['en' => "Post {$i}"],
            'content' => ['en' => 'Content'],
            'status' => 'published',
            'published_at' => now()->subDays($i),
        ]);
    }

    $response = $this->get(route('blog.index'));

    $response->assertStatus(200);
    $response->assertViewHas('posts', function ($posts) {
        return $posts->total() === 25 && $posts->perPage() === config('blog.posts_per_page', 10);
    });
});
