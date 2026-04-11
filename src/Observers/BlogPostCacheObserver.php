<?php

declare(strict_types=1);

namespace YourVendor\Blog\Observers;

use YourVendor\Blog\Models\BlogPost;
use Illuminate\Support\Facades\Cache;

class BlogPostCacheObserver
{
    /**
     * Handle the BlogPost "saved" event.
     */
    public function saved(BlogPost $post): void
    {
        $this->clearPopularPostsCache();
    }

    /**
     * Handle the BlogPost "deleted" event.
     */
    public function deleted(BlogPost $post): void
    {
        $this->clearPopularPostsCache();
    }

    /**
     * Clear all popular posts cache keys
     */
    private function clearPopularPostsCache(): void
    {
        $prefix = config('blog.cache.prefix', 'blog');
        $periods = ['7days', '30days', 'alltime'];
        $limits = [3, 5, 10];

        foreach ($periods as $period) {
            foreach ($limits as $limit) {
                Cache::forget("{$prefix}.popular_posts.{$period}.{$limit}");
            }
        }

        // Clear popular tags cache
        Cache::forget("{$prefix}.popular_tags");

        // Clear homepage cache per locale
        foreach (config('blog.languages', ['en']) as $locale) {
            Cache::forget("{$prefix}.homepage.posts.{$locale}");
        }

        // Flush cache tags if supported
        if (config('blog.cache.enabled') && ! empty(config('blog.cache.tags'))) {
            Cache::tags(config('blog.cache.tags'))->flush();
        }
    }
}
