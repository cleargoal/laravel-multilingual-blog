<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Cleargoal\Blog\Tests\TestCase;


it('publishes config file', function () {
    $configPath = config_path('blog.php');

    if (File::exists($configPath)) {
        File::delete($configPath);
    }

    Artisan::call('blog:install', ['--no-interaction' => true]);

    expect(File::exists($configPath))->toBeTrue();

    // Cleanup
    if (File::exists($configPath)) {
        File::delete($configPath);
    }
});

it('publishes migrations', function () {
    $migrationsPath = database_path('migrations');

    Artisan::call('blog:install', ['--no-interaction' => true]);

    $migrationFiles = File::glob($migrationsPath.'/*_create_blog_tables.php');

    expect(count($migrationFiles))->toBeGreaterThan(0);

    // Cleanup
    foreach ($migrationFiles as $file) {
        File::delete($file);
    }
});

it('runs migrations when confirmed', function () {
    Artisan::call('blog:install', [
        '--no-interaction' => true,
        '--migrate' => true,
    ]);

    expect(\Illuminate\Support\Facades\Schema::hasTable('blog_posts'))->toBeTrue();
    expect(\Illuminate\Support\Facades\Schema::hasTable('blog_categories'))->toBeTrue();
    expect(\Illuminate\Support\Facades\Schema::hasTable('blog_comments'))->toBeTrue();
});

it('displays success message', function () {
    $output = Artisan::call('blog:install', ['--no-interaction' => true]);

    expect($output)->toBe(0);

    $outputText = Artisan::output();
    expect($outputText)->toContain('Blog package installed successfully');

    // Cleanup
    $configPath = config_path('blog.php');
    if (File::exists($configPath)) {
        File::delete($configPath);
    }
});

it('can seed sample data when requested', function () {
    $this->createUser(); // Ensure at least one user exists

    Artisan::call('blog:install', [
        '--no-interaction' => true,
        '--seed' => true,
    ]);

    expect(\Cleargoal\Blog\Models\BlogPost::count())->toBeGreaterThan(0);
    expect(\Cleargoal\Blog\Models\BlogCategory::count())->toBeGreaterThan(0);
});

it('skips seeding when not requested', function () {
    Artisan::call('blog:install', ['--no-interaction' => true]);

    expect(\Cleargoal\Blog\Models\BlogPost::count())->toBe(0);
});
