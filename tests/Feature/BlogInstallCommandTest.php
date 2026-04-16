<?php

use Cleargoal\Blog\Models\BlogCategory;
use Cleargoal\Blog\Models\BlogPost;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    // Clean up published config before each test
    $configPath = config_path('blog.php');
    if (File::exists($configPath)) {
        File::delete($configPath);
    }
});

afterEach(function () {
    // Clean up published config after each test
    $configPath = config_path('blog.php');
    if (File::exists($configPath)) {
        File::delete($configPath);
    }
});

it('publishes config file', function () {
    $configPath = config_path('blog.php');

    $this->artisan('blog:install', ['--no-interaction' => true])
        ->expectsConfirmation('Run migrations now?', 'no') // First question in showMigrationsReminder
        ->expectsConfirmation('Publish migration files for customization? (optional)', 'no') // Second question in showMigrationsReminder
        ->expectsConfirmation('Publish view files for customization?', 'no') // Question in publishViews
        ->assertSuccessful();

    expect(File::exists($configPath))->toBeTrue();
});

it('can publish migrations for customization', function () {
    $migrationsPath = database_path('migrations');

    // Clean up any existing migration files first
    $existingFiles = File::glob($migrationsPath.'/*_create_blog_tables.php');
    foreach ($existingFiles as $file) {
        File::delete($file);
    }

    // Run install with interaction to publish migrations
    $this->artisan('blog:install')
        ->expectsConfirmation('Run migrations now?', 'no')
        ->expectsConfirmation('Publish migration files for customization? (optional)', 'yes')
        ->expectsConfirmation('Publish view files for customization?', 'no')
        ->assertSuccessful();

    $migrationFiles = File::glob($migrationsPath.'/*_create_blog_tables.php');

    expect(count($migrationFiles))->toBeGreaterThan(0);

    // Cleanup
    foreach ($migrationFiles as $file) {
        File::delete($file);
    }
});

it('tables are accessible via auto-loaded migrations', function () {
    // Migrations are auto-loaded by BlogServiceProvider
    // Tables should already exist from the test setup
    expect(Schema::hasTable('blog_posts'))->toBeTrue();
    expect(Schema::hasTable('blog_categories'))->toBeTrue();
    expect(Schema::hasTable('blog_comments'))->toBeTrue();
});

it('displays success message', function () {
    $this->artisan('blog:install', ['--no-interaction' => true])
        ->expectsConfirmation('Run migrations now?', 'no')
        ->expectsConfirmation('Publish migration files for customization? (optional)', 'no')
        ->expectsConfirmation('Publish view files for customization?', 'no')
        ->assertSuccessful()
        ->expectsOutputToContain('Blog package installed successfully');
});

it('can seed sample data when requested', function () {
    $this->createUser(); // Ensure at least one user exists

    $this->artisan('blog:install', ['--seed' => true, '--no-interaction' => true])
        ->expectsConfirmation('Run migrations now?', 'no')
        ->expectsConfirmation('Publish migration files for customization? (optional)', 'no')
        ->expectsConfirmation('Publish view files for customization?', 'no')
        ->assertSuccessful();

    expect(BlogPost::count())->toBeGreaterThan(0);
    expect(BlogCategory::count())->toBeGreaterThan(0);
});

it('skips seeding when not requested', function () {
    $this->artisan('blog:install', ['--no-interaction' => true])
        ->expectsConfirmation('Run migrations now?', 'no')
        ->expectsConfirmation('Publish migration files for customization? (optional)', 'no')
        ->expectsConfirmation('Publish view files for customization?', 'no')
        ->assertSuccessful();

    expect(BlogPost::count())->toBe(0);
});
