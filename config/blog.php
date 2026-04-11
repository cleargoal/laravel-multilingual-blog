<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Blog Models
    |--------------------------------------------------------------------------
    |
    | Configure which Eloquent models to use for blog functionality.
    | This allows you to extend or replace default models.
    |
    */
    'models' => [
        'blog_post' => \YourVendor\Blog\Models\BlogPost::class,
        'blog_category' => \YourVendor\Blog\Models\BlogCategory::class,
        'blog_comment' => \YourVendor\Blog\Models\BlogComment::class,
        'post_tag' => \YourVendor\Blog\Models\PostTag::class,
        'blog_post_rating' => \YourVendor\Blog\Models\BlogPostRating::class,
        'user' => \App\Models\User::class, // Your application's User model
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Tables
    |--------------------------------------------------------------------------
    |
    | Table names used by the blog package. Change these if you need
    | custom table names in your application.
    |
    */
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

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific blog features.
    |
    */
    'features' => [
        'comments' => true,
        'ratings' => true,
        'favorites' => true,
        'categories' => true,
        'tags' => true,
        'media' => true,
        'rss_feeds' => false,
        'automation' => false, // AI generation & RSS imports (see blog-automation.php)
    ],

    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    |
    | Configure authentication guards, middleware, and authorization logic.
    |
    */
    'authorization' => [
        'guard' => 'web',
        'middleware' => [
            'public' => ['web'],
            'user' => ['web', 'auth', 'verified'],
        ],
        'authorizer' => \YourVendor\Blog\Services\DefaultBlogAuthorizer::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Sanitization
    |--------------------------------------------------------------------------
    |
    | HTML sanitization service for user-generated content.
    | Default uses strip_tags(). For advanced sanitization, use mews/purifier.
    |
    */
    'sanitizer' => \YourVendor\Blog\Services\DefaultContentSanitizer::class,

    /*
    |--------------------------------------------------------------------------
    | Multilingual Support
    |--------------------------------------------------------------------------
    |
    | Supported languages and translation settings.
    |
    */
    'languages' => ['en', 'uk', 'de', 'fr', 'es'],
    'default_locale' => 'en',

    'translation' => [
        'provider' => null, // Set to BlogTranslationProvider implementation
        'enabled' => false,
        'queue' => true,
        'delay_seconds' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Routing
    |--------------------------------------------------------------------------
    |
    | URL prefix, route naming, and middleware for blog routes.
    |
    */
    'routes' => [
        'prefix' => 'blog',
        'name_prefix' => 'blog.',
        'middleware' => ['web'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Items per page for various blog listings.
    |
    */
    'pagination' => [
        'per_page' => 15,
        'featured_count' => 3,
        'comments_per_page' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache settings for blog data. Uses Laravel's cache system.
    |
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour in seconds
        'prefix' => 'blog',
        'tags' => ['blog'], // Only works with cache drivers that support tagging
    ],

    /*
    |--------------------------------------------------------------------------
    | RSS Feed Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for RSS feed generation (requires spatie/laravel-feed).
    |
    */
    'rss' => [
        'enabled' => false,
        'items_limit' => 50,
        'title' => env('APP_NAME').' Blog',
        'description' => 'Latest blog posts',
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Settings
    |--------------------------------------------------------------------------
    |
    | Image upload and media library configuration.
    |
    */
    'media' => [
        'disk' => 'public',
        'max_upload_size' => 5120, // KB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'image_conversions' => [
            'thumb' => [
                'width' => 400,
                'height' => 300,
            ],
            'large' => [
                'width' => 1200,
                'height' => 630,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Settings
    |--------------------------------------------------------------------------
    |
    | Default SEO metadata for blog posts.
    |
    */
    'seo' => [
        'meta_description_length' => 160,
        'generate_og_images' => true,
        'sitemap_enabled' => true,
    ],
];
