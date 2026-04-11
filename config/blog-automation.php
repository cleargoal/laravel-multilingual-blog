<?php

return [
    // AI Content Generation
    'ai' => [
        'provider' => env('BLOG_AI_PROVIDER', 'anthropic'), // anthropic or openai
        'model' => env('BLOG_AI_MODEL', 'claude-sonnet-4-5-20250929'),
        'max_tokens' => env('BLOG_AI_MAX_TOKENS', 4000),
        'temperature' => env('BLOG_AI_TEMPERATURE', 0.7),
        'timeout' => env('BLOG_AI_TIMEOUT', 120), // seconds
    ],

    // RSS Feed Sources
    'rss_feeds' => [
        'enabled' => env('BLOG_RSS_ENABLED', true),
        'sources' => [
            [
                'name' => 'Freelancer Union Blog',
                'url' => 'https://blog.freelancersunion.org/feed/',
                'category' => 'Industry News',
                'language' => 'en',
            ],
            [
                'name' => 'Freelancing Hacks',
                'url' => 'https://www.freelancinghacks.com/feed/',
                'category' => 'Industry News',
                'language' => 'en',
            ],
            [
                'name' => 'Toptal Blog',
                'url' => 'https://www.toptal.com/blog.rss',
                'category' => 'Industry News',
                'language' => 'en',
            ],
        ],
        'max_items_per_feed' => 5, // Only process latest 5 items
        'content_min_words' => 300, // Skip short articles
        'cache_ttl' => 3600, // Cache feed data for 1 hour
    ],

    // Content Generation Topics
    'topics' => [
        'platform_features' => [
            'How to use the portfolio system effectively',
            'Understanding service package pricing strategies',
            'Maximizing your freelancer profile visibility',
            'Using the dispute resolution system',
            'Getting started with service offerings on our platform',
        ],
        'freelancing_tips' => [
            'Time management strategies for remote freelancers',
            'Building long-term client relationships',
            'Pricing strategies for new freelancers',
            'Creating a productive home office workspace',
            'Marketing your freelance services effectively',
        ],
        'tutorials' => [
            'Writing compelling service descriptions that convert',
            'Creating an attractive portfolio that stands out',
            'Communicating effectively with clients',
            'Handling difficult client situations professionally',
            'Optimizing your freelance workflow for efficiency',
        ],
        'buyer_guides' => [
            'How to write a clear project brief for freelancers',
            'Choosing the right freelancer for your project',
            'Setting realistic deadlines and budgets for freelance work',
            'Effective communication tips for working with remote freelancers',
            'Understanding freelance pricing and package options',
            'How to review and provide constructive feedback on deliverables',
            'Managing multiple freelancers on a single project',
            'When to request revisions vs accept work as-is',
            'Building long-term relationships with trusted freelancers',
            'Red flags to watch for when hiring freelancers',
        ],
    ],

    // Scheduler Configuration
    'scheduler' => [
        'enabled' => env('BLOG_AUTOMATION_ENABLED', true),
        'original_posts_per_month' => env('BLOG_ORIGINAL_POSTS_PER_MONTH', 2),
        'rss_posts_per_month' => env('BLOG_RSS_POSTS_PER_MONTH', 2),
        'publish_immediately' => env('BLOG_PUBLISH_IMMEDIATELY', false), // Create as drafts
        'featured_probability' => 0.15, // 15% chance to mark as featured

        // Content Balance Rules
        // At least 1 post per month must be from 'buyer_guides' category (client-targeted content)
        // This ensures we create content that helps buyers/clients, not just freelancers
        'min_buyer_posts_per_month' => 1,
    ],

    // Translation
    'translation' => [
        'enabled' => true,
        'queue' => env('BLOG_TRANSLATION_QUEUE', true), // Queue translations
        'delay_seconds' => 5, // Delay between language translations (rate limiting)
    ],

    // Image Fetching (Unsplash API)
    'image_fetching' => [
        'enabled' => env('BLOG_IMAGE_FETCHING_ENABLED', true),
        'api_key' => env('UNSPLASH_ACCESS_KEY'),
        'orientation' => 'landscape', // landscape, portrait, squarish
        'timeout' => 30, // HTTP request timeout in seconds
        'max_retries' => 3, // Keyword fallback attempts
    ],

    // Logging
    'logging' => [
        'channel' => env('BLOG_AUTOMATION_LOG_CHANNEL', 'daily'),
        'prefix' => '[BlogAutomation]',
    ],
];
