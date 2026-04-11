<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Actions\Blog;

use Cleargoal\Blog\Models\PostTag;
use Illuminate\Support\Facades\Cache;

class GetPopularTags
{
    /**
     * Get popular tags ordered by usage
     */
    public function execute(int $limit = 20): array
    {
        $cachePrefix = config('blog.cache.prefix', 'blog');
        $cacheKey = "{$cachePrefix}.popular_tags";
        $cacheTtl = config('blog.cache.popular_tags_ttl', 3600);

        if (! config('blog.cache.enabled', true)) {
            return $this->getPopularTags($limit);
        }

        return Cache::remember($cacheKey, $cacheTtl, function () use ($limit) {
            return $this->getPopularTags($limit);
        });
    }

    private function getPopularTags(int $limit): array
    {
        $tags = PostTag::where('usage_count', '>', 0)
            ->orderBy('usage_count', 'desc')
            ->limit($limit)
            ->get();

        return $tags->map(function ($tag): array {
            return [
                'name' => $tag->name,
                'slug' => $tag->slug,
                'usage_count' => $tag->usage_count,
            ];
        })->toArray();
    }
}
