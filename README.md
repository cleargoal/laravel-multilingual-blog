# Laravel Multilingual Blog Package

A complete, production-ready multilingual blog system for Laravel with AI content generation, RSS imports, Filament admin integration, and advanced features.

## Features

### Core Features
- 📝 **Multilingual Content** - Full support for multiple languages using Spatie Translatable
- 🎨 **Media Management** - Featured images and content images using Spatie Media Library
- 📂 **Hierarchical Categories** - Nested categories with translations
- 🏷️ **Smart Tagging** - Multilingual tags with usage tracking and tag clouds
- 💬 **Threaded Comments** - Nested comments with approval workflow
- ⭐ **Ratings & Favorites** - 5-star rating system and user favorites
- 🔍 **Advanced Search** - Full-text search across titles, content, and tags
- 📊 **Analytics** - Track views, ratings, and engagement metrics
- 🔐 **Authorization** - Flexible permission system via BlogAuthor interface

### Automation Features (Optional)
- 🤖 **AI Content Generation** - Automated blog post creation using Claude/OpenAI
- 📡 **RSS Feed Imports** - Import content from external RSS feeds
- 🌍 **Automated Translation** - Queue-based translation using DeepL or custom providers
- ⏰ **Scheduled Publishing** - Auto-publish posts on schedule
- 🖼️ **Auto Image Fetching** - Fetch featured images from Unsplash

### Admin Features
- 🎛️ **Filament Integration** - Beautiful admin panel out of the box
- 📈 **Dashboard Analytics** - Author performance metrics
- ✅ **Draft Management** - Preview unpublished posts
- 🔄 **Bulk Operations** - Tag management, status changes

## Requirements

- PHP 8.2+
- Laravel 11.0+ | 12.0+ | 13.0+
- PostgreSQL or MySQL (JSON column support required)

## Installation

### 1. Install via Composer

```bash
composer require cleargoal/laravel-multilingual-blog
```

### 2. Run Migrations

Migrations are auto-loaded from the package, just run:

```bash
php artisan migrate
```

Or use the install command for full setup with configuration:
```bash
php artisan blog:install
```

The `blog:install` command will:
- Publish configuration files (`config/blog.php`, `config/blog-automation.php`)
- Optionally publish views for customization
- Optionally seed sample data (with `--seed` flag)

### 3. Implement BlogAuthor Interface

Update your `User` model:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use YourVendor\Blog\Contracts\BlogAuthor;
use YourVendor\Blog\Traits\HasBlogPosts;

class User extends Authenticatable implements BlogAuthor
{
    use HasBlogPosts;

    // Implement required methods
    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function canManageBlogPosts(): bool
    {
        // Define your logic - e.g., check role or permission
        return $this->is_admin || $this->role === 'blogger';
    }
}
```

### 4. Configure the Package

Edit `config/blog.php`:

```php
return [
    'models' => [
        'user' => \App\Models\User::class,
        // ... other models
    ],

    'features' => [
        'comments' => true,
        'ratings' => true,
        'favorites' => true,
        'automation' => false, // Enable for AI/RSS features
    ],

    'languages' => ['en', 'uk', 'de', 'fr', 'es'],
    'default_locale' => 'en',

    // ... more configuration options
];
```

### 5. Access Your Blog

Visit `http://yourapp.test/blog` to see your blog!

## Configuration

### Basic Configuration (`config/blog.php`)

```php
// Customize routes
'routes' => [
    'prefix' => 'blog',           // URL prefix
    'name_prefix' => 'blog.',     // Route name prefix
    'middleware' => ['web'],      // Middleware for public routes
],

// Enable/disable features
'features' => [
    'comments' => true,
    'ratings' => true,
    'favorites' => true,
    'rss_feeds' => false,
    'automation' => false,
],

// Pagination
'pagination' => [
    'per_page' => 15,
    'featured_count' => 3,
],

// Caching
'cache' => [
    'enabled' => true,
    'ttl' => 3600,
    'prefix' => 'blog',
],
```

### Automation Configuration (`config/blog-automation.php`)

```php
// AI Content Generation
'ai' => [
    'provider' => 'anthropic',  // or 'openai'
    'model' => 'claude-3-5-sonnet-20241022',
    'api_key' => env('ANTHROPIC_API_KEY'),
],

// Translation
'translation' => [
    'provider' => \YourVendor\Blog\Services\DeepLTranslationAdapter::class,
    'enabled' => true,
    'queue' => true,
    'target_languages' => ['uk', 'de', 'fr', 'es'],
],

// RSS Imports
'rss' => [
    'enabled' => true,
    'feeds' => [
        'https://example.com/feed.xml',
    ],
    'import_frequency' => 'daily',
],
```

## Usage

### Creating Blog Posts Programmatically

```php
use YourVendor\Blog\Models\BlogPost;
use YourVendor\Blog\Models\BlogCategory;

$post = BlogPost::create([
    'author_id' => $user->id,
    'category_id' => $category->id,
    'title' => ['en' => 'My First Post', 'uk' => 'Мій перший пост'],
    'excerpt' => ['en' => 'A short excerpt', 'uk' => 'Короткий уривок'],
    'content' => ['en' => '<p>Full content here</p>', 'uk' => '<p>Повний контент тут</p>'],
    'status' => 'published',
    'original_locale' => 'en',
    'published_at' => now(),
]);

// Attach tags
$post->tags()->attach($tagIds);
```

### Querying Posts

```php
// Get published posts
$posts = BlogPost::published()
    ->with(['author', 'category', 'tags'])
    ->latest('published_at')
    ->paginate(15);

// Get featured posts
$featured = BlogPost::featured()
    ->published()
    ->limit(3)
    ->get();

// Filter by category
$categoryPosts = BlogPost::where('category_id', $categoryId)
    ->published()
    ->get();
```

### Using Actions

```php
use YourVendor\Blog\Actions\Blog\GetPopularPosts;

$popularPosts = app(GetPopularPosts::class)->execute(
    limit: 5,
    period: '7days' // or '30days', 'alltime'
);
```

### AI Content Generation

```php
use YourVendor\Blog\Services\BlogContentOrchestrator;

$orchestrator = app(BlogContentOrchestrator::class);

// Generate a blog post
$post = $orchestrator->generateOriginalPost(
    topic: 'Laravel Best Practices',
    category: 'tutorials'
);

// Post will be automatically translated to configured languages
```

## Filament Integration

The package automatically registers Filament resources if Filament is installed:

```php
// config/blog.php
'features' => [
    'filament' => true, // Enable Filament admin integration
],
```

Access at: `yourapp.test/admin/blog-posts`

## Customization

### Custom Authorization

Create your own authorizer:

```php
namespace App\Blog;

use YourVendor\Blog\Contracts\BlogAuthorizer;

class CustomBlogAuthorizer implements BlogAuthorizer
{
    public function canView($user, $post): bool
    {
        // Your custom logic
    }

    // ... implement other methods
}
```

Register in `config/blog.php`:

```php
'authorization' => [
    'authorizer' => \App\Blog\CustomBlogAuthorizer::class,
],
```

### Custom HTML Sanitization

```php
namespace App\Blog;

use YourVendor\Blog\Contracts\ContentSanitizer;

class PurifierSanitizer implements ContentSanitizer
{
    public function sanitizeHtml(string $html): string
    {
        return clean($html); // Using mews/purifier
    }
}
```

Register in `config/blog.php`:

```php
'sanitizer' => \App\Blog\ContentSanitizer::class,
```

### Publishing Views

```bash
php artisan vendor:publish --tag=blog-views
```

Views will be published to `resources/views/vendor/blog/`

## Database Schema

The package creates 8 tables:

- `blog_categories` - Hierarchical categories with translations
- `blog_posts` - Main blog posts table
- `post_tags` - Tags with translations and usage tracking
- `post_tag` - Post-tag pivot table
- `blog_comments` - Threaded comments with approval
- `blog_post_ratings` - User ratings (1-5 stars)
- `blog_post_favorites` - User favorites
- `blog_rss_imports` - RSS import tracking

All tables use configurable names via `config/blog.php`.

## Events

The package dispatches events for key actions:

- `BlogPostCreated`
- `BlogPostUpdated`
- `BlogPostPublished`
- `BlogPostDeleted`
- `BlogCommentPosted`
- `BlogCommentApproved`

Listen to events in your `EventServiceProvider`:

```php
protected $listen = [
    \YourVendor\Blog\Events\BlogPostPublished::class => [
        \App\Listeners\NotifySubscribers::class,
    ],
];
```

## Testing

```bash
composer test
```

## Security

Content is automatically sanitized using the configured `ContentSanitizer`. By default, only safe HTML tags are allowed.

For production use with user-generated content, consider:
- Installing `mews/purifier` for advanced HTML purification
- Implementing CSRF protection (included in routes)
- Rate limiting on comment/rating endpoints
- Input validation on all user submissions

## Performance

- **Eager Loading**: All relationships are eager-loaded by default
- **Caching**: Popular posts, tags, and analytics are cached
- **Database Indexes**: Strategic indexes on frequently queried columns
- **Query Optimization**: N+1 query prevention throughout

## Credits

Built with:
- [Spatie Laravel Translatable](https://github.com/spatie/laravel-translatable)
- [Spatie Laravel Media Library](https://github.com/spatie/laravel-medialibrary)
- [Spatie Laravel Sluggable](https://github.com/spatie/laravel-sluggable)
- [Filament](https://filamentphp.com/)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/yourvendor/laravel-multilingual-blog).
