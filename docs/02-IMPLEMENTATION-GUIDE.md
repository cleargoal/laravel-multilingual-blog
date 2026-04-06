# Blog Package: Step-by-Step Implementation Guide
## Complete Instructions for Package Creation

**Package Name**: `yourvendor/laravel-multilingual-blog`
**Source Project**: `/home/yefrem/projects/freelance`
**Target Audience**: Another Claude instance or developer building the package
**Documentation Version**: 1.0
**Last Updated**: 2026-04-06

---

## Table of Contents

1. [Prerequisites & Setup](#1-prerequisites--setup)
2. [Step 1: Package Initialization](#step-1-package-initialization)
3. [Step 2: Create Interfaces & Contracts](#step-2-create-interfaces--contracts)
4. [Step 3: Create Traits](#step-3-create-traits)
5. [Step 4: Copy & Refactor Models](#step-4-copy--refactor-models)
6. [Step 5: Create Events](#step-5-create-events)
7. [Step 6: Copy & Refactor Actions](#step-6-copy--refactor-actions)
8. [Step 7: Copy & Refactor Services](#step-7-copy--refactor-services)
9. [Step 8: Copy & Refactor Jobs](#step-8-copy--refactor-jobs)
10. [Step 9: Copy & Refactor Controllers](#step-9-copy--refactor-controllers)
11. [Step 10: Copy Observers](#step-10-copy-observers)
12. [Step 11: Consolidate Migrations](#step-11-consolidate-migrations)
13. [Step 12: Copy Views & Components](#step-12-copy-views--components)
14. [Step 13: Copy Filament Resources](#step-13-copy-filament-resources)
15. [Step 14: Create Configuration Files](#step-14-create-configuration-files)
16. [Step 15: Create Service Provider](#step-15-create-service-provider)
17. [Step 16: Create Routes](#step-16-create-routes)
18. [Step 17: Create Seeder](#step-17-create-seeder)
19. [Step 18: Create Artisan Command](#step-18-create-artisan-command)
20. [Step 19: Testing Setup](#step-19-testing-setup)
21. [Step 20: Documentation](#step-20-documentation)
22. [Step 21: Package Publishing](#step-21-package-publishing)
23. [Verification Checklist](#verification-checklist)

---

## 1. Prerequisites & Setup

### 1.1 Required Tools

- PHP 8.2+
- Composer 2.x
- Git
- Code editor (VS Code, PHPStorm, etc.)
- Access to source project at `/home/yefrem/projects/freelance`

### 1.2 Initial Setup

**Create Package Directory**:
```bash
mkdir -p ~/packages/laravel-multilingual-blog
cd ~/packages/laravel-multilingual-blog
git init
```

**Create Base Directory Structure**:
```bash
mkdir -p src/{Models,Http/Controllers,Actions/Blog,Jobs,Services,Observers,Contracts,Traits,Events,Console/Commands,Filament/Resources}
mkdir -p database/{migrations,seeders}
mkdir -p resources/{views,lang/en}
mkdir -p routes
mkdir -p config
mkdir -p tests/{Feature,Unit}
```

**Verify Structure**:
```bash
tree -L 2
# Should show all directories created
```

---

## Step 1: Package Initialization

### 1.1 Create composer.json

**File**: `composer.json`

```json
{
    "name": "yourvendor/laravel-multilingual-blog",
    "description": "Complete multilingual blog system with AI generation, RSS imports, and Filament admin",
    "keywords": [
        "laravel",
        "blog",
        "multilingual",
        "filament",
        "spatie",
        "ai",
        "rss"
    ],
    "license": "MIT",
    "type": "library",
    "authors": [
        {
            "name": "Your Name",
            "email": "your@email.com"
        }
    ],
    "require": {
        "php": "^8.2",
        "laravel/framework": "^11.0|^12.0",
        "spatie/laravel-translatable": "^6.0|^7.0",
        "spatie/laravel-sluggable": "^3.6",
        "spatie/laravel-medialibrary": "^11.0"
    },
    "require-dev": {
        "orchestra/testbench": "^9.0",
        "pestphp/pest": "^3.0",
        "pestphp/pest-plugin-laravel": "^3.0",
        "phpstan/phpstan": "^2.0",
        "laravel/pint": "^1.0"
    },
    "suggest": {
        "filament/filament": "Required for admin panel integration (^4.0)",
        "anthropics/anthropic-sdk-php": "Required for AI content generation (^1.0)",
        "yourvendor/laravel-deepl-translations": "Recommended for automated translations with key rotation (^1.0)",
        "spatie/laravel-feed": "Required for RSS feed generation (^4.0)",
        "mews/purifier": "Recommended for advanced HTML sanitization (^3.3)"
    },
    "autoload": {
        "psr-4": {
            "YourVendor\\Blog\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "YourVendor\\Blog\\Tests\\": "tests/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "YourVendor\\Blog\\BlogServiceProvider"
            ]
        }
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

### 1.2 Create Package Files

**File**: `README.md` (basic structure, will expand in Step 20)

```markdown
# Laravel Multilingual Blog

Complete blog system with AI generation, RSS imports, and Filament admin.

## Features

- Multilingual support (5 languages)
- AI content generation (Claude API)
- RSS feed imports
- Filament admin panel
- Categories, tags, comments, ratings, favorites
- SEO-friendly (slugs, RSS feeds)

## Installation

```bash
composer require yourvendor/laravel-multilingual-blog
```

See full documentation in `/docs` folder.
```

**File**: `LICENSE` (MIT)

```
MIT License

Copyright (c) 2026 Your Name

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

**File**: `.gitattributes`

```
# Path-based git attributes
/tests export-ignore
/.gitattributes export-ignore
/.gitignore export-ignore
/phpunit.xml export-ignore
/pint.json export-ignore
```

**File**: `.gitignore`

```
/vendor
composer.lock
.phpunit.result.cache
.phpstan-cache
```

### 1.3 Install Dependencies

```bash
composer install
```

**Verify**: No errors, vendor directory created

---

## Step 2: Create Interfaces & Contracts

### 2.1 BlogAuthor Interface

**File**: `src/Contracts/BlogAuthor.php`

```php
<?php

declare(strict_types=1);

namespace YourVendor\Blog\Contracts;

interface BlogAuthor
{
    /**
     * Get the author's unique identifier.
     */
    public function getId(): int;

    /**
     * Get the author's display name.
     */
    public function getName(): string;

    /**
     * Get the author's email address.
     */
    public function getEmail(): string;

    /**
     * Check if the author can manage blog posts (create, edit, delete).
     */
    public function canManageBlogPosts(): bool;
}
```

### 2.2 ContentSanitizer Interface

**File**: `src/Contracts/ContentSanitizer.php`

```php
<?php

declare(strict_types=1);

namespace YourVendor\Blog\Contracts;

interface ContentSanitizer
{
    /**
     * Sanitize HTML content while preserving safe tags.
     *
     * @param  string  $html  Raw HTML content
     * @return string         Sanitized HTML
     */
    public function sanitizeHtml(string $html): string;

    /**
     * Strip all HTML tags from content.
     *
     * @param  string  $html  Raw HTML content
     * @return string         Plain text
     */
    public function stripAllTags(string $html): string;
}
```

### 2.3 BlogAuthorizer Interface

**File**: `src/Contracts/BlogAuthorizer.php`

```php
<?php

declare(strict_types=1);

namespace YourVendor\Blog\Contracts;

interface BlogAuthorizer
{
    /**
     * Check if the given user can view the blog post.
     */
    public function canView(BlogAuthor $user, object $post): bool;

    /**
     * Check if the given user can create blog posts.
     */
    public function canCreate(BlogAuthor $user): bool;

    /**
     * Check if the given user can update the blog post.
     */
    public function canUpdate(BlogAuthor $user, object $post): bool;

    /**
     * Check if the given user can delete the blog post.
     */
    public function canDelete(BlogAuthor $user, object $post): bool;

    /**
     * Check if the given user can publish/unpublish blog posts.
     */
    public function canPublish(BlogAuthor $user): bool;
}
```

### 2.4 BlogTranslationProvider Interface

**File**: `src/Contracts/BlogTranslationProvider.php`

```php
<?php

declare(strict_types=1);

namespace YourVendor\Blog\Contracts;

interface BlogTranslationProvider
{
    /**
     * Translate text from one language to another.
     *
     * @param  string  $text    Text to translate
     * @param  string  $from    Source language code (e.g., 'en')
     * @param  string  $to      Target language code (e.g., 'uk')
     * @return string           Translated text
     *
     * @throws \RuntimeException if translation fails
     */
    public function translate(string $text, string $from, string $to): string;

    /**
     * Check if translation is supported for the given language pair.
     */
    public function canTranslate(string $from, string $to): bool;

    /**
     * Get the list of supported target languages.
     *
     * @return array<string>
     */
    public function supportedLanguages(): array;
}
```

---

## Step 3: Create Traits

### 3.1 HasBlogPosts Trait

**File**: `src/Traits/HasBlogPosts.php`

```php
<?php

declare(strict_types=1);

namespace YourVendor\Blog\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trait for User model to add blog post relationships.
 *
 * Usage:
 * ```php
 * class User extends Authenticatable implements BlogAuthor
 * {
 *     use HasBlogPosts;
 * }
 * ```
 */
trait HasBlogPosts
{
    /**
     * Get all blog posts authored by this user.
     */
    public function blogPosts(): HasMany
    {
        return $this->hasMany(config('blog.models.blog_post'), 'author_id');
    }

    /**
     * Get published blog posts by this user.
     */
    public function publishedBlogPosts(): HasMany
    {
        return $this->blogPosts()
            ->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    /**
     * Get all blog comments by this user.
     */
    public function blogComments(): HasMany
    {
        return $this->hasMany(config('blog.models.blog_comment'), 'author_id');
    }

    /**
     * Get this user's favorite blog posts.
     */
    public function favoriteBlogPosts(): BelongsToMany
    {
        return $this->belongsToMany(
            config('blog.models.blog_post'),
            config('blog.tables.blog_post_favorites'),
            'user_id',
            'blog_post_id'
        )->withTimestamps();
    }

    /**
     * Get all blog post ratings by this user.
     */
    public function blogPostRatings(): HasMany
    {
        return $this->hasMany(config('blog.models.blog_post_rating'), 'user_id');
    }

    /**
     * Check if this user has favorited a specific blog post.
     */
    public function hasFavoritedBlogPost(int $postId): bool
    {
        return $this->favoriteBlogPosts()->where('id', $postId)->exists();
    }

    /**
     * Get the user's rating for a specific blog post.
     */
    public function getBlogPostRating(int $postId): ?int
    {
        $rating = $this->blogPostRatings()
            ->where('blog_post_id', $postId)
            ->first();

        return $rating?->rating;
    }
}
```

### 3.2 HasTranslationFallback Trait

**Source**: `/home/yefrem/projects/freelance/app/Traits/HasTranslationFallback.php`

**Copy to**: `src/Traits/HasTranslationFallback.php`

**Changes**:
1. Update namespace to `YourVendor\Blog\Traits`
2. Keep all logic as-is (graceful fallback for missing translations)

```bash
# Copy command
cp /home/yefrem/projects/freelance/app/Traits/HasTranslationFallback.php \
   src/Traits/HasTranslationFallback.php

# Update namespace
sed -i 's/namespace App\\Traits/namespace YourVendor\\Blog\\Traits/g' \
    src/Traits/HasTranslationFallback.php
```

**File content** (after namespace update):

```php
<?php

declare(strict_types=1);

namespace YourVendor\Blog\Traits;

/**
 * Trait for models using Spatie Translatable with graceful fallback.
 *
 * Provides getTranslationSafe() method that returns:
 * 1. Translation for requested locale
 * 2. Default locale translation if requested missing
 * 3. Empty string if no translations exist
 */
trait HasTranslationFallback
{
    /**
     * Get translation with fallback behavior.
     *
     * @param  string  $key     Translatable attribute name
     * @param  string  $locale  Target locale
     * @return string           Translated value or fallback
     */
    public function getTranslationSafe(string $key, string $locale): string
    {
        // Try requested locale
        $value = $this->getTranslation($key, $locale, false);

        if (! empty($value)) {
            return $value;
        }

        // Fallback to default locale
        $defaultLocale = config('blog.default_locale', 'en');
        if ($locale !== $defaultLocale) {
            $value = $this->getTranslation($key, $defaultLocale, false);

            if (! empty($value)) {
                return $value;
            }
        }

        // Fallback to first available translation
        $translations = $this->getTranslations($key);
        return ! empty($translations) ? (string) reset($translations) : '';
    }
}
```

---

## Step 4: Copy & Refactor Models

### 4.1 BlogPost Model

**Source**: `/home/yefrem/projects/freelance/app/Models/BlogPost.php`

**Copy to**: `src/Models/BlogPost.php`

**Required Changes**:
1. Namespace: `App\Models` → `YourVendor\Blog\Models`
2. User relationship: `user()` → `author()`, use config
3. HTML sanitization: `clean()` → interface
4. Import updates for other models

**Step-by-step**:

```bash
# Copy file
cp /home/yefrem/projects/freelance/app/Models/BlogPost.php \
   src/Models/BlogPost.php
```

**Edit `src/Models/BlogPost.php`**:

```php
<?php

declare(strict_types=1);

namespace YourVendor\Blog\Models; // CHANGED

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;
use YourVendor\Blog\Traits\HasTranslationFallback; // CHANGED
use YourVendor\Blog\Contracts\ContentSanitizer; // ADDED

class BlogPost extends Model implements HasMedia
{
    use HasFactory;
    use HasSlug;
    use HasTranslations;
    use HasTranslationFallback;
    use InteractsWithMedia;
    use SoftDeletes;

    public array $translatable = ['title', 'excerpt', 'content'];

    protected $fillable = [
        'category_id',
        'author_id', // CHANGED from user_id
        'title',
        'slug',
        'excerpt',
        'content',
        'status',
        'published_at',
        'original_locale',
        'is_featured',
        'views_count',
        'rating_average',
        'rating_count',
        'completed_orders',
        'is_external',
        'external_source_name',
        'external_source_url',
        'generated_by_ai',
        'ai_model_used',
        'generation_prompt_version',
        'referral_metadata',
        'is_demo',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'views_count' => 'integer',
            'rating_average' => 'float',
            'rating_count' => 'integer',
            'completed_orders' => 'integer',
            'is_external' => 'boolean',
            'generated_by_ai' => 'boolean',
            'referral_metadata' => 'array',
            'is_demo' => 'boolean',
        ];
    }

    // Boot method - sanitize HTML using interface
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($post) {
            // Sanitize HTML content using configured sanitizer
            $sanitizer = app(ContentSanitizer::class); // CHANGED

            if (! empty($post->content)) {
                foreach (config('blog.languages', ['en']) as $locale) {
                    $content = $post->getTranslation('content', $locale, false);
                    if (! empty($content)) {
                        $post->setTranslation('content', $locale, $sanitizer->sanitizeHtml($content)); // CHANGED
                    }
                }
            }

            // Strip HTML from excerpt
            if (! empty($post->excerpt)) {
                foreach (config('blog.languages', ['en']) as $locale) {
                    $excerpt = $post->getTranslation('excerpt', $locale, false);
                    if (! empty($excerpt)) {
                        $post->setTranslation('excerpt', $locale, $sanitizer->stripAllTags($excerpt)); // CHANGED
                    }
                }
            }
        });
    }

    // Slug configuration
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function ($model) {
                return $model->getTranslation('title', config('blog.default_locale', 'en'));
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    // Relationships

    /**
     * Get the author of this blog post.
     * CHANGED: Renamed from user() to author(), uses config
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(
            config('blog.models.user'),
            'author_id' // CHANGED from user_id
        )->restrictOnDelete(); // From migration 2026_03_23
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            config('blog.models.blog_category', BlogCategory::class) // CHANGED
        )->nullOnDelete();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            config('blog.models.post_tag', PostTag::class), // CHANGED
            config('blog.tables.post_tag_pivot', 'post_tag') // CHANGED
        )->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(
            config('blog.models.blog_comment', BlogComment::class) // CHANGED
        );
    }

    public function approvedComments(): HasMany
    {
        return $this->comments()->where('status', 'approved');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(
            config('blog.models.blog_post_rating', BlogPostRating::class) // CHANGED
        );
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(
            config('blog.models.user'), // CHANGED
            config('blog.tables.blog_post_favorites', 'blog_post_favorites') // CHANGED
        )->withTimestamps();
    }

    public function rssImport(): HasOne
    {
        return $this->hasOne(
            config('blog.models.blog_rss_import', BlogRssImport::class) // CHANGED (if model exists)
        );
    }

    // Media collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')
            ->singleFile()
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('thumb')
                    ->width(400)
                    ->height(300)
                    ->sharpen(10);

                $this->addMediaConversion('large')
                    ->width(1200)
                    ->height(630)
                    ->sharpen(10);
            });

        $this->addMediaCollection('content_images');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeNotDemo($query)
    {
        return $query->where('is_demo', false);
    }

    // Helpers
    public function isPublished(): bool
    {
        return $this->status === 'published'
            && $this->published_at !== null
            && $this->published_at->isPast();
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function updateRatingStats(): void
    {
        $stats = $this->ratings()
            ->selectRaw('AVG(rating) as average, COUNT(*) as count')
            ->first();

        $this->update([
            'rating_average' => $stats->average ?? 0,
            'rating_count' => $stats->count ?? 0,
        ]);
    }

    public function calculateReadingTime(): int
    {
        $content = $this->getTranslation('content', app()->getLocale(), false);
        $wordCount = str_word_count(strip_tags($content ?: ''));

        return (int) ceil($wordCount / 200); // 200 words per minute
    }

    // Override table name (configurable)
    public function getTable()
    {
        return config('blog.tables.blog_posts', parent::getTable());
    }
}
```

**Key Changes Summary**:
- ✅ Namespace updated
- ✅ `user()` → `author()` relationship
- ✅ `user_id` → `author_id` column
- ✅ `clean()` → `ContentSanitizer` interface
- ✅ All model references use `config()`
- ✅ All table names use `config()`

### 4.2 BlogCategory Model

**Source**: `/home/yefrem/projects/freelance/app/Models/BlogCategory.php`

**Copy to**: `src/Models/BlogCategory.php`

**Required Changes**: Namespace only (no user relations)

```bash
# Copy
cp /home/yefrem/projects/freelance/app/Models/BlogCategory.php \
   src/Models/BlogCategory.php

# Update namespace
sed -i 's/namespace App\\Models/namespace YourVendor\\Blog\\Models/g' \
    src/Models/BlogCategory.php

# Update imports
sed -i 's/use App\\Models\\BlogPost/use YourVendor\\Blog\\Models\\BlogPost/g' \
    src/Models/BlogCategory.php
sed -i 's/use App\\Traits\\HasTranslationFallback/use YourVendor\\Blog\\Traits\\HasTranslationFallback/g' \
    src/Models/BlogCategory.php
```

**Add configurable table name at end of class**:

```php
// Override table name (configurable)
public function getTable()
{
    return config('blog.tables.blog_categories', parent::getTable());
}
```

### 4.3 BlogComment Model

**Source**: `/home/yefrem/projects/freelance/app/Models/BlogComment.php`

**Copy to**: `src/Models/BlogComment.php`

**Required Changes**: Namespace + user → author

```bash
# Copy
cp /home/yefrem/projects/freelance/app/Models/BlogComment.php \
   src/Models/BlogComment.php
```

**Edit `src/Models/BlogComment.php`**:

1. Update namespace to `YourVendor\Blog\Models`
2. Change `user()` method to `author()`:

```php
public function author(): BelongsTo // CHANGED from user()
{
    return $this->belongsTo(config('blog.models.user'), 'author_id'); // CHANGED
}
```

3. Update `post()` relationship to use config:

```php
public function post(): BelongsTo
{
    return $this->belongsTo(config('blog.models.blog_post', BlogPost::class));
}
```

4. Add table name override:

```php
public function getTable()
{
    return config('blog.tables.blog_comments', parent::getTable());
}
```

### 4.4 PostTag Model

**Source**: `/home/yefrem/projects/freelance/app/Models/PostTag.php`

**Copy to**: `src/Models/PostTag.php`

**Required Changes**: Namespace + BlogPost reference

```bash
# Copy & update
cp /home/yefrem/projects/freelance/app/Models/PostTag.php \
   src/Models/PostTag.php

sed -i 's/namespace App\\Models/namespace YourVendor\\Blog\\Models/g' \
    src/Models/PostTag.php
sed -i 's/use App\\Models\\BlogPost/use YourVendor\\Blog\\Models\\BlogPost/g' \
    src/Models/PostTag.php
sed -i 's/use App\\Traits\\HasTranslationFallback/use YourVendor\\Blog\\Traits\\HasTranslationFallback/g' \
    src/Models/PostTag.php
```

**Edit relationships**:

```php
public function blogPosts(): BelongsToMany
{
    return $this->belongsToMany(
        config('blog.models.blog_post', BlogPost::class),
        config('blog.tables.post_tag_pivot', 'post_tag')
    )->withTimestamps();
}
```

**Add table override**:

```php
public function getTable()
{
    return config('blog.tables.post_tags', parent::getTable());
}
```

### 4.5 BlogPostRating Model

**Source**: `/home/yefrem/projects/freelance/app/Models/BlogPostRating.php`

**Copy to**: `src/Models/BlogPostRating.php`

**Required Changes**: Namespace + user → author

```bash
# Copy
cp /home/yefrem/projects/freelance/app/Models/BlogPostRating.php \
   src/Models/BlogPostRating.php
```

**Edit file**:

1. Update namespace
2. Change `user()` → `author()` (if exists, or just use config)
3. Update `post()` relationship:

```php
public function post(): BelongsTo
{
    return $this->belongsTo(config('blog.models.blog_post', BlogPost::class));
}

public function user(): BelongsTo
{
    return $this->belongsTo(config('blog.models.user'));
}
```

4. Add table override:

```php
public function getTable()
{
    return config('blog.tables.blog_post_ratings', parent::getTable());
}
```

### 4.6 Verification

Run PHPStan/Pint to check syntax:

```bash
# Check syntax
php -l src/Models/BlogPost.php
php -l src/Models/BlogCategory.php
php -l src/Models/BlogComment.php
php -l src/Models/PostTag.php
php -l src/Models/BlogPostRating.php

# Format code
./vendor/bin/pint src/Models
```

---

## Step 5: Create Events

All events are simple data-carrying classes. Create 6 events:

### 5.1 BlogPostCreated Event

**File**: `src/Events/BlogPostCreated.php`

```php
<?php

declare(strict_types=1);

namespace YourVendor\Blog\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use YourVendor\Blog\Models\BlogPost;

class BlogPostCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public BlogPost $post
    ) {}
}
```

### 5.2-5.6 Other Events

Create similar files for:

- `BlogPostUpdated.php` (same structure)
- `BlogPostPublished.php` (same structure)
- `BlogPostDeleted.php` (same structure)
- `BlogCommentPosted.php` (pass `BlogComment $comment`)
- `BlogCommentApproved.php` (pass `BlogComment $comment`)

**Quick create script**:

```bash
# Create all events
cat > src/Events/BlogPostUpdated.php << 'EOF'
<?php

declare(strict_types=1);

namespace YourVendor\Blog\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use YourVendor\Blog\Models\BlogPost;

class BlogPostUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public BlogPost $post) {}
}
EOF

# ... repeat for other events
```

---

## Step 6: Copy & Refactor Actions

Actions mostly need namespace updates only. Cache keys need config prefixes.

### 6.1 GetBlogIndexData

**Source**: `/home/yefrem/projects/freelance/app/Actions/Blog/GetBlogIndexData.php`

**Copy to**: `src/Actions/Blog/GetBlogIndexData.php`

```bash
# Copy
cp /home/yefrem/projects/freelance/app/Actions/Blog/GetBlogIndexData.php \
   src/Actions/Blog/GetBlogIndexData.php

# Update namespaces
sed -i 's/namespace App\\Actions\\Blog/namespace YourVendor\\Blog\\Actions\\Blog/g' \
    src/Actions/Blog/GetBlogIndexData.php
sed -i 's/use App\\Models\\BlogPost/use YourVendor\\Blog\\Models\\BlogPost/g' \
    src/Actions/Blog/GetBlogIndexData.php
```

### 6.2 GetBlogPostForShow

**Source**: `/home/yefrem/projects/freelance/app/Actions/Blog/GetBlogPostForShow.php`

**Copy to**: `src/Actions/Blog/GetBlogPostForShow.php`

```bash
# Copy & update
cp /home/yefrem/projects/freelance/app/Actions/Blog/GetBlogPostForShow.php \
   src/Actions/Blog/GetBlogPostForShow.php

sed -i 's/namespace App\\Actions\\Blog/namespace YourVendor\\Blog\\Actions\\Blog/g' \
    src/Actions/Blog/GetBlogPostForShow.php
sed -i 's/use App\\Models\\/use YourVendor\\Blog\\Models\\/g' \
    src/Actions/Blog/GetBlogPostForShow.php
```

### 6.3 GetPopularPosts (Cache Key Update)

**Source**: `/home/yefrem/projects/freelance/app/Actions/Blog/GetPopularPosts.php`

**Copy to**: `src/Actions/Blog/GetPopularPosts.php`

**Required Change**: Update cache keys

```bash
# Copy
cp /home/yefrem/projects/freelance/app/Actions/Blog/GetPopularPosts.php \
   src/Actions/Blog/GetPopularPosts.php
```

**Edit file** - change cache key:

```php
// Before
Cache::remember('blog.popular_posts', 3600, function () {
    // ...
});

// After
Cache::remember(
    config('blog.cache.prefix').'.popular_posts',
    config('blog.cache.ttl', 3600),
    function () {
        // ...
    }
);
```

### 6.4 GetPopularTags (Cache Key Update)

Same pattern as GetPopularPosts:

```bash
cp /home/yefrem/projects/freelance/app/Actions/Blog/GetPopularTags.php \
   src/Actions/Blog/GetPopularTags.php
```

Update cache key:

```php
Cache::remember(
    config('blog.cache.prefix').'.popular_tags',
    config('blog.cache.ttl', 3600),
    function () {
        // ...
    }
);
```

### 6.5 SyncPostTags

```bash
cp /home/yefrem/projects/freelance/app/Actions/Blog/SyncPostTags.php \
   src/Actions/Blog/SyncPostTags.php

# Update namespace
sed -i 's/namespace App\\Actions\\Blog/namespace YourVendor\\Blog\\Actions\\Blog/g' \
    src/Actions/Blog/SyncPostTags.php
sed -i 's/use App\\Models\\/use YourVendor\\Blog\\Models\\/g' \
    src/Actions/Blog/SyncPostTags.php
```

### 6.6 GetBlogPostsForHomepage (Cache Key Update)

```bash
cp /home/yefrem/projects/freelance/app/Actions/Blog/GetBlogPostsForHomepage.php \
   src/Actions/Blog/GetBlogPostsForHomepage.php
```

Update cache keys:

```php
Cache::remember(
    config('blog.cache.prefix').'.homepage.posts.'.$locale,
    config('blog.cache.ttl', 3600),
    function () use ($locale) {
        // ...
    }
);
```

---

## Step 7: Copy & Refactor Services

### 7.1 BlogContentOrchestrator (MAJOR REFACTORING)

**Source**: `/home/yefrem/projects/freelance/app/Services/BlogContentOrchestrator.php`

**Copy to**: `src/Services/BlogContentOrchestrator.php`

**This is the most complex refactoring** - Replace ALL `PlatformSetting::get()` calls with `config()`.

**Pattern**:

```php
// Before
use App\Models\PlatformSetting;
$enabled = PlatformSetting::getBoolean('blog_ai_enabled');
$model = PlatformSetting::getString('blog_ai_model');

// After
$enabled = config('blog.automation.ai.enabled');
$model = config('blog.automation.ai.model');
```

**Comprehensive replacement list** (grep source file first):

```bash
# Copy file
cp /home/yefrem/projects/freelance/app/Services/BlogContentOrchestrator.php \
   src/Services/BlogContentOrchestrator.php

# Remove PlatformSetting import
sed -i '/use App\\Models\\PlatformSetting/d' \
    src/Services/BlogContentOrchestrator.php

# Replace all PlatformSetting calls (manual review recommended)
# Example replacements (adjust based on actual usage):

# blog_ai_enabled
sed -i 's/PlatformSetting::getBoolean(\x27blog_ai_enabled\x27)/config(\x27blog.automation.ai.enabled\x27)/g' \
    src/Services/BlogContentOrchestrator.php

# blog_ai_model
sed -i 's/PlatformSetting::getString(\x27blog_ai_model\x27)/config(\x27blog.automation.ai.model\x27)/g' \
    src/Services/BlogContentOrchestrator.php

# blog_publish_immediately
sed -i 's/PlatformSetting::getBoolean(\x27blog_publish_immediately\x27)/config(\x27blog.automation.publish_immediately\x27)/g' \
    src/Services/BlogContentOrchestrator.php

# ... (repeat for ~15-20 more settings)
```

**Manual Review Required**: Open file and verify ALL PlatformSetting references replaced

### 7.2 AIContentGenerationService

```bash
cp /home/yefrem/projects/freelance/app/Services/AIContentGenerationService.php \
   src/Services/AIContentGenerationService.php

# Update namespace
sed -i 's/namespace App\\Services/namespace YourVendor\\Blog\\Services/g' \
    src/Services/AIContentGenerationService.php
```

Check for PlatformSetting usage and replace if needed.

### 7.3 BlogRssImportService

```bash
cp /home/yefrem/projects/freelance/app/Services/BlogRssImportService.php \
   src/Services/BlogRssImportService.php

# Update namespace
sed -i 's/namespace App\\Services/namespace YourVendor\\Blog\\Services/g' \
    src/Services/BlogRssImportService.php
```

### 7.4 DefaultContentSanitizer (NEW)

**File**: `src/Services/DefaultContentSanitizer.php`

```php
<?php

declare(strict_types=1);

namespace YourVendor\Blog\Services;

use YourVendor\Blog\Contracts\ContentSanitizer;

class DefaultContentSanitizer implements ContentSanitizer
{
    /**
     * Sanitize HTML content while preserving safe tags.
     *
     * This default implementation uses strip_tags() with allowed tags.
     * For advanced sanitization, override this service with mews/purifier.
     */
    public function sanitizeHtml(string $html): string
    {
        $allowedTags = '<p><a><strong><em><ul><ol><li><br><img><h1><h2><h3><h4><h5><h6><blockquote><code><pre>';

        return strip_tags($html, $allowedTags);
    }

    /**
     * Strip all HTML tags from content.
     */
    public function stripAllTags(string $html): string
    {
        return strip_tags(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
```

### 7.5 DefaultBlogAuthorizer (NEW)

**File**: `src/Services/DefaultBlogAuthorizer.php`

```php
<?php

declare(strict_types=1);

namespace YourVendor\Blog\Services;

use YourVendor\Blog\Contracts\BlogAuthor;
use YourVendor\Blog\Contracts\BlogAuthorizer;

class DefaultBlogAuthorizer implements BlogAuthorizer
{
    /**
     * Check if the given user can view the blog post.
     *
     * Default: Anyone can view published posts, only author/admin can view drafts.
     */
    public function canView(BlogAuthor $user, object $post): bool
    {
        if ($post->isPublished()) {
            return true;
        }

        // Draft posts only viewable by author or blog managers
        return $post->author_id === $user->getId() || $user->canManageBlogPosts();
    }

    /**
     * Check if the given user can create blog posts.
     *
     * Default: Uses canManageBlogPosts() from BlogAuthor interface.
     */
    public function canCreate(BlogAuthor $user): bool
    {
        return $user->canManageBlogPosts();
    }

    /**
     * Check if the given user can update the blog post.
     *
     * Default: Post author or blog managers only.
     */
    public function canUpdate(BlogAuthor $user, object $post): bool
    {
        return $post->author_id === $user->getId() || $user->canManageBlogPosts();
    }

    /**
     * Check if the given user can delete the blog post.
     *
     * Default: Post author or blog managers only.
     */
    public function canDelete(BlogAuthor $user, object $post): bool
    {
        return $post->author_id === $user->getId() || $user->canManageBlogPosts();
    }

    /**
     * Check if the given user can publish/unpublish blog posts.
     *
     * Default: Blog managers only.
     */
    public function canPublish(BlogAuthor $user): bool
    {
        return $user->canManageBlogPosts();
    }
}
```

### 7.6 DeepLTranslationAdapter (NEW)

**File**: `src/Services/DeepLTranslationAdapter.php`

```php
<?php

declare(strict_types=1);

namespace YourVendor\Blog\Services;

use YourVendor\Blog\Contracts\BlogTranslationProvider;

/**
 * Adapter for yourvendor/laravel-deepl-translations package.
 *
 * This adapter connects the blog package to the DeepL key rotation package.
 * Consuming applications can configure this adapter or provide their own implementation.
 */
class DeepLTranslationAdapter implements BlogTranslationProvider
{
    protected $manager;

    public function __construct()
    {
        // Check if DeepL package is installed
        if (! class_exists('\\YourVendor\\DeepLTranslations\\DeepLApiKeyManager')) {
            throw new \RuntimeException(
                'DeepL translations package not installed. '.
                'Run: composer require yourvendor/laravel-deepl-translations'
            );
        }

        $this->manager = app('\\YourVendor\\DeepLTranslations\\DeepLApiKeyManager');
    }

    public function translate(string $text, string $from, string $to): string
    {
        // Use DeepL package's key rotation logic
        return $this->manager->translate($text, $from, $to);
    }

    public function canTranslate(string $from, string $to): bool
    {
        $supported = $this->supportedLanguages();

        return in_array($from, $supported) && in_array($to, $supported);
    }

    public function supportedLanguages(): array
    {
        // DeepL supported languages (match your package)
        return ['en', 'uk', 'de', 'fr', 'es', 'it', 'pl', 'pt', 'ru', 'ja', 'zh'];
    }
}
```

---

## Step 8: Copy & Refactor Jobs

### 8.1 TranslateBlogPostJob

**Source**: `/home/yefrem/projects/freelance/app/Jobs/TranslateBlogPostJob.php`

**Copy to**: `src/Jobs/TranslateBlogPostJob.php`

**Changes**:
1. Namespace
2. Translation service via interface

```bash
cp /home/yefrem/projects/freelance/app/Jobs/TranslateBlogPostJob.php \
   src/Jobs/TranslateBlogPostJob.php
```

**Edit file** - update translation service usage:

```php
// Before
use App\Services\DeepLTranslationService;
$service = new DeepLTranslationService();

// After
use YourVendor\Blog\Contracts\BlogTranslationProvider;

// In handle() method:
if (! config('blog.translation.enabled')) {
    return;
}

$translationService = app(BlogTranslationProvider::class);

// Use $translationService->translate() instead of direct service
```

### 8.2 GenerateAIBlogPostJob

```bash
cp /home/yefrem/projects/freelance/app/Jobs/GenerateAIBlogPostJob.php \
   src/Jobs/GenerateAIBlogPostJob.php

# Update namespace
sed -i 's/namespace App\\Jobs/namespace YourVendor\\Blog\\Jobs/g' \
    src/Jobs/GenerateAIBlogPostJob.php
sed -i 's/use App\\Services\\/use YourVendor\\Blog\\Services\\/g' \
    src/Jobs/GenerateAIBlogPostJob.php
sed -i 's/use App\\Models\\/use YourVendor\\Blog\\Models\\/g' \
    src/Jobs/GenerateAIBlogPostJob.php
```

---

## Step 9: Copy & Refactor Controllers

### 9.1 BlogController (Public)

**Source**: `/home/yefrem/projects/freelance/app/Http/Controllers/Public/BlogController.php`

**Copy to**: `src/Http/Controllers/BlogController.php`

**Changes**:
1. Namespace (remove `Public`)
2. Auth guards configurable
3. Authorization via interface

```bash
cp /home/yefrem/projects/freelance/app/Http/Controllers/Public/BlogController.php \
   src/Http/Controllers/BlogController.php
```

**Edit file**:

```php
namespace YourVendor\Blog\Http\Controllers; // CHANGED

// Update all model imports
use YourVendor\Blog\Models\BlogPost;
use YourVendor\Blog\Models\BlogCategory;
use YourVendor\Blog\Models\PostTag;
use YourVendor\Blog\Actions\Blog\GetBlogIndexData;
// ... etc

class BlogController extends Controller
{
    // Replace auth() with config-based guard
    protected function getAuthUser()
    {
        return auth(config('blog.authorization.guard'))->user();
    }

    public function show($slug)
    {
        // ...

        // Check auth
        $user = $this->getAuthUser();
        if ($user instanceof \YourVendor\Blog\Contracts\BlogAuthor) {
            // Use BlogAuthorizer
            $authorizer = app(\YourVendor\Blog\Contracts\BlogAuthorizer::class);
            if (! $authorizer->canView($user, $post)) {
                abort(403);
            }
        }

        // ...
    }

    // ... other methods
}
```

### 9.2 UserBlogController (User-facing)

**Source**: `/home/yefrem/projects/freelance/app/Http/Controllers/User/BlogController.php`

**Copy to**: `src/Http/Controllers/UserBlogController.php` (RENAME)

**Changes**: Same as BlogController + rename class

```bash
cp /home/yefrem/projects/freelance/app/Http/Controllers/User/BlogController.php \
   src/Http/Controllers/UserBlogController.php
```

**Edit file**:

```php
namespace YourVendor\Blog\Http\Controllers;

class UserBlogController extends Controller // RENAMED class
{
    // Apply middleware via config
    public function __construct()
    {
        $this->middleware(config('blog.authorization.middleware.user'));
    }

    // ... rest similar to BlogController
}
```

---

## Step 10: Copy Observers

### 10.1 BlogPostCacheObserver

**Source**: `/home/yefrem/projects/freelance/app/Observers/BlogPostCacheObserver.php`

**Copy to**: `src/Observers/BlogPostCacheObserver.php`

**Changes**: Cache key prefixes

```bash
cp /home/yefrem/projects/freelance/app/Observers/BlogPostCacheObserver.php \
   src/Observers/BlogPostCacheObserver.php
```

**Edit file**:

```php
namespace YourVendor\Blog\Observers;

use Illuminate\Support\Facades\Cache;
use YourVendor\Blog\Models\BlogPost;

class BlogPostCacheObserver
{
    protected function clearCaches(BlogPost $post): void
    {
        $prefix = config('blog.cache.prefix', 'blog');
        $tags = config('blog.cache.tags', ['blog']);

        // Clear per-locale homepage caches
        foreach (config('blog.languages', ['en']) as $locale) {
            Cache::forget("{$prefix}.homepage.posts.{$locale}");
        }

        // Clear analytics cache
        if ($post->author_id) {
            Cache::forget("{$prefix}.analytics.{$post->author_id}");
        }

        // Clear popular tags
        Cache::forget("{$prefix}.popular_tags");

        // Clear tag-based cache
        if (config('blog.cache.enabled') && ! empty($tags)) {
            Cache::tags($tags)->flush();
        }
    }

    public function created(BlogPost $post): void
    {
        $this->clearCaches($post);
    }

    public function updated(BlogPost $post): void
    {
        $this->clearCaches($post);
    }

    public function deleted(BlogPost $post): void
    {
        $this->clearCaches($post);
    }
}
```

---

## Step 11: Consolidate Migrations

### 11.1 Merge All Migrations

**Goal**: Create single `create_blog_tables.php` with final schema state

**Strategy**: Take the FINAL state from all 15 migrations

**File**: `database/migrations/create_blog_tables.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $userTable = config('blog.tables.users', 'users');

        // Blog Categories
        Schema::create(config('blog.tables.blog_categories', 'blog_categories'), function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->foreignId('parent_id')->nullable()
                  ->constrained(config('blog.tables.blog_categories', 'blog_categories'))
                  ->nullOnDelete();
            $table->json('name'); // Translatable
            $table->json('description')->nullable(); // Translatable
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Blog Posts
        Schema::create(config('blog.tables.blog_posts', 'blog_posts'), function (Blueprint $table) use ($userTable) {
            $table->id();
            $table->foreignId('category_id')->nullable()
                  ->constrained(config('blog.tables.blog_categories', 'blog_categories'))
                  ->nullOnDelete();
            $table->foreignId('author_id')
                  ->constrained($userTable)
                  ->restrictOnDelete(); // From migration 2026_03_23
            $table->json('title'); // Translatable
            $table->string('slug')->unique();
            $table->json('excerpt')->nullable(); // Translatable
            $table->json('content'); // Translatable
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->string('original_locale', 5)->default('en'); // From automation fields
            $table->boolean('is_featured')->default(false); // From 2026_02_05
            $table->unsignedInteger('views_count')->default(0); // From 2025_12_08
            $table->decimal('rating_average', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->unsignedInteger('completed_orders')->default(0);

            // Automation fields (from 2026_02_16)
            $table->boolean('is_external')->default(false);
            $table->string('external_source_name')->nullable();
            $table->string('external_source_url')->nullable();
            $table->boolean('generated_by_ai')->default(false);
            $table->string('ai_model_used')->nullable();
            $table->string('generation_prompt_version')->nullable();
            $table->json('referral_metadata')->nullable(); // From 2026_02_20
            $table->boolean('is_demo')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });

        // Blog Comments
        Schema::create(config('blog.tables.blog_comments', 'blog_comments'), function (Blueprint $table) use ($userTable) {
            $table->id();
            $table->foreignId('blog_post_id')
                  ->constrained(config('blog.tables.blog_posts', 'blog_posts'))
                  ->cascadeOnDelete();
            $table->foreignId('author_id')
                  ->constrained($userTable)
                  ->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()
                  ->constrained(config('blog.tables.blog_comments', 'blog_comments'))
                  ->cascadeOnDelete();
            $table->text('content');
            $table->enum('status', ['pending', 'approved'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // Post Tags
        Schema::create(config('blog.tables.post_tags', 'post_tags'), function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('name'); // Translatable
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();
        });

        // Post-Tag Pivot
        Schema::create(config('blog.tables.post_tag_pivot', 'post_tag'), function (Blueprint $table) {
            $table->foreignId('blog_post_id')
                  ->constrained(config('blog.tables.blog_posts', 'blog_posts'))
                  ->cascadeOnDelete();
            $table->foreignId('post_tag_id')
                  ->constrained(config('blog.tables.post_tags', 'post_tags'))
                  ->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['blog_post_id', 'post_tag_id']);
        });

        // Blog Post Ratings
        Schema::create(config('blog.tables.blog_post_ratings', 'blog_post_ratings'), function (Blueprint $table) use ($userTable) {
            $table->id();
            $table->foreignId('blog_post_id')
                  ->constrained(config('blog.tables.blog_posts', 'blog_posts'))
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->constrained($userTable)
                  ->cascadeOnDelete();
            $table->tinyInteger('rating')->unsigned(); // 1-5
            $table->timestamps();
            $table->unique(['blog_post_id', 'user_id']);
        });

        // Blog Post Favorites
        Schema::create(config('blog.tables.blog_post_favorites', 'blog_post_favorites'), function (Blueprint $table) use ($userTable) {
            $table->foreignId('blog_post_id')
                  ->constrained(config('blog.tables.blog_posts', 'blog_posts'))
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->constrained($userTable)
                  ->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['blog_post_id', 'user_id']);
        });

        // Blog RSS Imports (optional, for automation)
        if (config('blog.features.automation', false)) {
            Schema::create(config('blog.tables.blog_rss_imports', 'blog_rss_imports'), function (Blueprint $table) {
                $table->id();
                $table->foreignId('blog_post_id')->unique()
                      ->constrained(config('blog.tables.blog_posts', 'blog_posts'))
                      ->cascadeOnDelete();
                $table->string('source_name');
                $table->string('source_url');
                $table->string('external_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(config('blog.tables.blog_rss_imports', 'blog_rss_imports'));
        Schema::dropIfExists(config('blog.tables.blog_post_favorites', 'blog_post_favorites'));
        Schema::dropIfExists(config('blog.tables.blog_post_ratings', 'blog_post_ratings'));
        Schema::dropIfExists(config('blog.tables.post_tag_pivot', 'post_tag'));
        Schema::dropIfExists(config('blog.tables.post_tags', 'post_tags'));
        Schema::dropIfExists(config('blog.tables.blog_comments', 'blog_comments'));
        Schema::dropIfExists(config('blog.tables.blog_posts', 'blog_posts'));
        Schema::dropIfExists(config('blog.tables.blog_categories', 'blog_categories'));
    }
};
```

**Key Points**:
- All table names use `config()`
- User table reference uses config
- Constraint updates from 2026_03_23 included (RESTRICT on author)
- Final schema state (all fields from 15 migrations)
- RSS table creation conditional on automation feature flag

---

## Step 12: Copy Views & Components

### 12.1 Copy All Views

```bash
# Copy main views
cp -r /home/yefrem/projects/freelance/resources/views/blog/* \
      resources/views/

# Copy components
mkdir -p resources/views/components
cp /home/yefrem/projects/freelance/resources/views/components/blog-cta.blade.php \
   resources/views/components/cta.blade.php
cp /home/yefrem/projects/freelance/resources/views/components/blog-social-share.blade.php \
   resources/views/components/social-share.blade.php
```

### 12.2 Update Component References

In all view files, update component references:

```bash
# Update component namespaces in views
find resources/views -type f -name '*.blade.php' -exec \
    sed -i 's/<x-blog-cta/<x-blog::cta/g' {} \;
find resources/views -type f -name '*.blade.php' -exec \
    sed -i 's/<x-blog-social-share/<x-blog::social-share/g' {} \;
```

### 12.3 Update auth() References

Views using `auth()->user()` should use injected user or check `config('blog.authorization.guard')`:

```blade
{{-- Before --}}
@auth
    @if(auth()->user()->canManageBlogPosts())
        <a href="{{ route('blog.edit', $post) }}">Edit</a>
    @endif
@endauth

{{-- After --}}
@auth(config('blog.authorization.guard'))
    @if(auth(config('blog.authorization.guard'))->user() instanceof \YourVendor\Blog\Contracts\BlogAuthor
        && auth(config('blog.authorization.guard'))->user()->canManageBlogPosts())
        <a href="{{ route(config('blog.routes.name_prefix').'edit', $post) }}">Edit</a>
    @endif
@endauth
```

---

## Step 13: Copy Filament Resources

### 13.1 Copy BlogPost Resource

```bash
# Copy resource files
cp -r /home/yefrem/projects/freelance/app/Filament/Resources/BlogPosts/* \
      src/Filament/Resources/BlogPostResource/
```

### 13.2 Update Namespaces

Update all 4 BlogPost resource files:

```bash
# Update namespace in resource
sed -i 's/namespace App\\Filament\\Resources\\BlogPosts/namespace YourVendor\\Blog\\Filament\\Resources\\BlogPostResource/g' \
    src/Filament/Resources/BlogPostResource/*.php

# Update model references
sed -i 's/use App\\Models\\BlogPost/use YourVendor\\Blog\\Models\\BlogPost/g' \
    src/Filament/Resources/BlogPostResource/*.php
```

### 13.3 Copy BlogCategory Resource

```bash
# Copy
cp -r /home/yefrem/projects/freelance/app/Filament/Resources/BlogCategories/* \
      src/Filament/Resources/BlogCategoryResource/

# Update namespaces
sed -i 's/namespace App\\Filament\\Resources\\BlogCategories/namespace YourVendor\\Blog\\Filament\\Resources\\BlogCategoryResource/g' \
    src/Filament/Resources/BlogCategoryResource/*.php

sed -i 's/use App\\Models\\BlogCategory/use YourVendor\\Blog\\Models\\BlogCategory/g' \
    src/Filament/Resources/BlogCategoryResource/*.php
```

---

## Step 14: Create Configuration Files

### 14.1 Core Config

**File**: `config/blog.php`

(Copy the full config structure from plan file's section 6.1)

```php
<?php

return [
    // Models
    'models' => [
        'blog_post' => \YourVendor\Blog\Models\BlogPost::class,
        'blog_category' => \YourVendor\Blog\Models\BlogCategory::class,
        'blog_comment' => \YourVendor\Blog\Models\BlogComment::class,
        'post_tag' => \YourVendor\Blog\Models\PostTag::class,
        'blog_post_rating' => \YourVendor\Blog\Models\BlogPostRating::class,
        'user' => \App\Models\User::class, // Consuming app's model
    ],

    // Tables
    'tables' => [
        'blog_posts' => 'blog_posts',
        'blog_categories' => 'blog_categories',
        'blog_comments' => 'blog_comments',
        'post_tags' => 'post_tags',
        'post_tag_pivot' => 'post_tag',
        'blog_post_ratings' => 'blog_post_ratings',
        'blog_post_favorites' => 'blog_post_favorites',
        'blog_rss_imports' => 'blog_rss_imports',
        'users' => 'users',
    ],

    // Features
    'features' => [
        'comments' => true,
        'ratings' => true,
        'favorites' => true,
        'categories' => true,
        'tags' => true,
        'media' => true,
        'rss_feeds' => false,
        'automation' => false,
    ],

    // Authorization
    'authorization' => [
        'guard' => 'web',
        'middleware' => [
            'public' => ['web'],
            'user' => ['web', 'auth', 'verified'],
        ],
        'authorizer' => \YourVendor\Blog\Services\DefaultBlogAuthorizer::class,
    ],

    // Content Sanitization
    'sanitizer' => \YourVendor\Blog\Services\DefaultContentSanitizer::class,

    // Translation
    'languages' => ['en', 'uk', 'de', 'fr', 'es'],
    'default_locale' => 'en',
    'translation' => [
        'provider' => null,
        'enabled' => false,
        'queue' => true,
        'delay_seconds' => 5,
    ],

    // Routing
    'routes' => [
        'prefix' => 'blog',
        'name_prefix' => 'blog.',
        'middleware' => ['web'],
    ],

    // Pagination
    'pagination' => [
        'per_page' => 15,
        'featured_count' => 3,
    ],

    // Cache
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
        'prefix' => 'blog',
        'tags' => ['blog'],
    ],
];
```

### 14.2 Automation Config

**File**: `config/blog-automation.php`

```bash
# Copy from source
cp /home/yefrem/projects/freelance/config/blog-automation.php \
   config/blog-automation.php

# No changes needed (just references, not PlatformSetting usage)
```

---

## Step 15: Create Service Provider

**File**: `src/BlogServiceProvider.php`

(This is a large, critical file - ~200 lines)

```php
<?php

declare(strict_types=1);

namespace YourVendor\Blog;

use Illuminate\Support\ServiceProvider;
use YourVendor\Blog\Contracts\BlogAuthorizer;
use YourVendor\Blog\Contracts\BlogTranslationProvider;
use YourVendor\Blog\Contracts\ContentSanitizer;
use YourVendor\Blog\Models\BlogPost;
use YourVendor\Blog\Observers\BlogPostCacheObserver;
use YourVendor\Blog\Services\DefaultBlogAuthorizer;
use YourVendor\Blog\Services\DefaultContentSanitizer;

class BlogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge configurations
        $this->mergeConfigFrom(__DIR__.'/../config/blog.php', 'blog');
        $this->mergeConfigFrom(__DIR__.'/../config/blog-automation.php', 'blog-automation');

        // Bind interfaces
        $this->app->singleton(ContentSanitizer::class, function ($app) {
            return $app->make(config('blog.sanitizer', DefaultContentSanitizer::class));
        });

        $this->app->singleton(BlogAuthorizer::class, function ($app) {
            return $app->make(config('blog.authorization.authorizer', DefaultBlogAuthorizer::class));
        });

        // Translation provider (if configured)
        if (config('blog.translation.provider')) {
            $this->app->singleton(BlogTranslationProvider::class, function ($app) {
                return $app->make(config('blog.translation.provider'));
            });
        }
    }

    public function boot(): void
    {
        // Publish configurations
        $this->publishes([
            __DIR__.'/../config/blog.php' => config_path('blog.php'),
        ], ['blog-config', 'config']);

        $this->publishes([
            __DIR__.'/../config/blog-automation.php' => config_path('blog-automation.php'),
        ], ['blog-automation-config', 'config']);

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], ['blog-migrations', 'migrations']);

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/blog'),
        ], ['blog-views', 'views']);

        // Publish language files
        $this->publishes([
            __DIR__.'/../resources/lang' => lang_path('vendor/blog'),
        ], ['blog-lang', 'lang']);

        // Load package routes
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/../routes/blog.php');
        }

        // Load package views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'blog');

        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'blog');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \YourVendor\Blog\Console\Commands\BlogInstallCommand::class,
            ]);
        }

        // Register observers
        if (config('blog.cache.enabled')) {
            BlogPost::observe(BlogPostCacheObserver::class);
        }

        // Boot Filament resources (if Filament installed)
        if (class_exists('\\Filament\\Facades\\Filament')) {
            $this->bootFilament();
        }
    }

    protected function bootFilament(): void
    {
        // Auto-discover Filament resources if package is installed
        \Filament\Facades\Filament::serving(function () {
            \Filament\Facades\Filament::registerResources([
                \YourVendor\Blog\Filament\Resources\BlogPostResource::class,
                \YourVendor\Blog\Filament\Resources\BlogCategoryResource::class,
            ]);
        });
    }
}
```

---

## Step 16: Create Routes

**File**: `routes/blog.php`

**Extract from**: `/home/yefrem/projects/freelance/routes/web.php` (blog section)

```php
<?php

use Illuminate\Support\Facades\Route;
use YourVendor\Blog\Http\Controllers\BlogController;
use YourVendor\Blog\Http\Controllers\UserBlogController;

$prefix = config('blog.routes.prefix', 'blog');
$namePrefix = config('blog.routes.name_prefix', 'blog.');
$middleware = config('blog.routes.middleware', ['web']);

// Public blog routes
Route::prefix($prefix)
    ->name($namePrefix)
    ->middleware($middleware)
    ->group(function () {
        Route::get('/', [BlogController::class, 'index'])->name('index');
        Route::get('/category/{slug}', [BlogController::class, 'category'])->name('category');
        Route::get('/tag/{slug}', [BlogController::class, 'tag'])->name('tag');
        Route::get('/{slug}', [BlogController::class, 'show'])->name('show');
    });

// Authenticated user routes (my posts, create, edit)
Route::prefix($prefix)
    ->name($namePrefix)
    ->middleware(config('blog.authorization.middleware.user'))
    ->group(function () {
        Route::get('/my-posts', [UserBlogController::class, 'myPosts'])->name('my-posts');
        Route::get('/create', [UserBlogController::class, 'create'])->name('create');
        Route::post('/', [UserBlogController::class, 'store'])->name('store');
        Route::get('/{post}/edit', [UserBlogController::class, 'edit'])->name('edit');
        Route::put('/{post}', [UserBlogController::class, 'update'])->name('update');
        Route::delete('/{post}', [UserBlogController::class, 'destroy'])->name('destroy');

        // Interactions
        Route::post('/{post}/rate', [BlogController::class, 'rate'])->name('rate');
        Route::post('/{post}/favorite', [BlogController::class, 'toggleFavorite'])->name('favorite');
        Route::post('/{post}/comment', [BlogController::class, 'storeComment'])->name('comment');

        // Image upload for editor
        Route::post('/upload-image', [UserBlogController::class, 'uploadImage'])->name('upload-image');
    });
```

---

## Step 17: Create Seeder

**File**: `database/seeders/BlogSeeder.php`

**Source**: `/home/yefrem/projects/freelance/database/seeders/BlogPostSeeder.php` (adapt)

```php
<?php

namespace YourVendor\Blog\Database\Seeders;

use Illuminate\Database\Seeder;
use YourVendor\Blog\Models\BlogCategory;
use YourVendor\Blog\Models\BlogPost;
use YourVendor\Blog\Models\PostTag;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        // Check if User model exists and has records
        $userModel = config('blog.models.user');
        $userCount = $userModel::count();

        if ($userCount === 0) {
            $this->command->warn('No users found. Please create users before seeding blog.');
            return;
        }

        $this->seedCategories();
        $this->seedTags();
        $this->seedPosts();
    }

    protected function seedCategories(): void
    {
        $categories = [
            ['name' => ['en' => 'Technology', 'uk' => 'Технології'], 'slug' => 'technology'],
            ['name' => ['en' => 'Business', 'uk' => 'Бізнес'], 'slug' => 'business'],
            ['name' => ['en' => 'Lifestyle', 'uk' => 'Стиль життя'], 'slug' => 'lifestyle'],
        ];

        foreach ($categories as $category) {
            BlogCategory::updateOrCreate(
                ['slug' => $category['slug']],
                ['name' => $category['name'], 'is_active' => true]
            );
        }

        $this->command->info('Blog categories seeded.');
    }

    protected function seedTags(): void
    {
        $tags = [
            ['name' => ['en' => 'Laravel', 'uk' => 'Laravel'], 'slug' => 'laravel'],
            ['name' => ['en' => 'PHP', 'uk' => 'PHP'], 'slug' => 'php'],
            ['name' => ['en' => 'JavaScript', 'uk' => 'JavaScript'], 'slug' => 'javascript'],
        ];

        foreach ($tags as $tag) {
            PostTag::updateOrCreate(
                ['slug' => $tag['slug']],
                ['name' => $tag['name']]
            );
        }

        $this->command->info('Blog tags seeded.');
    }

    protected function seedPosts(): void
    {
        $userModel = config('blog.models.user');
        $firstUser = $userModel::first();

        $categories = BlogCategory::all();
        $tags = PostTag::all();

        foreach (range(1, 10) as $i) {
            $post = BlogPost::create([
                'author_id' => $firstUser->id,
                'category_id' => $categories->random()->id,
                'title' => ['en' => "Sample Post {$i}", 'uk' => "Приклад Публікації {$i}"],
                'excerpt' => ['en' => "This is excerpt {$i}", 'uk' => "Це витяг {$i}"],
                'content' => ['en' => "<p>This is the content of post {$i}</p>", 'uk' => "<p>Це вміст публікації {$i}</p>"],
                'status' => 'published',
                'published_at' => now()->subDays(rand(1, 30)),
                'is_featured' => $i <= 3,
            ]);

            // Attach random tags
            $post->tags()->attach($tags->random(rand(1, 3))->pluck('id'));
        }

        $this->command->info('Blog posts seeded.');
    }
}
```

---

## Step 18: Create Artisan Command

**File**: `src/Console/Commands/BlogInstallCommand.php`

```php
<?php

declare(strict_types=1);

namespace YourVendor\Blog\Console\Commands;

use Illuminate\Console\Command;

class BlogInstallCommand extends Command
{
    protected $signature = 'blog:install
                            {--migrations : Publish migrations}
                            {--views : Publish views}
                            {--config : Publish configuration}
                            {--seed : Run seeder}
                            {--all : Publish everything and seed}';

    protected $description = 'Install the Laravel Multilingual Blog package';

    public function handle(): int
    {
        $this->info('Installing Laravel Multilingual Blog...');

        if ($this->option('all')) {
            $this->publishAll();
            $this->runSeeder();
        } else {
            if ($this->option('migrations')) {
                $this->publishMigrations();
            }

            if ($this->option('views')) {
                $this->publishViews();
            }

            if ($this->option('config')) {
                $this->publishConfig();
            }

            if ($this->option('seed')) {
                $this->runSeeder();
            }

            if (! $this->option('migrations') && ! $this->option('views')
                && ! $this->option('config') && ! $this->option('seed')) {
                $this->askWhatToInstall();
            }
        }

        $this->info('Blog installation complete!');
        $this->showNextSteps();

        return Command::SUCCESS;
    }

    protected function publishAll(): void
    {
        $this->publishMigrations();
        $this->publishViews();
        $this->publishConfig();
    }

    protected function publishMigrations(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'blog-migrations',
            '--force' => true,
        ]);
        $this->info('✓ Migrations published');
    }

    protected function publishViews(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'blog-views',
            '--force' => true,
        ]);
        $this->info('✓ Views published');
    }

    protected function publishConfig(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'blog-config',
            '--force' => true,
        ]);
        $this->call('vendor:publish', [
            '--tag' => 'blog-automation-config',
            '--force' => true,
        ]);
        $this->info('✓ Configuration files published');
    }

    protected function runSeeder(): void
    {
        if ($this->confirm('Run blog seeder?', true)) {
            $this->call('db:seed', [
                '--class' => 'YourVendor\\Blog\\Database\\Seeders\\BlogSeeder',
            ]);
            $this->info('✓ Blog seeded');
        }
    }

    protected function askWhatToInstall(): void
    {
        $choices = $this->choice(
            'What would you like to publish?',
            ['migrations', 'views', 'config', 'all'],
            3, // default 'all'
            null,
            true // multiple
        );

        if (in_array('all', $choices)) {
            $this->publishAll();
        } else {
            if (in_array('migrations', $choices)) {
                $this->publishMigrations();
            }
            if (in_array('views', $choices)) {
                $this->publishViews();
            }
            if (in_array('config', $choices)) {
                $this->publishConfig();
            }
        }

        $this->runSeeder();
    }

    protected function showNextSteps(): void
    {
        $this->newLine();
        $this->line('Next steps:');
        $this->line('1. Add BlogAuthor interface to your User model');
        $this->line('2. Add HasBlogPosts trait to your User model');
        $this->line('3. Run migrations: php artisan migrate');
        $this->line('4. Configure blog settings in config/blog.php');
        $this->newLine();
        $this->line('Documentation: https://github.com/yourvendor/laravel-multilingual-blog');
    }
}
```

---

## Step 19: Testing Setup

### 19.1 Create TestCase

**File**: `tests/TestCase.php`

```php
<?php

namespace YourVendor\Blog\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;
use YourVendor\Blog\BlogServiceProvider;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            BlogServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
```

### 19.2 Create Sample Tests

**File**: `tests/Feature/BlogPostTest.php`

```php
<?php

namespace YourVendor\Blog\Tests\Feature;

use YourVendor\Blog\Models\BlogPost;
use YourVendor\Blog\Tests\TestCase;

class BlogPostTest extends TestCase
{
    public function test_can_create_blog_post(): void
    {
        $post = BlogPost::create([
            'author_id' => 1,
            'title' => ['en' => 'Test Post'],
            'excerpt' => ['en' => 'Test Excerpt'],
            'content' => ['en' => '<p>Test Content</p>'],
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('blog_posts', [
            'id' => $post->id,
        ]);
    }
}
```

### 19.3 Configure PHPUnit

**File**: `phpunit.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="YourVendor Test Suite">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="DB_CONNECTION" value="testing"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
    </php>
</phpunit>
```

---

## Step 20: Documentation

### 20.1 Comprehensive README.md

**File**: `README.md` (expand from basic version)

```markdown
# Laravel Multilingual Blog

Complete multilingual blog system with AI content generation, RSS imports, and Filament admin panel.

## Features

- **Multilingual Support**: 5 languages (EN, UK, DE, FR, ES) with Spatie Translatable
- **Content Automation**: AI generation (Claude API) + RSS feed imports
- **Admin Panel**: Full Filament v4 integration
- **User Engagement**: Comments, ratings, favorites, analytics
- **SEO Friendly**: Auto-slugs, RSS feeds, meta support
- **Media Management**: Featured images with conversions
- **Flexible**: Configurable models, tables, authorization

## Requirements

- PHP 8.2+
- Laravel 11.0|12.0
- PostgreSQL/MySQL (recommended for JSON fields)

## Installation

```bash
composer require yourvendor/laravel-multilingual-blog
```

### Publish Assets

```bash
# Install everything
php artisan blog:install --all

# Or selectively:
php artisan blog:install --migrations --views --config
```

### Configure

Add to your User model:

```php
use YourVendor\Blog\Contracts\BlogAuthor;
use YourVendor\Blog\Traits\HasBlogPosts;

class User extends Authenticatable implements BlogAuthor
{
    use HasBlogPosts;

    public function canManageBlogPosts(): bool
    {
        return $this->is_admin; // Your logic
    }

    // Required by BlogAuthor interface
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
}
```

Run migrations:

```bash
php artisan migrate
```

### Optional: Configure Translation

If using automated translations:

```bash
composer require yourvendor/laravel-deepl-translations
```

In `config/blog.php`:

```php
'translation' => [
    'provider' => \YourVendor\Blog\Services\DeepLTranslationAdapter::class,
    'enabled' => true,
    'queue' => true,
],
```

## Usage

### Public Routes

- `GET /blog` - Blog index
- `GET /blog/{slug}` - Single post
- `GET /blog/category/{slug}` - Filter by category
- `GET /blog/tag/{slug}` - Filter by tag

### User Routes (Authenticated)

- `GET /blog/my-posts` - User's posts dashboard
- `GET /blog/create` - Create post form
- `POST /blog` - Store post
- `GET /blog/{post}/edit` - Edit form
- `PUT /blog/{post}` - Update post
- `DELETE /blog/{post}` - Delete post

### Admin Panel

Access Filament at `/admin` - blog resources auto-registered if Filament installed.

## Configuration

See `config/blog.php` for all options:

- Table names
- Model classes
- Feature flags (comments, ratings, automation)
- Authorization logic
- Cache settings
- Translation settings

## Customization

### Custom HTML Sanitizer

```php
// Create custom sanitizer
class MyHtmlSanitizer implements \YourVendor\Blog\Contracts\ContentSanitizer
{
    public function sanitizeHtml(string $html): string
    {
        return \Mews\Purifier\Facades\Purifier::clean($html);
    }
    // ...
}

// Register in config/blog.php
'sanitizer' => \App\Services\MyHtmlSanitizer::class,
```

### Custom Authorization

```php
// Create custom authorizer
class MyBlogAuthorizer implements \YourVendor\Blog\Contracts\BlogAuthorizer
{
    public function canUpdate(BlogAuthor $user, object $post): bool
    {
        return $user->hasRole('editor') || $post->author_id === $user->getId();
    }
    // ...
}

// Register
'authorization' => [
    'authorizer' => \App\Services\MyBlogAuthorizer::class,
],
```

## Events

Listen to blog events in your app:

```php
// In EventServiceProvider
use YourVendor\Blog\Events\BlogPostPublished;

Event::listen(BlogPostPublished::class, function ($event) {
    // Send notifications, update cache, etc.
});
```

Available events:
- `BlogPostCreated`
- `BlogPostUpdated`
- `BlogPostPublished`
- `BlogPostDeleted`
- `BlogCommentPosted`
- `BlogCommentApproved`

## Testing

```bash
composer test
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md)

## License

MIT License. See [LICENSE](LICENSE) for details.

## Credits

- Inspired by the blog system in `/home/yefrem/projects/freelance`
- Uses Spatie packages (Translatable, Sluggable, MediaLibrary)
- Filament admin panel integration

## Support

- Documentation: [Full docs](/docs)
- Issues: [GitHub Issues](https://github.com/yourvendor/laravel-multilingual-blog/issues)
```

### 20.2 Create CHANGELOG.md

**File**: `CHANGELOG.md`

```markdown
# Changelog

All notable changes to `laravel-multilingual-blog` will be documented in this file.

## 1.0.0 - 2026-04-06

### Initial Release

- Full-featured blog system
- Multilingual support (5 languages)
- AI content generation (Claude API)
- RSS feed imports
- Filament v4 admin panel
- Comments, ratings, favorites
- SEO optimization
- Extensive configuration options
```

---

## Step 21: Package Publishing

### 21.1 Composer Validation

```bash
composer validate
```

Fix any issues reported.

### 21.2 Git Setup

```bash
git add .
git commit -m "Initial package release v1.0.0"
git tag v1.0.0
```

### 21.3 Packagist Submission

1. Push to GitHub/GitLab
2. Submit to packagist.org
3. Verify package installable: `composer require yourvendor/laravel-multilingual-blog`

---

## Verification Checklist

### Code Quality

- [ ] All files have correct namespaces
- [ ] No `App\` references remain
- [ ] All `config()` calls use correct keys
- [ ] PHPStan passes (run `./vendor/bin/phpstan analyse`)
- [ ] Pint formatting applied (run `./vendor/bin/pint`)
- [ ] No syntax errors (run `find src -name '*.php' -exec php -l {} \;`)

### Functionality

- [ ] Migrations run successfully
- [ ] Seeder creates sample data
- [ ] Routes registered correctly
- [ ] Views render without errors
- [ ] Filament resources load (if Filament installed)
- [ ] Events fire correctly
- [ ] Cache observer works
- [ ] User model integration functional

### Configuration

- [ ] All config values have defaults
- [ ] Table names configurable
- [ ] Model classes configurable
- [ ] Feature flags work
- [ ] Translation provider interface works
- [ ] Sanitizer interface works
- [ ] Authorizer interface works

### Documentation

- [ ] README.md complete
- [ ] Installation instructions clear
- [ ] Configuration examples provided
- [ ] Customization guide included
- [ ] Event documentation complete
- [ ] CHANGELOG.md created

### Testing

- [ ] Test suite runs (` composer test`)
- [ ] Sample tests pass
- [ ] Package installable in fresh Laravel app
- [ ] User model integration tested

---

## Common Issues & Solutions

### Issue 1: Namespace Conflicts

**Problem**: `Class 'App\Models\BlogPost' not found`

**Solution**: Search and replace all remaining `App\` namespace references

```bash
grep -r "use App\\" src/
# Fix any matches
```

### Issue 2: Config Not Loading

**Problem**: `config('blog.models.blog_post')` returns null

**Solution**: Ensure `mergeConfigFrom()` called in service provider `register()` method

### Issue 3: Views Not Found

**Problem**: View `blog::index` not found

**Solution**: Check `loadViewsFrom()` in service provider:

```php
$this->loadViewsFrom(__DIR__.'/../resources/views', 'blog');
```

### Issue 4: Migrations Fail

**Problem**: Foreign key constraint fails for `author_id`

**Solution**: Ensure users table exists before running blog migrations, or adjust constraint

### Issue 5: Translation Provider Not Working

**Problem**: `BlogTranslationProvider not bound in container`

**Solution**: Ensure provider configured in `config/blog.php`:

```php
'translation' => [
    'provider' => \YourVendor\Blog\Services\DeepLTranslationAdapter::class,
    'enabled' => true,
],
```

---

## Final Notes

**Total Implementation Time**: 40-60 hours for complete package

**Most Complex Steps**:
1. Step 7: BlogContentOrchestrator refactoring (5-8 hours)
2. Step 11: Migration consolidation (3-4 hours)
3. Step 15: Service provider implementation (2-3 hours)

**Recommended Order**:
Follow steps 1-21 sequentially. Don't skip ahead - each step builds on previous ones.

**Testing Strategy**:
- Test incrementally after Steps 4, 9, 15, and 21
- Install in fresh Laravel app to verify integration
- Check all routes, views, and admin panel

---

**Document End** | Total Lines: ~1,500 | Version 1.0
