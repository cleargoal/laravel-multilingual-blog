<?php

use Cleargoal\Blog\Events\BlogCommentApproved;
use Cleargoal\Blog\Events\BlogCommentPosted;
use Cleargoal\Blog\Events\BlogPostCreated;
use Cleargoal\Blog\Events\BlogPostDeleted;
use Cleargoal\Blog\Events\BlogPostPublished;
use Cleargoal\Blog\Events\BlogPostUpdated;
use Cleargoal\Blog\Models\BlogComment;
use Cleargoal\Blog\Models\BlogPost;
use Illuminate\Support\Facades\Event;

it('dispatches BlogPostCreated event when post is created', function () {
    Event::fake([BlogPostCreated::class]);

    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'New Post'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    Event::assertDispatched(BlogPostCreated::class, function ($event) use ($post) {
        return $event->post->id === $post->id;
    });
});

it('dispatches BlogPostUpdated event when post is updated', function () {
    Event::fake([BlogPostUpdated::class]);

    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Original Title'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    Event::assertNotDispatched(BlogPostUpdated::class);

    $post->update(['title' => ['en' => 'Updated Title']]);

    Event::assertDispatched(BlogPostUpdated::class, function ($event) use ($post) {
        return $event->post->id === $post->id;
    });
});

it('dispatches BlogPostPublished event when post status changes to published', function () {
    Event::fake([BlogPostPublished::class]);

    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'New Post'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    Event::assertNotDispatched(BlogPostPublished::class);

    $post->update([
        'status' => 'published',
        'published_at' => now(),
    ]);

    Event::assertDispatched(BlogPostPublished::class, function ($event) use ($post) {
        return $event->post->id === $post->id;
    });
});

it('dispatches BlogPostDeleted event when post is deleted', function () {
    Event::fake([BlogPostDeleted::class]);

    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    $post->delete();

    Event::assertDispatched(BlogPostDeleted::class, function ($event) use ($post) {
        return $event->post->id === $post->id;
    });
});

it('dispatches BlogCommentPosted event when comment is created', function () {
    Event::fake([BlogCommentPosted::class]);

    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $comment = BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Great post!',
        'status' => 'pending',
    ]);

    Event::assertDispatched(BlogCommentPosted::class, function ($event) use ($comment) {
        return $event->comment->id === $comment->id;
    });
});

it('dispatches BlogCommentApproved event when comment status changes to approved', function () {
    Event::fake([BlogCommentApproved::class]);

    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $comment = BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Great post!',
        'status' => 'pending',
    ]);

    Event::assertNotDispatched(BlogCommentApproved::class);

    $comment->update([
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    Event::assertDispatched(BlogCommentApproved::class, function ($event) use ($comment) {
        return $event->comment->id === $comment->id;
    });
});
