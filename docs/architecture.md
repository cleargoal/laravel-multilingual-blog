# Package Architecture

## Overview
Multilingual Laravel blog package with Filament admin panel, following clean architecture principles with Actions, Services, and Observers.

## Layer Structure

### 1. Models (`src/Models/`)
Eloquent models with relationships, scopes, and basic business logic.
- See `models.json` for detailed structure
- All use `author_id` foreign key (not `user_id`)
- Translatable fields use Spatie Translatable
- Soft deletes on BlogPost and BlogComment

### 2. Actions (`src/Actions/Blog/`)
Business logic layer - single-purpose action classes:
- `GetBlogIndexData` - Fetch posts for index page with filters
- `GetBlogPostForShow` - Fetch single post with related data
- `GetPopularPosts` - Fetch most viewed posts
- `GetPopularTags` - Fetch trending tags
- `GetBlogAnalytics` - Fetch analytics data
- `SyncPostTags` - Sync tags for a post

**Pattern**: Each action has single `execute()` method.

### 3. Services (`src/Services/`)
Infrastructure services implementing contracts:
- `DefaultContentSanitizer` - HTML sanitization
- `DefaultBlogAuthorizer` - Authorization logic

**Customizable**: Configure alternative implementations in `config/blog.php`

### 4. Observers (`src/Observers/`)
Event dispatching on model changes:
- `BlogPostObserver` - Fires BlogPostCreated, BlogPostUpdated, BlogPostPublished, BlogPostDeleted
- `BlogCommentObserver` - Fires BlogCommentPosted, BlogCommentApproved
- `BlogPostCacheObserver` - Cache invalidation (if enabled)

### 5. Contracts (`src/Contracts/`)
Interfaces for dependency injection:
- `BlogAuthor` - User model must implement
- `BlogAuthorizer` - Authorization service
- `ContentSanitizer` - HTML sanitization service
- `BlogTranslationProvider` - Optional translation service

### 6. Traits (`src/Traits/`)
Reusable functionality:
- `HasBlogPosts` - Add to User model for blog relationships
- `HasTranslationFallback` - Fallback to default locale

### 7. Controllers (`src/Http/Controllers/`)
- `BlogController` - Public blog pages and actions
- `UserBlogController` - User dashboard for managing posts

### 8. Filament Resources (`src/Filament/Resources/`)
Admin panel integration:
- `BlogPostResource` - CRUD for blog posts
- `BlogCategoryResource` - CRUD for categories

## Data Flow

### Public Blog Post View
```
Route → BlogController@show
  → GetBlogPostForShow::execute($slug)
    → BlogPost::where('slug', $slug)->with(['author', 'category', 'tags'])->first()
    → Increment views_count
    → Fetch related posts, comments, ratings
  → Return view with data
```

### Creating a Blog Post
```
Form Submission → BlogController@store
  → Validate input
  → BlogPost::create([...])
  → Save featured image (Spatie MediaLibrary)
  → SyncPostTags::execute($post, $tags)
  → TranslateBlogPostJob::dispatch($post)
  → Observer fires BlogPostCreated event
  → Redirect with success message
```

## Configuration

### Main Config (`config/blog.php`)
```php
[
    'models' => [
        'user' => App\Models\User::class,
        'blog_post' => YourVendor\Blog\Models\BlogPost::class,
        // ...
    ],
    'sanitizer' => DefaultContentSanitizer::class,
    'authorization' => [
        'authorizer' => DefaultBlogAuthorizer::class,
    ],
    'translation' => [
        'provider' => null, // Optional
    ],
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
    ],
]
```

### Automation Config (`config/blog-automation.php`)
RSS imports, AI generation, scheduled tasks.

## Routes (`routes/blog.php`)

**Public Routes** (middleware: 'web'):
- `GET /blog` - Index
- `GET /blog/{slug}` - Show post
- `GET /blog/category/{slug}` - Category filter
- `GET /blog/tag/{slug}` - Tag filter

**Authenticated Routes** (middleware: 'web', 'auth', 'verified'):
- `GET /blog/my-posts` - User's posts
- `GET /blog/favorites` - User's favorites
- `POST /blog/{post}/rate` - Rate post
- `POST /blog/{post}/favorite` - Toggle favorite
- `POST /blog/{post}/comments` - Post comment
- Full CRUD for posts

## Database Schema

**Tables**:
1. `blog_categories` - Categories with parent_id for hierarchy
2. `blog_posts` - Main content table
3. `post_tags` - Tags with usage_count
4. `post_tag` - Pivot table
5. `blog_comments` - Comments with parent_id for threading
6. `blog_post_ratings` - 1-5 star ratings
7. `blog_post_favorites` - User favorites
8. `blog_rss_imports` - RSS import tracking

**Foreign Key Constraints**:
- `author_id` → users (RESTRICT on delete)
- `category_id` → blog_categories (SET NULL on delete)
- `parent_id` → blog_comments (CASCADE on delete)

## Testing Architecture

### TestCase Setup
- SQLite in-memory database
- Foreign keys enabled: `PRAGMA foreign_keys = ON`
- Migrations run manually in `setUp()`
- TestUser implements BlogAuthor + Authenticatable

### Test Categories
1. **Unit Tests** - Models, Actions, Services, Scopes, Traits
2. **Feature Tests** - Controllers, Commands, Seeders, Events

See `testing.yaml` for detailed breakdown.

## Key Patterns

### 1. Configuration-Based Models
Never hardcode model class names:
```php
// Good ✓
$blogPostModel = config('blog.models.blog_post', BlogPost::class);
$posts = $blogPostModel::published()->get();

// Bad ✗
$posts = BlogPost::published()->get();
```

### 2. Translation Access
```php
// In views/runtime
$post->title  // Current locale

// In tests/assertions
$post->getTranslations('title')  // ['en' => 'Title', 'uk' => 'Заголовок']
```

### 3. Authorization
```php
$authorizer = app(BlogAuthorizer::class);
if (!$authorizer->canDelete($user, $post)) {
    abort(403);
}
```

### 4. Sanitization
```php
$sanitizer = app(ContentSanitizer::class);
$clean = $sanitizer->sanitizeHtml($html);  // Removes dangerous tags/attributes
$plain = $sanitizer->stripAllTags($html);  // Removes all HTML
```

## Events

- `BlogPostCreated` - New post created
- `BlogPostUpdated` - Post updated
- `BlogPostPublished` - Status changed to published
- `BlogPostDeleted` - Post deleted
- `BlogCommentPosted` - New comment
- `BlogCommentApproved` - Comment approved

All fired automatically via Observers.

## Extensibility

### Custom Sanitizer
```php
// 1. Implement ContentSanitizer interface
class MyCustomSanitizer implements ContentSanitizer { ... }

// 2. Configure in blog.php
'sanitizer' => MyCustomSanitizer::class
```

### Custom Authorizer
```php
// 1. Implement BlogAuthorizer interface
class MyCustomAuthorizer implements BlogAuthorizer { ... }

// 2. Configure in blog.php
'authorization' => ['authorizer' => MyCustomAuthorizer::class]
```

### Translation Provider
```php
// 1. Implement BlogTranslationProvider interface
class DeepLTranslator implements BlogTranslationProvider { ... }

// 2. Configure in blog.php
'translation' => ['provider' => DeepLTranslator::class]
```
