# Quick Reference Guide

## Common Commands

### Testing
```bash
# Run all tests
./vendor/bin/pest --no-coverage

# Run specific test file
./vendor/bin/pest tests/Unit/Models/BlogPostTest.php

# Run with coverage
./vendor/bin/pest --coverage

# Run only unit tests
./vendor/bin/pest tests/Unit/

# Run only feature tests
./vendor/bin/pest tests/Feature/
```

### Installation
```bash
# Install package
composer require yourvendor/blog

# Publish assets
php artisan blog:install

# With options
php artisan blog:install --migrate --seed --force
```

### Database
```bash
# Run migrations
php artisan migrate

# Run seeder
php artisan db:seed --class=YourVendor\\Blog\\Database\\Seeders\\BlogSeeder

# Rollback
php artisan migrate:rollback --path=database/migrations/2024_01_01_000000_create_blog_tables.php
```

## Common Gotchas

### 1. Always Use `author_id`
```php
// ✓ Correct
BlogPost::where('author_id', $userId)
BlogComment::where('author_id', $userId)

// ✗ Wrong
BlogPost::where('user_id', $userId)  // Column doesn't exist!
```

### 2. Translation Access in Tests
```php
// ✓ In tests - use getTranslations()
expect($post->getTranslations('title'))->toBe(['en' => 'Title'])

// ✗ In tests - don't use property directly
expect($post->title)->toBe(['en' => 'Title'])  // Will fail! Returns string

// ✓ In runtime/views - property works
{{ $post->title }}  // Returns translated string for current locale
```

### 3. Configuration-Based Models
```php
// ✓ Always use config()
$model = config('blog.models.blog_post', BlogPost::class);
$posts = $model::published()->get();

// ✗ Never hardcode
$posts = BlogPost::published()->get();
```

### 4. Soft Deletes
```php
// BlogPost and BlogComment use soft deletes
$post->delete();  // Soft delete
$post->forceDelete();  // Permanent delete

// Include trashed
BlogPost::withTrashed()->get();

// Only trashed
BlogPost::onlyTrashed()->get();
```

### 5. Foreign Keys in Tests
```php
// TestCase::setUp() must enable foreign keys for SQLite
if (config('database.connections.testing.driver') === 'sqlite') {
    \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = ON;');
}
```

### 6. Observer Events
```php
// Events fire automatically via Observers
BlogPost::create([...]);  // Fires BlogPostCreated event

// Don't dispatch manually unless needed
Event::dispatch(new BlogPostCreated($post));  // Usually not needed
```

## File Naming Conventions

### Models
```
BlogPost.php          // Not Post.php or Blog_Post.php
BlogCategory.php      // Not Category.php
PostTag.php           // Not Tag.php or BlogTag.php
```

### Tables
```
blog_posts            // Not posts
blog_categories       // Not categories
post_tags             // Not tags
post_tag              // Pivot table (not post_tags)
```

### Foreign Keys
```
author_id             // Not user_id
blog_post_id          // Not post_id
post_tag_id           // Not tag_id
```

## Useful Snippets

### Create Test User
```php
$user = $this->createUser([
    'can_blog' => true,
    'is_admin' => false,
]);
```

### Create Published Post
```php
$post = BlogPost::create([
    'author_id' => $user->id,
    'category_id' => $category->id,
    'title' => ['en' => 'Test Post'],
    'content' => ['en' => 'Content here'],
    'status' => 'published',
    'published_at' => now(),
]);
```

### Create Comment with Reply
```php
$parent = BlogComment::create([
    'blog_post_id' => $post->id,
    'author_id' => $user->id,
    'content' => 'Parent comment',
    'status' => 'approved',
    'approved_at' => now(),
]);

$reply = BlogComment::create([
    'blog_post_id' => $post->id,
    'author_id' => $user->id,
    'parent_id' => $parent->id,
    'content' => 'Reply comment',
    'status' => 'approved',
    'approved_at' => now(),
]);
```

### Sync Tags
```php
$tags = ['Laravel', 'PHP', 'Testing'];
app(SyncPostTags::class)->execute($post, $tags);
```

### Get Blog Index Data
```php
$data = app(GetBlogIndexData::class)->execute(
    categorySlug: 'tutorials',
    tagSlug: null,
    search: 'Laravel'
);

// Returns: ['posts' => $posts, 'categories' => $categories, 'popularTags' => $tags]
```

## Debugging Tips

### Check Route Registration
```bash
php artisan route:list | grep blog
```

### Verify Configuration
```php
dd(config('blog.models'));
dd(config('blog.cache.enabled'));
```

### Enable Query Logging
```php
\DB::enableQueryLog();
// ... run queries ...
dd(\DB::getQueryLog());
```

### Check Translation Data
```php
dd($post->getTranslations('title'));  // See all translations
dd($post->getRawOriginal('title'));   // See JSON in database
```

## Package Publishing

### Publish Config
```bash
php artisan vendor:publish --tag=blog-config
```

### Publish Migrations
```bash
php artisan vendor:publish --tag=blog-migrations
```

### Publish Views
```bash
php artisan vendor:publish --tag=blog-views
```

### Publish All
```bash
php artisan vendor:publish --provider="YourVendor\Blog\BlogServiceProvider"
```
