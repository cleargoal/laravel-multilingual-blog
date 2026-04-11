<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Services;

use Cleargoal\Blog\Contracts\BlogAuthor;
use Cleargoal\Blog\Contracts\BlogAuthorizer;

/**
 * Default blog authorization logic.
 *
 * Override this in config/blog.php to implement custom authorization
 * logic, such as role-based permissions or policy classes.
 */
class DefaultBlogAuthorizer implements BlogAuthorizer
{
    /**
     * Check if the given user can view the blog post.
     *
     * Default: Anyone can view published posts, only author/admin can view drafts.
     */
    public function canView(BlogAuthor $user, object $post): bool
    {
        if ($post->isPublished()) {
            return true;
        }

        // Draft posts only viewable by author or blog managers
        return $post->author_id === $user->getId() || $user->canManageBlogPosts();
    }

    /**
     * Check if the given user can create blog posts.
     *
     * Default: Uses canManageBlogPosts() from BlogAuthor interface.
     */
    public function canCreate(BlogAuthor $user): bool
    {
        return $user->canManageBlogPosts();
    }

    /**
     * Check if the given user can update the blog post.
     *
     * Default: Post author or admins only.
     */
    public function canUpdate(BlogAuthor $user, object $post): bool
    {
        $isAdmin = method_exists($user, 'isAdmin') && $user->isAdmin();
        return $post->author_id === $user->getId() || $isAdmin;
    }

    /**
     * Check if the given user can delete the blog post.
     *
     * Default: Post author or admins only.
     */
    public function canDelete(BlogAuthor $user, object $post): bool
    {
        $isAdmin = method_exists($user, 'isAdmin') && $user->isAdmin();
        return $post->author_id === $user->getId() || $isAdmin;
    }

    /**
     * Check if the given user can publish/unpublish blog posts.
     *
     * Default: Blog managers only.
     */
    public function canPublish(BlogAuthor $user): bool
    {
        return $user->canManageBlogPosts();
    }
}
