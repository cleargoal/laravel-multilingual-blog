<?php

use Cleargoal\Blog\Models\BlogPost;
use Cleargoal\Blog\Models\PostTag;

it('increments usage count', function () {
    $tag = PostTag::create([
        'name' => ['en' => 'Laravel'],
        'slug' => 'laravel',
        'usage_count' => 0,
    ]);

    expect($tag->usage_count)->toBe(0);

    $tag->incrementUsage();
    $tag->refresh();

    expect($tag->usage_count)->toBe(1);

    $tag->incrementUsage();
    $tag->refresh();

    expect($tag->usage_count)->toBe(2);
});

it('decrements usage count', function () {
    $tag = PostTag::create([
        'name' => ['en' => 'Laravel'],
        'slug' => 'laravel',
        'usage_count' => 5,
    ]);

    $tag->decrementUsage();
    $tag->refresh();

    expect($tag->usage_count)->toBe(4);
});

it('does not decrement below zero', function () {
    $tag = PostTag::create([
        'name' => ['en' => 'Laravel'],
        'slug' => 'laravel',
        'usage_count' => 0,
    ]);

    $tag->decrementUsage();
    $tag->refresh();

    expect($tag->usage_count)->toBe(0);
});

it('returns popular tags ordered by usage', function () {
    PostTag::create([
        'name' => ['en' => 'PHP'],
        'slug' => 'php',
        'usage_count' => 10,
    ]);

    PostTag::create([
        'name' => ['en' => 'Laravel'],
        'slug' => 'laravel',
        'usage_count' => 50,
    ]);

    PostTag::create([
        'name' => ['en' => 'JavaScript'],
        'slug' => 'javascript',
        'usage_count' => 30,
    ]);

    PostTag::create([
        'name' => ['en' => 'Unused'],
        'slug' => 'unused',
        'usage_count' => 0,
    ]);

    $popular = PostTag::popular(3);

    expect($popular)->toHaveCount(3);
    expect($popular->first()->slug)->toBe('laravel');
    expect($popular->get(1)->slug)->toBe('javascript');
    expect($popular->get(2)->slug)->toBe('php');
});

it('finds or creates tag by name', function () {
    $tag = PostTag::findOrCreateByName('Laravel', 'en');

    expect($tag)->toBeInstanceOf(PostTag::class);
    expect($tag->slug)->toBe('laravel');
    expect($tag->getTranslation('name', 'en'))->toBe('Laravel');
    expect($tag->usage_count)->toBe(0);

    // Try to find it again - should return existing
    $sameTag = PostTag::findOrCreateByName('Laravel', 'en');
    expect($sameTag->id)->toBe($tag->id);
});

it('has posts relationship', function () {
    $user = $this->createUser();
    $tag = PostTag::create([
        'name' => ['en' => 'Laravel'],
        'slug' => 'laravel',
    ]);

    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'Test Post'],
        'content' => ['en' => 'Content'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $post->tags()->attach($tag->id);

    expect($tag->posts)->toHaveCount(1);
    expect($tag->posts->first()->id)->toBe($post->id);
});
