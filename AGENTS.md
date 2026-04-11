# AGENTS.md - Laravel Multilingual Blog Package

## Commands

```bash
# Tests (Pest)
./vendor/bin/pest --no-coverage              # All tests
./vendor/bin/pest tests/Unit/               # Unit only
./vendor/bin/pest tests/Feature/            # Feature only

# Static analysis
./vendor/bin/phpstan analyse                 # Level 5

# Code style
./vendor/bin/pint                           # Laravel Pint

# Package installation
php artisan blog:install --migrate --seed
```

## Critical Patterns

### 1. Use `author_id`, NOT `user_id`
```php
BlogPost::where('author_id', $userId)
BlogComment::where('author_id', $userId)
```

### 2. Configuration-Based Models
```php
// Always use config() - never hardcode
$model = config('blog.models.blog_post', BlogPost::class);
```

### 3. Translation Access
```php
// Views/runtime: property returns current locale
{{ $post->title }}

// Tests: use getTranslations()
expect($post->getTranslations('title'))->toBe(['en' => 'Title'])
```

### 4. SQLite FK Constraints in Tests
```php
// TestCase.php:23 already enables this
if (config('database.connections.testing.driver') === 'sqlite') {
    DB::statement('PRAGMA foreign_keys = ON;');
}
```

### 5. Soft Deletes
BlogPost and BlogComment use SoftDeletes. Cascade delete handled in model boot().

## Architecture

- **Models**: `src/Models/` (BlogPost, BlogCategory, BlogComment, PostTag)
- **Actions**: `src/Actions/Blog/` - single-purpose classes with `execute()` method
- **Services**: `src/Services/` - implement Contracts interfaces, customizable via config
- **Observers**: Auto-fire events (BlogPostCreated, BlogCommentPosted, etc.)
- **Namespace**: `Cleargoal\Blog`
- **Package entry**: `src/BlogServiceProvider.php`

## Table/Column Conventions

| What | Use |
|------|-----|
| Foreign key | `author_id` (not `user_id`) |
| Pivot table | `post_tag` (singular) |
| Main tables | `blog_posts`, `blog_categories`, `post_tags` |

## Known Issues

- 2 test failures in `UserBlogControllerTest` (blog.favorites and blog.my-posts routes return 404 in test env)
- Routes are defined but edge case in test routing

## Optional Dependencies (guarded by class_exists)

- Filament (admin panel)
- Anthropic/OpenAI SDK (AI generation)
- Mews/Purifier (HTML sanitization)
