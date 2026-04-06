# Blog Package: Architecture & File Inventory
## Complete Reference for Package Creation

**Package Name**: `yourvendor/laravel-multilingual-blog`
**Source Project**: `/home/yefrem/projects/freelance`
**Documentation Version**: 1.0
**Last Updated**: 2026-04-06

---

## Table of Contents

1. [Package Architecture Overview](#1-package-architecture-overview)
2. [Complete File Inventory](#2-complete-file-inventory)
3. [File Categorization](#3-file-categorization)
4. [Namespace Mapping](#4-namespace-mapping)
5. [Dependency Analysis](#5-dependency-analysis)
6. [Configuration Strategy](#6-configuration-strategy)
7. [Quick Reference Tables](#7-quick-reference-tables)

---

## 1. Package Architecture Overview

### 1.1 Package Purpose

This package provides a complete multilingual blog system for Laravel applications, including:

- **Core Blog Features**: Posts, categories, tags, comments, ratings, favorites
- **Multilingual Support**: 5 languages (EN, UK, DE, FR, ES) via Spatie Translatable
- **Content Automation**: AI generation (Claude API) + RSS imports
- **Admin Panel**: Filament v4 resources for blog management
- **Media Management**: Featured images with conversions via Spatie MediaLibrary
- **Translation Integration**: Works with separate DeepL key rotation package
- **SEO Features**: Slugs, RSS feeds, sitemap integration
- **User Engagement**: Comments, ratings, favorites, analytics

### 1.2 Package Structure

```
yourvendor/laravel-multilingual-blog/
├── src/                                    # Package source code
│   ├── BlogServiceProvider.php            # Main service provider
│   ├── Models/                             # Eloquent models (5 models)
│   ├── Http/Controllers/                   # Public + user controllers
│   ├── Actions/Blog/                       # Business logic (6 actions)
│   ├── Jobs/                               # Queue jobs (2 jobs)
│   ├── Services/                           # Service classes (6 services)
│   ├── Observers/                          # Model observers (1 observer)
│   ├── Contracts/                          # Interfaces (4 interfaces)
│   ├── Traits/                             # Reusable traits (2 traits)
│   ├── Events/                             # Domain events (6 events)
│   └── Console/Commands/                   # Artisan commands (1 command)
├── database/
│   ├── migrations/                         # Database schema (1 consolidated file)
│   └── seeders/                            # Database seeders (1 seeder)
├── resources/
│   ├── views/                              # Blade templates (7 views + 2 components)
│   └── lang/en/                            # Translation strings
├── routes/
│   └── blog.php                            # Package routes
├── config/
│   ├── blog.php                            # Core configuration
│   └── blog-automation.php                 # AI/RSS automation config
├── tests/
│   ├── Feature/                            # Feature tests
│   └── Unit/                               # Unit tests
├── composer.json                           # Dependencies
├── README.md                               # Installation & usage guide
└── LICENSE                                 # MIT license

```

### 1.3 Design Principles

**Abstraction Layer**:
- User model integration via `BlogAuthor` interface
- HTML sanitization via `ContentSanitizer` interface
- Authorization via `BlogAuthorizer` interface
- Translation via `BlogTranslationProvider` interface

**Configuration-Driven**:
- All table names configurable
- All model classes configurable
- Feature flags for optional functionality
- Cache settings customizable

**Event-Driven**:
- `BlogPostCreated`, `BlogPostPublished`, `BlogPostDeleted` events
- Allows consuming applications to hook into blog lifecycle
- Replaces hardcoded `PlatformSetting` integration from source

**Dependency Injection**:
- All services use constructor injection
- Interfaces bound in service provider
- Testable with mocks/fakes

---

## 2. Complete File Inventory

### 2.1 Source Files Summary

**Total Files from Source Project**: ~75 files

**Breakdown**:
- 5 Models
- 2 Controllers (Public + User)
- 6 Action Classes (1 skipped: TrackServiceLinkClick)
- 2 Jobs
- 4 Services (+ 2 new default implementations)
- 1 Observer
- 8 Filament Resources (4 BlogPost + 4 BlogCategory)
- 15 Migrations (consolidate to 1)
- 1 Seeder
- 9 Views (7 main + 2 components)
- 1 Config File
- 4 Routes (extracted from web.php)

**New Files to Create**: ~20 files
- 1 Service Provider
- 4 Interfaces
- 2 Traits
- 2 Default Service Implementations
- 6 Events
- 1 Command
- 4 Package files (composer.json, README, LICENSE, .gitattributes)

**Total Package Files**: ~95 files

### 2.2 File Inventory by Type

#### Models (5 files)

| File | Source Path | Package Path | Changes Required |
|------|-------------|--------------|------------------|
| BlogPost | `app/Models/BlogPost.php` | `src/Models/BlogPost.php` | Namespace, user → author, interfaces |
| BlogCategory | `app/Models/BlogCategory.php` | `src/Models/BlogCategory.php` | Namespace only |
| BlogComment | `app/Models/BlogComment.php` | `src/Models/BlogComment.php` | Namespace, user → author |
| PostTag | `app/Models/PostTag.php` | `src/Models/PostTag.php` | Namespace only |
| BlogPostRating | `app/Models/BlogPostRating.php` | `src/Models/BlogPostRating.php` | Namespace, user → author |

**Skipped Models**:
- `BlogPostServiceLink.php` - Service offering coupling, already removed in migration

#### Controllers (2 files)

| File | Source Path | Package Path | Changes Required |
|------|-------------|--------------|------------------|
| BlogController (Public) | `app/Http/Controllers/Public/BlogController.php` | `src/Http/Controllers/BlogController.php` | Namespace, auth() guards, authorization |
| BlogController (User) | `app/Http/Controllers/User/BlogController.php` | `src/Http/Controllers/UserBlogController.php` | Namespace, auth(), authorization, rename |

#### Actions (6 files, 1 skipped)

| File | Source Path | Package Path | Changes Required |
|------|-------------|--------------|------------------|
| GetBlogIndexData | `app/Actions/Blog/GetBlogIndexData.php` | `src/Actions/Blog/GetBlogIndexData.php` | Namespace only |
| GetBlogPostForShow | `app/Actions/Blog/GetBlogPostForShow.php` | `src/Actions/Blog/GetBlogPostForShow.php` | Namespace only |
| GetPopularPosts | `app/Actions/Blog/GetPopularPosts.php` | `src/Actions/Blog/GetPopularPosts.php` | Namespace, cache keys |
| GetPopularTags | `app/Actions/Blog/GetPopularTags.php` | `src/Actions/Blog/GetPopularTags.php` | Namespace, cache keys |
| SyncPostTags | `app/Actions/Blog/SyncPostTags.php` | `src/Actions/Blog/SyncPostTags.php` | Namespace only |
| GetBlogPostsForHomepage | `app/Actions/Blog/GetBlogPostsForHomepage.php` | `src/Actions/Blog/GetBlogPostsForHomepage.php` | Namespace, cache keys |

**Skipped Actions**:
- `TrackServiceLinkClick.php` - Service offering specific, not needed in package

#### Jobs (2 files)

| File | Source Path | Package Path | Changes Required |
|------|-------------|--------------|------------------|
| TranslateBlogPostJob | `app/Jobs/TranslateBlogPostJob.php` | `src/Jobs/TranslateBlogPostJob.php` | Namespace, translation service abstraction |
| GenerateAIBlogPostJob | `app/Jobs/GenerateAIBlogPostJob.php` | `src/Jobs/GenerateAIBlogPostJob.php` | Namespace, check dependencies |

#### Services (6 files: 4 from source + 2 new)

| File | Source Path | Package Path | Changes Required |
|------|-------------|--------------|------------------|
| BlogContentOrchestrator | `app/Services/BlogContentOrchestrator.php` | `src/Services/BlogContentOrchestrator.php` | **MAJOR**: PlatformSetting → config() |
| AIContentGenerationService | `app/Services/AIContentGenerationService.php` | `src/Services/AIContentGenerationService.php` | Namespace, check dependencies |
| BlogRssImportService | `app/Services/BlogRssImportService.php` | `src/Services/BlogRssImportService.php` | Namespace, minor changes |
| DeepLTranslationAdapter | *(NEW)* | `src/Services/DeepLTranslationAdapter.php` | Create new - adapts translation package |
| DefaultContentSanitizer | *(NEW)* | `src/Services/DefaultContentSanitizer.php` | Create new - implements interface |
| DefaultBlogAuthorizer | *(NEW)* | `src/Services/DefaultBlogAuthorizer.php` | Create new - implements interface |

**Note**: `BlogImageProcessingService.php` - Check if exists in source, may be part of BlogContentOrchestrator

#### Observers (1 file)

| File | Source Path | Package Path | Changes Required |
|------|-------------|--------------|------------------|
| BlogPostCacheObserver | `app/Observers/BlogPostCacheObserver.php` | `src/Observers/BlogPostCacheObserver.php` | Namespace, cache key configs |

#### Filament Resources (8 files total)

**BlogPost Resource** (4 files):

| File | Source Path | Package Path | Changes Required |
|------|-------------|--------------|------------------|
| BlogPostResource | `app/Filament/Resources/BlogPosts/BlogPostResource.php` | `src/Filament/Resources/BlogPostResource.php` | Namespace, model references |
| ListBlogPosts | `app/Filament/Resources/BlogPosts/Pages/ListBlogPosts.php` | `src/Filament/Resources/BlogPostResource/Pages/ListBlogPosts.php` | Namespace |
| CreateBlogPost | `app/Filament/Resources/BlogPosts/Pages/CreateBlogPost.php` | `src/Filament/Resources/BlogPostResource/Pages/CreateBlogPost.php` | Namespace |
| EditBlogPost | `app/Filament/Resources/BlogPosts/Pages/EditBlogPost.php` | `src/Filament/Resources/BlogPostResource/Pages/EditBlogPost.php` | Namespace |

**BlogCategory Resource** (4 files):

| File | Source Path | Package Path | Changes Required |
|------|-------------|--------------|------------------|
| BlogCategoryResource | `app/Filament/Resources/BlogCategories/BlogCategoryResource.php` | `src/Filament/Resources/BlogCategoryResource.php` | Namespace |
| ListBlogCategories | `app/Filament/Resources/BlogCategories/Pages/ListBlogCategories.php` | `src/Filament/Resources/BlogCategoryResource/Pages/ListBlogCategories.php` | Namespace |
| CreateBlogCategory | `app/Filament/Resources/BlogCategories/Pages/CreateBlogCategory.php` | `src/Filament/Resources/BlogCategoryResource/Pages/CreateBlogCategory.php` | Namespace |
| EditBlogCategory | `app/Filament/Resources/BlogCategories/Pages/EditBlogCategory.php` | `src/Filament/Resources/BlogCategoryResource/Pages/EditBlogCategory.php` | Namespace |

**Note**: Consider creating separate package `yourvendor/laravel-multilingual-blog-filament` for these resources

#### Migrations (15 files → consolidate to 1)

| Migration Date | File Name | Purpose | Include in Package? |
|----------------|-----------|---------|---------------------|
| 2025_12_06 | `create_blog_posts_table.php` | Core posts table | ✅ Yes |
| 2025_12_06 | `create_blog_categories_table.php` | Categories table | ✅ Yes |
| 2025_12_06 | `create_blog_comments_table.php` | Comments table | ✅ Yes |
| 2025_12_06 | `create_post_tags_table.php` | Tags table | ✅ Yes |
| 2025_12_06 | `create_post_tag_pivot_table.php` | Post-tag relationship | ✅ Yes |
| 2025_12_08 | `add_views_count_to_blog_posts.php` | View tracking | ✅ Yes |
| 2025_12_11 | `create_blog_post_ratings_table.php` | Ratings table | ✅ Yes |
| 2025_12_11 | `create_blog_post_favorites_table.php` | Favorites pivot | ✅ Yes |
| 2026_02_05 | `add_is_featured_to_blog_posts.php` | Featured posts flag | ✅ Yes |
| 2026_02_16 | `add_blog_automation_fields.php` | AI/RSS fields | ✅ Yes |
| 2026_02_18 | `create_blog_rss_imports_table.php` | RSS metadata | ✅ Yes (automation feature) |
| 2026_02_20 | `migrate_blog_referrals_to_metadata.php` | Data migration | ⚠️ Skip (one-time data fix) |
| 2026_02_20 | `remove_blog_operational_relationships.php` | Drop service links | ⚠️ Skip (cleanup migration) |
| 2026_03_23 | `change_blog_posts_user_id_constraint_to_restrict.php` | FK constraint change | ⚠️ Consolidate into main |
| *(Various)* | `create_blog_post_service_links_table.php` | Service links | ❌ No (removed) |

**Consolidation Strategy**: Merge all into single `create_blog_tables.php` migration with final schema state

#### Views (7 main + 2 components = 9 files)

**Main Views**:

| File | Source Path | Package Path | Changes Required |
|------|-------------|--------------|------------------|
| Index | `resources/views/blog/index.blade.php` | `resources/views/index.blade.php` | Component namespaces |
| Show | `resources/views/blog/show.blade.php` | `resources/views/show.blade.php` | Component namespaces |
| Category | `resources/views/blog/category.blade.php` | `resources/views/category.blade.php` | Component namespaces |
| Tag | `resources/views/blog/tag.blade.php` | `resources/views/tag.blade.php` | Component namespaces |
| Form | `resources/views/blog/form.blade.php` | `resources/views/form.blade.php` | Component namespaces, auth() |
| My Posts | `resources/views/blog/my-posts.blade.php` | `resources/views/my-posts.blade.php` | Component namespaces, auth() |
| Analytics | `resources/views/blog/analytics.blade.php` | `resources/views/analytics.blade.php` | Component namespaces |

**Components**:

| File | Source Path | Package Path | Changes Required |
|------|-------------|--------------|------------------|
| Blog CTA | `resources/views/components/blog-cta.blade.php` | `resources/views/components/cta.blade.php` | Minor adjustments |
| Social Share | `resources/views/components/blog-social-share.blade.php` | `resources/views/components/social-share.blade.php` | None |

#### Seeders (1 file)

| File | Source Path | Package Path | Changes Required |
|------|-------------|--------------|------------------|
| BlogPostSeeder | `database/seeders/BlogPostSeeder.php` | `database/seeders/BlogSeeder.php` | Namespace, make generic, rename |

#### Configuration (2 files: 1 from source + 1 split)

| File | Source Path | Package Path | Changes Required |
|------|-------------|--------------|------------------|
| Blog Config | *(Extracted from source config)* | `config/blog.php` | Create new - core settings |
| Automation Config | `config/blog-automation.php` | `config/blog-automation.php` | Namespace references |

#### Routes (1 consolidated file)

| File | Source Path | Package Path | Changes Required |
|------|-------------|--------------|------------------|
| Blog Routes | `routes/web.php` (blog section) | `routes/blog.php` | Extract blog routes only, use config prefixes |

---

## 3. File Categorization

### Category A: Copy As-Is (Namespace Changes Only)

**Total**: 11 files

These files require only namespace changes from `App\` to `YourVendor\Blog\`:

1. **Models** (2 files):
   - `BlogCategory.php` - Self-referencing parent/child, no user relations
   - `PostTag.php` - Standalone translatable tags

2. **Actions** (4 files):
   - `GetBlogIndexData.php` - Data extraction for index page
   - `GetBlogPostForShow.php` - Single post data preparation
   - `SyncPostTags.php` - Tag synchronization logic

3. **Views** (9 files):
   - All 7 main views (index, show, category, tag, form, my-posts, analytics)
   - 2 components (cta, social-share)

**Namespace Conversion Pattern**:
```php
// Before (source)
namespace App\Models;
use App\Models\BlogPost;

// After (package)
namespace YourVendor\Blog\Models;
use YourVendor\Blog\Models\BlogPost;
```

### Category B: Copy with Minor Refactoring

**Total**: 15 files

These files need namespace changes plus minor adjustments:

1. **Models** (3 files):
   - `BlogPost.php` - user() → author(), interface implementations
   - `BlogComment.php` - user() → author()
   - `BlogPostRating.php` - user() → author()

2. **Controllers** (2 files):
   - `BlogController.php` (Public) - auth() guards
   - `UserBlogController.php` (User) - auth(), authorization

3. **Actions** (3 files):
   - `GetPopularPosts.php` - Cache key config
   - `GetPopularTags.php` - Cache key config
   - `GetBlogPostsForHomepage.php` - Cache key config

4. **Jobs** (2 files):
   - `TranslateBlogPostJob.php` - Translation service interface
   - `GenerateAIBlogPostJob.php` - Check dependencies

5. **Services** (2 files):
   - `AIContentGenerationService.php` - Minor config changes
   - `BlogRssImportService.php` - Minor config changes

6. **Observers** (1 file):
   - `BlogPostCacheObserver.php` - Cache key configs

7. **Filament** (8 files):
   - All 8 Filament resource files (namespace + model references)

8. **Seeders** (1 file):
   - `BlogSeeder.php` - Make generic for package

### Category C: Copy with Major Refactoring

**Total**: 1 file

| File | Refactoring Required | Reason |
|------|----------------------|--------|
| `BlogContentOrchestrator.php` | Replace ALL `PlatformSetting::get()` calls with `config()` | Major coupling to platform settings system |

**Refactoring Pattern**:
```php
// Before (source)
$enabled = PlatformSetting::getBoolean('blog_ai_enabled');
$model = PlatformSetting::getString('blog_ai_model');
$apiKey = PlatformSetting::getString('anthropic_api_key');

// After (package)
$enabled = config('blog.automation.ai.enabled');
$model = config('blog.automation.ai.model');
$apiKey = config('services.anthropic.api_key'); // Or from package config
```

**Estimated Changes**: ~20-30 replacements

### Category D: Create New (Package-Specific)

**Total**: 20 files

These don't exist in source project and must be created:

1. **Service Provider** (1 file):
   - `BlogServiceProvider.php` - Main package registration

2. **Interfaces** (4 files):
   - `BlogAuthor.php` - User model contract
   - `ContentSanitizer.php` - HTML sanitization contract
   - `BlogAuthorizer.php` - Authorization contract
   - `BlogTranslationProvider.php` - Translation service contract

3. **Traits** (2 files):
   - `HasBlogPosts.php` - For User model (relationships)
   - `HasTranslationFallback.php` - Copy from app, adapt for package

4. **Default Implementations** (2 files):
   - `DefaultContentSanitizer.php` - Basic HTML sanitizer
   - `DefaultBlogAuthorizer.php` - Basic authorization logic

5. **Events** (6 files):
   - `BlogPostCreated.php`
   - `BlogPostUpdated.php`
   - `BlogPostPublished.php`
   - `BlogPostDeleted.php`
   - `BlogCommentPosted.php`
   - `BlogCommentApproved.php`

6. **Commands** (1 file):
   - `BlogInstallCommand.php` - Installation wizard

7. **Configuration** (1 file):
   - `config/blog.php` - Core package config (new)

8. **Package Files** (4 files):
   - `composer.json` - Dependencies
   - `README.md` - Installation guide
   - `LICENSE` - MIT recommended
   - `.gitattributes` - Export ignores

### Category E: Skip (Don't Include)

**Total**: 3-5 files

These are platform-specific and should NOT be included:

1. **Models**:
   - `BlogPostServiceLink.php` - Service offering coupling (already removed)

2. **Actions**:
   - `TrackServiceLinkClick.php` - Service-specific analytics

3. **Migrations**:
   - `migrate_blog_referrals_to_metadata.php` - One-time data migration
   - `remove_blog_operational_relationships.php` - Cleanup migration

4. **Platform-Specific Code**:
   - Any direct references to `PlatformSetting` (refactor instead)
   - `UserRole` enum usage (use interface method instead)
   - Service offering model references

---

## 4. Namespace Mapping

### 4.1 Complete Namespace Mapping Table

| Source Namespace | Package Namespace | Notes |
|------------------|-------------------|-------|
| `App\Models\` | `YourVendor\Blog\Models\` | All blog models |
| `App\Http\Controllers\Public\` | `YourVendor\Blog\Http\Controllers\` | Public controllers |
| `App\Http\Controllers\User\` | `YourVendor\Blog\Http\Controllers\` | User controllers (rename file) |
| `App\Actions\Blog\` | `YourVendor\Blog\Actions\Blog\` | Keep Blog subfolder |
| `App\Jobs\` | `YourVendor\Blog\Jobs\` | Blog-related jobs only |
| `App\Services\` | `YourVendor\Blog\Services\` | Blog services only |
| `App\Observers\` | `YourVendor\Blog\Observers\` | Blog observers only |
| `App\Filament\Resources\BlogPosts\` | `YourVendor\Blog\Filament\Resources\BlogPostResource\` | Flatten directory |
| `App\Filament\Resources\BlogCategories\` | `YourVendor\Blog\Filament\Resources\BlogCategoryResource\` | Flatten directory |
| *(NEW)* | `YourVendor\Blog\Contracts\` | Interfaces |
| *(NEW)* | `YourVendor\Blog\Traits\` | Reusable traits |
| *(NEW)* | `YourVendor\Blog\Events\` | Domain events |
| *(NEW)* | `YourVendor\Blog\Console\Commands\` | Artisan commands |

### 4.2 Import Statement Changes

**Pattern 1: Internal References**
```php
// Before
use App\Models\BlogPost;
use App\Actions\Blog\GetBlogIndexData;

// After
use YourVendor\Blog\Models\BlogPost;
use YourVendor\Blog\Actions\Blog\GetBlogIndexData;
```

**Pattern 2: Laravel Framework (No Change)**
```php
// Before and After (stays same)
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
```

**Pattern 3: Spatie Packages (No Change)**
```php
// Before and After (stays same)
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\HasMedia;
```

**Pattern 4: User Model (Config-Based)**
```php
// Before
use App\Models\User;

// After (dynamic)
$userModel = config('blog.models.user');
// Or in relationships:
return $this->belongsTo(config('blog.models.user'));
```

### 4.3 Namespace Search/Replace Commands

For bulk namespace updates, use these patterns:

```bash
# Models
find src/Models -type f -exec sed -i 's/namespace App\\Models/namespace YourVendor\\Blog\\Models/g' {} \;
find src -type f -exec sed -i 's/use App\\Models\\BlogPost/use YourVendor\\Blog\\Models\\BlogPost/g' {} \;
find src -type f -exec sed -i 's/use App\\Models\\BlogCategory/use YourVendor\\Blog\\Models\\BlogCategory/g' {} \;

# Controllers
find src/Http -type f -exec sed -i 's/namespace App\\Http\\Controllers/namespace YourVendor\\Blog\\Http\\Controllers/g' {} \;

# Actions
find src/Actions -type f -exec sed -i 's/namespace App\\Actions\\Blog/namespace YourVendor\\Blog\\Actions\\Blog/g' {} \;
find src -type f -exec sed -i 's/use App\\Actions\\Blog\\/use YourVendor\\Blog\\Actions\\Blog\\/g' {} \;

# Jobs
find src/Jobs -type f -exec sed -i 's/namespace App\\Jobs/namespace YourVendor\\Blog\\Jobs/g' {} \;
find src -type f -exec sed -i 's/use App\\Jobs\\TranslateBlogPostJob/use YourVendor\\Blog\\Jobs\\TranslateBlogPostJob/g' {} \;

# Services
find src/Services -type f -exec sed -i 's/namespace App\\Services/namespace YourVendor\\Blog\\Services/g' {} \;
find src -type f -exec sed -i 's/use App\\Services\\BlogContentOrchestrator/use YourVendor\\Blog\\Services\\BlogContentOrchestrator/g' {} \;

# Observers
find src/Observers -type f -exec sed -i 's/namespace App\\Observers/namespace YourVendor\\Blog\\Observers/g' {} \;

# Filament
find src/Filament -type f -exec sed -i 's/namespace App\\Filament\\Resources/namespace YourVendor\\Blog\\Filament\\Resources/g' {} \;
```

**Warning**: Run these commands AFTER copying files to package directory

---

## 5. Dependency Analysis

### 5.1 Required Composer Dependencies

**Core Dependencies** (must be installed):

```json
{
  "require": {
    "php": "^8.2",
    "laravel/framework": "^11.0|^12.0",
    "spatie/laravel-translatable": "^6.0|^7.0",
    "spatie/laravel-sluggable": "^3.6",
    "spatie/laravel-medialibrary": "^11.0"
  }
}
```

**Why Each Dependency**:

| Package | Used By | Purpose | Can Be Optional? |
|---------|---------|---------|------------------|
| `spatie/laravel-translatable` | BlogPost, BlogCategory, PostTag | JSON translation storage | ❌ No - Core feature |
| `spatie/laravel-sluggable` | BlogPost, BlogCategory | Auto-slug generation | ❌ No - Core feature |
| `spatie/laravel-medialibrary` | BlogPost model | Featured images + conversions | ⚠️ Could be optional, but recommended |

### 5.2 Optional Dependencies (Suggested)

```json
{
  "suggest": {
    "filament/filament": "^4.0 - Admin panel integration",
    "anthropics/anthropic-sdk-php": "^1.0 - AI content generation",
    "yourvendor/laravel-deepl-translations": "^1.0 - Automated translations with key rotation",
    "spatie/laravel-feed": "^4.0 - RSS feed generation",
    "mews/purifier": "^3.3 - Advanced HTML sanitization"
  }
}
```

**Feature Matrix**:

| Feature | Required Package | Fallback if Not Installed |
|---------|------------------|---------------------------|
| Blog Posts/Categories | None (core Laravel) | N/A |
| Translations | `spatie/laravel-translatable` | Throws exception |
| Admin Panel | `filament/filament` | Use custom controllers |
| AI Generation | `anthropics/anthropic-sdk-php` | Disable automation |
| Translation Service | `yourvendor/laravel-deepl-translations` | Manual translation only |
| RSS Feeds | `spatie/laravel-feed` | Build manually |
| HTML Sanitization | `mews/purifier` | Use default `strip_tags()` |

### 5.3 Development Dependencies

```json
{
  "require-dev": {
    "orchestra/testbench": "^9.0",
    "pestphp/pest": "^3.0",
    "pestphp/pest-plugin-laravel": "^3.0",
    "phpstan/phpstan": "^2.0",
    "laravel/pint": "^1.0"
  }
}
```

### 5.4 External API Dependencies

**Anthropic Claude API** (AI Generation):
- Used by: `BlogContentOrchestrator`, `AIContentGenerationService`
- Config: `config('blog.automation.ai.api_key')`
- Fallback: Disable AI automation if not configured

**DeepL API** (Translations):
- Used by: `TranslateBlogPostJob`, via your translation package
- Config: Via `BlogTranslationProvider` interface
- Fallback: Manual translation workflow

**Unsplash API** (Images):
- Used by: `BlogContentOrchestrator` (image fetching)
- Config: `config('blog.automation.image_fetching.unsplash_key')`
- Fallback: Skip image fetching, use defaults

**QuickChart API** (Charts - if used):
- Used by: `BlogContentOrchestrator` (data visualization)
- Config: Public API, no key required
- Fallback: Skip chart generation

### 5.5 Trait Dependencies

**From Source Project**:
- `HasTranslationFallback` - Custom trait for graceful translation fallback
- Located: `app/Traits/HasTranslationFallback.php`
- Action: **Copy to package** at `src/Traits/HasTranslationFallback.php`

**Usage in Models**:
```php
use Spatie\Translatable\HasTranslations;
use YourVendor\Blog\Traits\HasTranslationFallback;

class BlogPost extends Model
{
    use HasTranslations;
    use HasTranslationFallback;

    public array $translatable = ['title', 'excerpt', 'content'];
}
```

### 5.6 Helper Function Dependencies

**From Source Project**:

| Helper | Source Location | Used By | Action Required |
|--------|-----------------|---------|-----------------|
| `clean()` | Global helper (app/helpers.php?) | BlogPost model | Replace with interface |

**Replacement Strategy**:

```php
// Before (source)
$this->content = clean($this->content);

// After (package)
$sanitizer = app(\YourVendor\Blog\Contracts\ContentSanitizer::class);
$this->content = $sanitizer->sanitizeHtml($this->content);
```

### 5.7 Middleware Dependencies

**Used by Controllers**:
- `auth` - Laravel built-in (no issue)
- `verified` - Laravel built-in (no issue)
- Custom middleware - None identified

**Configuration**:
```php
// config/blog.php
'middleware' => [
    'public' => ['web'],
    'user' => ['web', 'auth', 'verified'],
],
```

---

## 6. Configuration Strategy

### 6.1 Core Configuration File

**File**: `config/blog.php`

**Sections**:

1. **Model Configuration** - Which models to use
2. **Table Configuration** - Table names
3. **Feature Flags** - Enable/disable features
4. **Authorization** - Guards, middleware
5. **Routing** - URL prefixes, middleware
6. **Pagination** - Items per page
7. **Cache** - Cache settings
8. **Translation** - Languages, default locale

**Example Structure**:

```php
return [
    // Model Configuration
    'models' => [
        'blog_post' => \YourVendor\Blog\Models\BlogPost::class,
        'blog_category' => \YourVendor\Blog\Models\BlogCategory::class,
        'blog_comment' => \YourVendor\Blog\Models\BlogComment::class,
        'post_tag' => \YourVendor\Blog\Models\PostTag::class,
        'blog_post_rating' => \YourVendor\Blog\Models\BlogPostRating::class,
        'user' => \App\Models\User::class, // Consuming app's User model
    ],

    // Table Names
    'tables' => [
        'blog_posts' => 'blog_posts',
        'blog_categories' => 'blog_categories',
        'blog_comments' => 'blog_comments',
        'post_tags' => 'post_tags',
        'post_tag_pivot' => 'post_tag',
        'blog_post_ratings' => 'blog_post_ratings',
        'blog_post_favorites' => 'blog_post_favorites',
        'blog_rss_imports' => 'blog_rss_imports',
        'users' => 'users', // Consuming app's users table
    ],

    // Features
    'features' => [
        'comments' => true,
        'ratings' => true,
        'favorites' => true,
        'categories' => true,
        'tags' => true,
        'media' => true,
        'rss_feeds' => false, // Optional dependency (spatie/laravel-feed)
        'automation' => false, // AI/RSS automation (see blog-automation.php)
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
        'provider' => null, // Set to translation service class
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
        'ttl' => 3600, // 1 hour
        'prefix' => 'blog',
        'tags' => ['blog'],
    ],
];
```

### 6.2 Automation Configuration File

**File**: `config/blog-automation.php`

**Sections** (from source project):

1. **General Settings** - Enable/disable automation
2. **AI Content Generation** - Claude API settings
3. **RSS Import** - RSS feed sources
4. **Translation** - Auto-translation settings
5. **Image Fetching** - Unsplash integration
6. **Chart Processing** - QuickChart settings
7. **Scheduling** - How often to generate content

**Copy from**: `/home/yefrem/projects/freelance/config/blog-automation.php`

**Changes Needed**: None (just namespace references in comments)

### 6.3 Configuration Access Patterns

**In Models**:
```php
// Get user model class
public function author(): BelongsTo
{
    return $this->belongsTo(config('blog.models.user'));
}

// Get table names
Schema::table(config('blog.tables.blog_posts'), function (Blueprint $table) {
    // ...
});
```

**In Controllers**:
```php
// Check if feature enabled
if (config('blog.features.comments')) {
    $comments = $post->comments;
}

// Get middleware
Route::middleware(config('blog.authorization.middleware.user'))
    ->group(function () {
        // User routes
    });
```

**In Observers**:
```php
// Cache keys
Cache::forget(config('blog.cache.prefix').'.homepage.posts.'.$locale);
Cache::tags(config('blog.cache.tags'))->flush();
```

**In Services**:
```php
// Translation provider
$translationService = app(config('blog.translation.provider'));

// Content sanitizer
$sanitizer = app(config('blog.sanitizer'));
```

### 6.4 Environment Variables

**Required** (consuming app's `.env`):
```env
# None - all configuration via config files
```

**Optional** (for automation features):
```env
ANTHROPIC_API_KEY=sk-ant-xxx
BLOG_AI_ENABLED=true
BLOG_AI_MODEL=claude-sonnet-4-5-20250929

BLOG_RSS_ENABLED=true

UNSPLASH_ACCESS_KEY=xxx

BLOG_TRANSLATION_ENABLED=true
```

**Note**: Package does NOT read `.env` directly, always via `config()` or `services` config

---

## 7. Quick Reference Tables

### 7.1 File Copy Checklist

| Step | Files | Action | Difficulty |
|------|-------|--------|------------|
| 1 | 5 Models | Copy + refactor user relations | Medium |
| 2 | 2 Controllers | Copy + refactor auth/authz | Medium |
| 3 | 6 Actions | Copy + namespace | Easy |
| 4 | 2 Jobs | Copy + refactor translation | Medium |
| 5 | 4 Services | Copy + major refactor (Orchestrator) | Hard |
| 6 | 1 Observer | Copy + cache key config | Easy |
| 7 | 8 Filament | Copy + namespace | Easy |
| 8 | 9 Views | Copy + component namespaces | Easy |
| 9 | 1 Seeder | Copy + make generic | Easy |
| 10 | 1 Config | Extract + create core config | Medium |
| 11 | 15 Migrations | Consolidate to 1 file | Hard |
| 12 | 20 New Files | Create from templates | Medium |

**Total Effort Estimate**: 40-60 hours

### 7.2 Relationship Mapping

**User Model Relations** (to update):

| Old Method | New Method | Change Required |
|------------|------------|-----------------|
| `user()` | `author()` | Rename method |
| `belongsTo(User::class)` | `belongsTo(config('blog.models.user'))` | Use config |
| `user_id` column | Consider `author_id` | Migration change |

**Cache Key Mapping**:

| Old Key | New Key | Usage |
|---------|---------|-------|
| `homepage.blog_posts.{locale}` | `{prefix}.homepage.posts.{locale}` | Homepage cache |
| `blog_analytics.{user_id}` | `{prefix}.analytics.{user_id}` | User analytics |
| `blog.popular_tags` | `{prefix}.popular_tags` | Tag cloud |

### 7.3 Interface Implementations

**Consuming App Must Implement**:

| Interface | Method | Purpose |
|-----------|--------|---------|
| `BlogAuthor` | `canManageBlogPosts(): bool` | Authorization check |
| `BlogAuthor` | `getId(): int` | Author ID for relations |
| `BlogAuthor` | `getName(): string` | Display name |
| `BlogAuthor` | `getEmail(): string` | Contact/avatar |

**Package Provides Defaults**:

| Interface | Default Implementation | Can Override? |
|-----------|------------------------|---------------|
| `ContentSanitizer` | `DefaultContentSanitizer` | ✅ Yes |
| `BlogAuthorizer` | `DefaultBlogAuthorizer` | ✅ Yes |
| `BlogTranslationProvider` | None (must configure) | ❌ Required |

### 7.4 Migration Consolidation Map

**Final Consolidated Schema**:

```
blog_posts (main table)
├── id, slug, status, published_at
├── author_id → users
├── category_id → blog_categories (NULL on delete)
├── translatable fields: title, excerpt, content
├── automation fields: is_external, generated_by_ai, ai_model_used
├── stats: views_count, rating_average, rating_count
├── flags: is_featured, is_demo
├── timestamps, soft deletes

blog_categories (hierarchical)
├── id, slug, parent_id (self-referencing)
├── translatable: name, description
├── is_active, sort_order
├── timestamps

blog_comments (threaded)
├── id, post_id, author_id, parent_id
├── content (text)
├── status (pending/approved), approved_at
├── timestamps

post_tags (standalone)
├── id, slug
├── translatable: name
├── usage_count
├── timestamps

post_tag (pivot)
├── blog_post_id, post_tag_id
├── timestamps

blog_post_ratings (stars)
├── id, blog_post_id, user_id
├── rating (1-5)
├── unique(blog_post_id, user_id)
├── timestamps

blog_post_favorites (pivot)
├── blog_post_id, user_id
├── timestamps

blog_rss_imports (metadata)
├── id, blog_post_id (unique)
├── source_name, source_url, external_id
├── timestamps
```

---

## Summary

**Total Files to Handle**: ~95 files

**Breakdown**:
- Copy as-is: 11 files
- Copy with minor refactoring: 24 files
- Copy with major refactoring: 1 file
- Create new: 20 files
- Skip: 3-5 files

**Critical Refactoring Points**:
1. User model → `BlogAuthor` interface
2. `PlatformSetting` → `config()` in `BlogContentOrchestrator`
3. `clean()` helper → `ContentSanitizer` interface
4. Cache keys → Configurable prefixes
5. Translation service → Interface-based

**Next Document**: See `02-IMPLEMENTATION-GUIDE.md` for step-by-step instructions

---

**Document End** | Total Lines: ~950 | Version 1.0
