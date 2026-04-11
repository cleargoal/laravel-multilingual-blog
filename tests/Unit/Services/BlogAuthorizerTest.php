<?php

use Cleargoal\Blog\Models\BlogPost;
use Cleargoal\Blog\Services\DefaultBlogAuthorizer;

it('allows anyone to view published posts', function () {
    $authorizer = new DefaultBlogAuthorizer;
    $author = $this->createUser();
    $viewer = $this->createUser(['email' => 'viewer@example.com', 'can_blog' => false]);

    $post = BlogPost::create([
        'author_id' => $author->id,
        'title' => ['en' => 'Published Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    expect($authorizer->canView($viewer, $post))->toBeTrue();
});

it('only allows author and managers to view draft posts', function () {
    $authorizer = new DefaultBlogAuthorizer;
    $author = $this->createUser();
    $viewer = $this->createUser(['email' => 'viewer@example.com', 'can_blog' => false]);
    $admin = $this->createAdmin(['email' => 'admin@example.com']);

    $post = BlogPost::create([
        'author_id' => $author->id,
        'title' => ['en' => 'Draft Post'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    expect($authorizer->canView($author, $post))->toBeTrue();
    expect($authorizer->canView($admin, $post))->toBeTrue();
    expect($authorizer->canView($viewer, $post))->toBeFalse();
});

it('allows only users with canManageBlogPosts to create posts', function () {
    $authorizer = new DefaultBlogAuthorizer;
    $blogger = $this->createUser(['can_blog' => true]);
    $regular = $this->createUser(['email' => 'regular@example.com', 'can_blog' => false]);

    expect($authorizer->canCreate($blogger))->toBeTrue();
    expect($authorizer->canCreate($regular))->toBeFalse();
});

it('allows post author and managers to update posts', function () {
    $authorizer = new DefaultBlogAuthorizer;
    $author = $this->createUser();
    $otherUser = $this->createUser(['email' => 'other@example.com']);
    $admin = $this->createAdmin(['email' => 'admin@example.com']);

    $post = BlogPost::create([
        'author_id' => $author->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    expect($authorizer->canUpdate($author, $post))->toBeTrue();
    expect($authorizer->canUpdate($admin, $post))->toBeTrue();
    expect($authorizer->canUpdate($otherUser, $post))->toBeFalse();
});

it('allows post author and managers to delete posts', function () {
    $authorizer = new DefaultBlogAuthorizer;
    $author = $this->createUser();
    $otherUser = $this->createUser(['email' => 'other@example.com']);
    $admin = $this->createAdmin(['email' => 'admin@example.com']);

    $post = BlogPost::create([
        'author_id' => $author->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    expect($authorizer->canDelete($author, $post))->toBeTrue();
    expect($authorizer->canDelete($admin, $post))->toBeTrue();
    expect($authorizer->canDelete($otherUser, $post))->toBeFalse();
});

it('allows only managers to publish posts', function () {
    $authorizer = new DefaultBlogAuthorizer;
    $blogger = $this->createUser(['can_blog' => true, 'is_admin' => false]);
    $admin = $this->createAdmin();

    expect($authorizer->canPublish($admin))->toBeTrue();
    expect($authorizer->canPublish($blogger))->toBeTrue(); // In default implementation, canManageBlogPosts = canPublish
});
