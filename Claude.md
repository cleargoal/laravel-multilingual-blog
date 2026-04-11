# Laravel Blog Package - Development Guide

## Quick Reference
- **Package**: Laravel 11+ multilingual blog system with Filament admin
- **Test Status**: 158/160 passing (98.75%) ✅
- **Key Dependencies**: Spatie Translatable, Sluggable, MediaLibrary

## Core Architecture
See `docs/architecture.md` for detailed information.

**Key Components**:
- Models: BlogPost, BlogCategory, BlogComment, PostTag (see `docs/models.json`)
- Actions: Business logic layer (GetBlogIndexData, GetBlogPostForShow, etc.)
- Services: ContentSanitizer, BlogAuthorizer
- Observers: Event dispatching (BlogPostObserver, BlogCommentObserver)

## Testing
```bash
./vendor/bin/pest --no-coverage
```
Status and configuration: `docs/testing.yaml`

## Important Patterns

### 1. Translations
All models use Spatie Translatable. Access translations:
```php
$post->title                        // Current locale
$post->getTranslations('title')     // All locales: ['en' => 'Title', 'uk' => 'Заголовок']
```

### 2. Models Use author_id (not user_id)
```php
BlogPost::where('author_id', $userId)
BlogComment::where('author_id', $userId)
```

### 3. Configuration-Based Models
```php
$userModel = config('blog.models.user');
$blogPostModel = config('blog.models.blog_post', BlogPost::class);
```

### 4. Soft Deletes
BlogPost and BlogComment use SoftDeletes - cascade handled in model boot().

## Common Tasks

### Run Tests
```bash
./vendor/bin/pest tests/Unit/      # Unit tests only
./vendor/bin/pest tests/Feature/   # Feature tests only
./vendor/bin/pest --no-coverage    # All tests
```

### Run Seeder
```bash
php artisan db:seed --class=YourVendor\\Blog\\Database\\Seeders\\BlogSeeder
```

### Install Package
```bash
php artisan blog:install --migrate --seed
```

## File Locations
- Models: `src/Models/`
- Actions: `src/Actions/Blog/`
- Controllers: `src/Http/Controllers/`
- Tests: `tests/Unit/`, `tests/Feature/`
- Config: `config/blog.php`, `config/blog-automation.php`
- Routes: `routes/blog.php`
- Views: `resources/views/`

## Known Issues
Two test failures (both 404s in UserBlogControllerTest):
- `blog.favorites` route
- `blog.my-posts` route

Both routes are defined and controllers exist - edge case in test environment routing.

## Documentation
- **Architecture**: `docs/architecture.md`
- **Models**: `docs/models.json`
- **Testing**: `docs/testing.yaml`
