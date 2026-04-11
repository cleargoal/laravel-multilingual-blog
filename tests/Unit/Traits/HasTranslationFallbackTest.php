<?php

use Cleargoal\Blog\Models\BlogPost;

it('returns translation for requested locale', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'English Title', 'uk' => 'Українська Назва'],
        'content' => ['en' => 'English Content', 'uk' => 'Український Контент'],
        'status' => 'draft',
    ]);

    expect($post->getTranslationSafe('title', 'en'))->toBe('English Title');
    expect($post->getTranslationSafe('title', 'uk'))->toBe('Українська Назва');
});

it('falls back to default locale when requested locale missing', function () {
    config()->set('blog.default_locale', 'en');

    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => 'English Only'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    // Request German, should fall back to English
    expect($post->getTranslationSafe('title', 'de'))->toBe('English Only');
});

it('returns first available translation when default also missing', function () {
    config()->set('blog.default_locale', 'en');

    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['uk' => 'Українська Назва', 'de' => 'Deutsche Titel'],
        'content' => ['uk' => 'Контент'],
        'status' => 'draft',
    ]);

    // Request French, English missing, should return first available
    $result = $post->getTranslationSafe('title', 'fr');
    expect($result)->toBeIn(['Українська Назва', 'Deutsche Titel']);
    expect($result)->not()->toBeEmpty();
});

it('returns empty string when no translations exist', function () {
    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => ''],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    expect($post->getTranslationSafe('title', 'uk'))->toBe('');
});

it('filters out empty string translations', function () {
    config()->set('blog.default_locale', 'en');

    $user = $this->createUser();
    $post = BlogPost::create([
        'author_id' => $user->id,
        'title' => ['en' => '', 'uk' => 'Українська Назва'],
        'content' => ['en' => 'Content'],
        'status' => 'draft',
    ]);

    // Request English but it's empty, should fall back to Ukrainian
    expect($post->getTranslationSafe('title', 'en'))->toBe('Українська Назва');
});
