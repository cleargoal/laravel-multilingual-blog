<?php

namespace Cleargoal\Blog\Actions\Blog;

use Cleargoal\Blog\Models\BlogPost;

class GetBlogPostForShow
{
    /**
     * Get blog post by slug with related data.
     *
     * @param  string  $slug  The blog post slug
     * @return array|null
     */
    public function execute(string $slug): ?array
    {
        $blogPostModel = config('blog.models.blog_post', BlogPost::class);

        // Find post by slug
        $post = $blogPostModel::where('slug', $slug)
            ->where('status', 'published')
            ->where('is_demo', false)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with(['author', 'category', 'tags'])
            ->first();

        if (!$post) {
            return null;
        }

        // Increment view count
        $post->increment('views_count');

        $locale = app()->getLocale();

        // Get related posts (same category)
        $relatedPosts = $blogPostModel::where('status', 'published')
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->where('is_demo', false)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        // Get approved comments for this post
        $comments = $post->comments()
            ->where('status', 'approved')
            ->whereNotNull('approved_at')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate average rating
        $ratings = $post->ratings;
        $averageRating = $ratings->isNotEmpty() ? round($ratings->avg('rating'), 1) : 0;
        $ratingsCount = $ratings->count();

        return [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'comments' => $comments,
            'averageRating' => $averageRating,
            'ratingsCount' => $ratingsCount,
        ];
    }

}
