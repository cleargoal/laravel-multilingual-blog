<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Actions\Blog;

use Cleargoal\Blog\Contracts\BlogAuthor;
use Cleargoal\Blog\Models\BlogComment;
use Cleargoal\Blog\Models\BlogPost;
use Cleargoal\Blog\Models\BlogPostRating;
use Illuminate\Support\Facades\Cache;

class GetBlogAnalytics
{
    public function execute(BlogAuthor $author): array
    {
        $cachePrefix = config('blog.cache.prefix', 'blog');
        $cacheKey = "{$cachePrefix}.analytics.{$author->getId()}";
        $cacheTtl = config('blog.cache.ttl', 3600);

        if (! config('blog.cache.enabled', true)) {
            return $this->calculateAnalytics($author);
        }

        return Cache::remember(
            $cacheKey,
            $cacheTtl,
            fn (): array => $this->calculateAnalytics($author)
        );
    }

    private function calculateAnalytics(BlogAuthor $author): array
    {
        $blogPostModel = config('blog.models.blog_post', BlogPost::class);

        // Get all posts (filter by author for author-specific, or all for global)
        $allPosts = $blogPostModel::where('is_demo', false)->get();
        $publishedPosts = $blogPostModel::where('status', 'published')
            ->where('is_demo', false)
            ->get();
        $draftPosts = $blogPostModel::where('status', 'draft')
            ->where('is_demo', false)
            ->get();

        // Comments
        $allComments = BlogComment::whereIn('blog_post_id', $allPosts->pluck('id'))->get();
        $approvedComments = $allComments->where('status', 'approved');
        $pendingComments = $allComments->where('status', 'pending');

        // Ratings
        $ratings = BlogPostRating::whereIn('blog_post_id', $publishedPosts->pluck('id'))->get();

        // Most viewed posts
        $mostViewedPosts = $blogPostModel::where('status', 'published')
            ->where('is_demo', false)
            ->orderBy('views_count', 'desc')
            ->limit(5)
            ->get();

        // Recent posts
        $recentPosts = $blogPostModel::where('status', 'published')
            ->where('is_demo', false)
            ->orderBy('published_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'totalPosts' => $allPosts->count(),
            'publishedPosts' => $publishedPosts->count(),
            'draftPosts' => $draftPosts->count(),
            'totalViews' => $publishedPosts->sum('views_count'),
            'totalComments' => $allComments->count(),
            'approvedComments' => $approvedComments->count(),
            'pendingComments' => $pendingComments->count(),
            'averageRating' => $ratings->isNotEmpty() ? round($ratings->avg('rating'), 1) : 0,
            'totalRatings' => $ratings->count(),
            'mostViewedPosts' => $mostViewedPosts,
            'recentPosts' => $recentPosts,
        ];
    }
}
