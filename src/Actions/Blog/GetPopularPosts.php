<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Actions\Blog;

use Cleargoal\Blog\Models\BlogPost;
use Illuminate\Support\Facades\Cache;

class GetPopularPosts
{
    /**
     * Get popular blog posts with caching
     *
     * @param  int  $limit  Number of posts to return
     * @param  string  $period  Time period (7days, 30days, alltime)
     */
    public function execute(int $limit = 5, string $period = '7days'): array
    {
        $cachePrefix = config('blog.cache.prefix', 'blog');
        $cacheKey = "{$cachePrefix}.popular_posts.{$period}.{$limit}";
        $cacheTtl = config('blog.cache.ttl', 3600);

        if (! config('blog.cache.enabled', true)) {
            return $this->getPopularPosts($limit, $period);
        }

        return Cache::remember($cacheKey, $cacheTtl, function () use ($limit, $period) {
            return $this->getPopularPosts($limit, $period);
        });
    }

    private function getPopularPosts(int $limit, string $period): array
    {
        $blogPostModel = config('blog.models.blog_post', BlogPost::class);

        $query = $blogPostModel::where('status', 'published')
            ->where('is_demo', false)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        // Apply time filter for 7days and 30days
        if ($period === '7days') {
            $query->where('published_at', '>=', now()->subDays(7));
        } elseif ($period === '30days') {
            $query->where('published_at', '>=', now()->subDays(30));
        }

        return $query->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($post) {
                return [
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'views_count' => $post->views_count,
                ];
            })
            ->toArray();
    }
}
