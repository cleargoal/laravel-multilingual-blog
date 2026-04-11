<?php

use Cleargoal\Blog\Models\BlogPost;
use Cleargoal\Blog\Models\BlogPostRating;
use Cleargoal\Blog\Tests\TestCase;


it('authenticated user can post a comment', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'slug' => 'test-post',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $response = $this->actingAs($user)->post(route('blog.comment.store', ['post' => $post]), [
        'blog_post_id' => $post->id,
        'content' => 'Great post!',
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('blog_comments', [
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Great post!',
        'status' => 'pending',
    ]);
});

it('guest cannot post a comment', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'slug' => 'test-post',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $response = $this->post(route('blog.comment.store', ['post' => $post]), [
        'blog_post_id' => $post->id,
        'content' => 'Great post!',
    ]);

    // Guest should be denied - verify no comment was created
    $this->assertDatabaseCount('blog_comments', 0);
});

it('authenticated user can rate a post', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'slug' => 'test-post',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $response = $this->actingAs($user)->post(route('blog.rate', ['post' => $post]), [
        'blog_post_id' => $post->id,
        'rating' => 5,
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('blog_post_ratings', [
        'blog_post_id' => $post->id,
        'user_id' => $user->id,
        'rating' => 5,
    ]);
});

it('user can update their rating', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'slug' => 'test-post',
        'status' => 'published',
        'published_at' => now(),
    ]);

    BlogPostRating::create([
        'user_id' => $user->id,
        'blog_post_id' => $post->id,
        'rating' => 3,
    ]);

    $response = $this->actingAs($user)->post(route('blog.rate', ['post' => $post]), [
        'blog_post_id' => $post->id,
        'rating' => 5,
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('blog_post_ratings', [
        'blog_post_id' => $post->id,
        'user_id' => $user->id,
        'rating' => 5,
    ]);

    expect(BlogPostRating::where('blog_post_id', $post->id)->count())->toBe(1);
});

it('rating must be between 1 and 5', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'slug' => 'test-post',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $response = $this->actingAs($user)->post(route('blog.rate', ['post' => $post]), [
        'blog_post_id' => $post->id,
        'rating' => 6,
    ]);

    $response->assertSessionHasErrors('rating');
});

it('authenticated user can favorite a post', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'slug' => 'test-post',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $response = $this->actingAs($user)->post(route('blog.favorite.toggle', ['post' => $post]), [
        'blog_post_id' => $post->id,
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('blog_post_favorites', [
        'blog_post_id' => $post->id,
        'user_id' => $user->id,
    ]);
});

it('user can unfavorite a post', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'slug' => 'test-post',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $user->favoriteBlogPosts()->attach($post->id);

    $response = $this->actingAs($user)->post(route('blog.favorite.toggle', ['post' => $post]), [
        'blog_post_id' => $post->id,
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseMissing('blog_post_favorites', [
        'blog_post_id' => $post->id,
        'user_id' => $user->id,
    ]);
});

it('user can view their favorite posts', function () {
    $user = $this->createUser();

    $post1 = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Favorite Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $post2 = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Other Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $user->favoriteBlogPosts()->attach($post1->id);

    $response = $this->actingAs($user)->get(route('blog.favorites'));

    $response->assertStatus(200);
    $response->assertSee('Favorite Post');
    $response->assertDontSee('Other Post');
});

it('user can view their blog posts', function () {
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

    $response = $this->actingAs($user1)->get(route('blog.my-posts'));

    $response->assertStatus(200);
    $response->assertSee('User 1 Post');
    $response->assertDontSee('User 2 Post');
});

it('comment requires content', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'slug' => 'test-post',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $response = $this->actingAs($user)->post(route('blog.comment.store', ['post' => $post]), [
        'blog_post_id' => $post->id,
        'content' => '',
    ]);

    $response->assertSessionHasErrors('content');
});

it('user can reply to a comment', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'slug' => 'test-post',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $parentComment = \Cleargoal\Blog\Models\BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Parent comment',
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    $response = $this->actingAs($user)->post(route('blog.comment.store', ['post' => $post]), [
        'blog_post_id' => $post->id,
        'parent_id' => $parentComment->id,
        'content' => 'Reply to parent',
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('blog_comments', [
        'blog_post_id' => $post->id,
        'parent_id' => $parentComment->id,
        'content' => 'Reply to parent',
    ]);
});
