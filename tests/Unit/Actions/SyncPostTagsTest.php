<?php

use Cleargoal\Blog\Actions\Blog\SyncPostTags;
use Cleargoal\Blog\Models\BlogPost;
use Cleargoal\Blog\Models\PostTag;
use Cleargoal\Blog\Tests\TestCase;


it('creates new tags and attaches them to post', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    $action = new SyncPostTags();
    $action->execute($post, ['Laravel', 'PHP', 'Testing']);

    expect($post->tags)->toHaveCount(3);
    expect($post->tags->pluck('slug')->toArray())->toEqual(['laravel', 'php', 'testing']);
});

it('increments usage count when tag is attached', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    $tag = PostTag::create([
        'name' => ['en' => 'Laravel'],
        'slug' => 'laravel',
        'usage_count' => 5,
    ]);

    $action = new SyncPostTags();
    $action->execute($post, ['Laravel']);

    $tag->refresh();
    expect($tag->usage_count)->toBe(6);
});

it('decrements usage count when tag is removed', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    $tag = PostTag::create([
        'name' => ['en' => 'Laravel'],
        'slug' => 'laravel',
        'usage_count' => 10,
    ]);

    $post->tags()->attach($tag->id);
    $tag->incrementUsage();
    $tag->refresh();
    expect($tag->usage_count)->toBe(11);

    $action = new SyncPostTags();
    $action->execute($post, []); // Remove all tags

    $tag->refresh();
    expect($tag->usage_count)->toBe(10);
});

it('reuses existing tags by slug', function () {
    $existingTag = PostTag::create([
        'name' => ['en' => 'Laravel'],
        'slug' => 'laravel',
        'usage_count' => 0,
    ]);

    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    $action = new SyncPostTags();
    $action->execute($post, ['Laravel']);

    expect(PostTag::count())->toBe(1);
    expect($post->tags->first()->id)->toBe($existingTag->id);
});

it('handles empty tag array', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    $tag = PostTag::create([
        'name' => ['en' => 'Laravel'],
        'slug' => 'laravel',
    ]);

    $post->tags()->attach($tag->id);
    expect($post->tags)->toHaveCount(1);

    $action = new SyncPostTags();
    $action->execute($post, []);

    $post->refresh();
    expect($post->tags)->toHaveCount(0);
});

it('handles mixed case and whitespace in tag names', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    $action = new SyncPostTags();
    $action->execute($post, ['  Laravel  ', 'PHP', 'vue.js']);

    expect($post->tags)->toHaveCount(3);
    expect($post->tags->pluck('slug')->toArray())->toEqual(['laravel', 'php', 'vuejs']);
});
