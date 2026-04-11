<?php

use Cleargoal\Blog\Models\BlogComment;
use Cleargoal\Blog\Models\BlogPost;
use Cleargoal\Blog\Tests\TestCase;


it('belongs to a blog post', function () {
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

    expect($comment->post)->toBeInstanceOf(BlogPost::class);
    expect($comment->post->id)->toBe($post->id);
});

it('belongs to an author', function () {
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

    expect($comment->author->id)->toBe($user->id);
});

it('supports threaded comments with parent-child relationships', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $parentComment = BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Parent comment',
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    $childComment = BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'parent_id' => $parentComment->id,
        'content' => 'Reply to parent',
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    expect($childComment->parent->id)->toBe($parentComment->id);
    expect($parentComment->replies)->toHaveCount(1);
    expect($parentComment->replies->first()->id)->toBe($childComment->id);
});

it('can have multiple replies', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $parentComment = BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Parent comment',
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    for ($i = 0; $i < 5; $i++) {
        BlogComment::create([
            'blog_post_id' => $post->id,
            'author_id' => $user->id,
            'parent_id' => $parentComment->id,
            'content' => "Reply {$i}",
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    expect($parentComment->replies)->toHaveCount(5);
});

it('checks if comment is approved', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $approvedComment = BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Approved',
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    $pendingComment = BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Pending',
        'status' => 'pending',
    ]);

    expect($approvedComment->isApproved())->toBeTrue();
    expect($pendingComment->isApproved())->toBeFalse();
});

it('filters approved comments', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Approved 1',
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Approved 2',
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Pending',
        'status' => 'pending',
    ]);

    BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Rejected',
        'status' => 'rejected',
    ]);

    $approvedComments = BlogComment::where('status', 'approved')->get();

    expect($approvedComments)->toHaveCount(2);
});

it('supports soft deletes', function () {
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
        'content' => 'Test comment',
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    $commentId = $comment->id;

    $comment->delete();

    expect(BlogComment::find($commentId))->toBeNull();
    expect(BlogComment::withTrashed()->find($commentId))->not()->toBeNull();
});

it('sanitizes HTML content', function () {
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
        'content' => '<p>Safe content</p><script>alert("XSS")</script>',
        'status' => 'pending',
    ]);

    expect($comment->content)->not()->toContain('<script>');
    expect($comment->content)->toContain('Safe content');
});

it('cascades delete to child comments when parent is deleted', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $parentComment = BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'content' => 'Parent',
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    $childComment = BlogComment::create([
        'blog_post_id' => $post->id,
        'author_id' => $user->id,
        'parent_id' => $parentComment->id,
        'content' => 'Child',
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    $parentComment->delete();

    expect(BlogComment::find($childComment->id))->toBeNull();
});

it('tracks approval timestamp', function () {
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
        'content' => 'Test',
        'status' => 'pending',
    ]);

    expect($comment->approved_at)->toBeNull();

    $comment->update([
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    expect($comment->approved_at)->not()->toBeNull();
});
