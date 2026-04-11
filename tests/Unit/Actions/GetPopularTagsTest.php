<?php

use Illuminate\Support\Facades\Cache;
use YourVendor\Blog\Actions\Blog\GetPopularTags;
use YourVendor\Blog\Models\PostTag;
use YourVendor\Blog\Tests\TestCase;


it('returns tags ordered by usage count', function () {
    PostTag::create([
        'name' => ['en' => 'Laravel'],
        'slug' => 'laravel',
        'usage_count' => 100,
    ]);

    PostTag::create([
        'name' => ['en' => 'PHP'],
        'slug' => 'php',
        'usage_count' => 50,
    ]);

    PostTag::create([
        'name' => ['en' => 'JavaScript'],
        'slug' => 'javascript',
        'usage_count' => 75,
    ]);

    $action = new GetPopularTags();
    $result = $action->execute(limit: 10);

    expect($result)->toHaveCount(3);
    expect($result[0]['name'])->toBe('Laravel');
    expect($result[0]['usage_count'])->toBe(100);
    expect($result[1]['name'])->toBe('JavaScript');
    expect($result[2]['name'])->toBe('PHP');
});

it('respects limit parameter', function () {
    for ($i = 0; $i < 10; $i++) {
        PostTag::create([
            'name' => ['en' => "Tag {$i}"],
            'slug' => "tag-{$i}",
            'usage_count' => $i,
        ]);
    }

    $action = new GetPopularTags();
    $result = $action->execute(limit: 5);

    expect($result)->toHaveCount(5);
});

it('excludes tags with zero usage', function () {
    PostTag::create([
        'name' => ['en' => 'Used Tag'],
        'slug' => 'used-tag',
        'usage_count' => 10,
    ]);

    PostTag::create([
        'name' => ['en' => 'Unused Tag'],
        'slug' => 'unused-tag',
        'usage_count' => 0,
    ]);

    $action = new GetPopularTags();
    $result = $action->execute(limit: 10);

    expect($result)->toHaveCount(1);
    expect($result[0]['name'])->toBe('Used Tag');
});

it('caches results when caching is enabled', function () {
    config()->set('blog.cache.enabled', true);
    config()->set('blog.cache.prefix', 'blog');
    config()->set('blog.cache.popular_tags_ttl', 60);

    PostTag::create([
        'name' => ['en' => 'Laravel'],
        'slug' => 'laravel',
        'usage_count' => 50,
    ]);

    Cache::shouldReceive('remember')
        ->once()
        ->with('blog.popular_tags', 60, \Mockery::any())
        ->andReturn([
            ['name' => 'Laravel', 'slug' => 'laravel', 'usage_count' => 50],
        ]);

    $action = new GetPopularTags();
    $result = $action->execute(limit: 10);

    expect($result)->toHaveCount(1);
});

it('returns empty array when no tags exist', function () {
    $action = new GetPopularTags();
    $result = $action->execute(limit: 10);

    expect($result)->toBeArray();
    expect($result)->toHaveCount(0);
});

it('includes slug and name in result', function () {
    PostTag::create([
        'name' => ['en' => 'Laravel', 'uk' => 'Ларавел'],
        'slug' => 'laravel',
        'usage_count' => 50,
    ]);

    $action = new GetPopularTags();
    $result = $action->execute(limit: 10);

    expect($result[0])->toHaveKeys(['name', 'slug', 'usage_count']);
    expect($result[0]['slug'])->toBe('laravel');
});
