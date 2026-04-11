<?php

namespace Cleargoal\Blog\Actions\Blog;

use Cleargoal\Blog\Models\BlogCategory;
use Cleargoal\Blog\Models\BlogPost;
use Cleargoal\Blog\Models\PostTag;

class GetBlogIndexData
{
    /**
     * Get blog index data with filtering.
     */
    public function execute(?string $categorySlug = null, ?string $tagSlug = null, ?string $search = null): array
    {
        $blogPostModel = config('blog.models.blog_post', BlogPost::class);
        $perPage = config('blog.posts_per_page', 10);

        // Build query
        $query = $blogPostModel::where('status', 'published')
            ->where('is_demo', false)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc');

        // Filter by category
        if ($categorySlug) {
            $category = BlogCategory::where('slug', $categorySlug)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // Filter by tag
        if ($tagSlug) {
            $tag = PostTag::where('slug', $tagSlug)->first();
            if ($tag) {
                $query->whereHas('tags', function ($q) use ($tag) {
                    $q->where('post_tags.id', $tag->id);
                });
            }
        }

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Paginate
        $posts = $query->paginate($perPage);

        // Get categories with post counts
        $categories = BlogCategory::withCount([
            'posts' => fn ($q) => $q->where('status', 'published')
                ->where('is_demo', false)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now()),
        ])->orderBy('sort_order')->get();

        // Get popular tags
        $popularTags = PostTag::where('usage_count', '>', 0)
            ->orderBy('usage_count', 'desc')
            ->limit(10)
            ->get();

        return [
            'posts' => $posts,
            'categories' => $categories,
            'popularTags' => $popularTags,
        ];
    }
}
