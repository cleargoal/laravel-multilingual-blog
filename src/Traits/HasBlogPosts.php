<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trait for User model to add blog post relationships.
 *
 * Usage:
 * ```php
 * class User extends Authenticatable implements BlogAuthor
 * {
 *     use HasBlogPosts;
 * }
 * ```
 */
trait HasBlogPosts
{
    /**
     * Get all blog posts authored by this user.
     */
    public function blogPosts(): HasMany
    {
        return $this->hasMany(config('blog.models.blog_post'), 'author_id');
    }

    /**
     * Get published blog posts by this user.
     */
    public function publishedBlogPosts(): HasMany
    {
        return $this->blogPosts()
            ->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    /**
     * Get all blog comments by this user.
     */
    public function blogComments(): HasMany
    {
        return $this->hasMany(config('blog.models.blog_comment'), 'author_id');
    }

    /**
     * Get this user's favorite blog posts.
     */
    public function favoriteBlogPosts(): BelongsToMany
    {
        return $this->belongsToMany(
            config('blog.models.blog_post'),
            config('blog.tables.blog_post_favorites'),
            'user_id',
            'blog_post_id'
        )->withTimestamps();
    }

    /**
     * Get all blog post ratings by this user.
     */
    public function blogPostRatings(): HasMany
    {
        return $this->hasMany(config('blog.models.blog_post_rating'), 'user_id');
    }

    /**
     * Check if this user has favorited a specific blog post.
     */
    public function hasFavoritedBlogPost(int $postId): bool
    {
        return $this->favoriteBlogPosts()->where('blog_posts.id', $postId)->exists();
    }

    /**
     * Get the user's rating for a specific blog post.
     */
    public function getBlogPostRating(int $postId): ?int
    {
        $rating = $this->blogPostRatings()
            ->where('blog_post_id', $postId)
            ->first();

        return $rating?->rating;
    }
}
