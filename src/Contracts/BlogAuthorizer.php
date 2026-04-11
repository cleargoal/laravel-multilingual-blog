<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Contracts;

interface BlogAuthorizer
{
    /**
     * Check if the given user can view the blog post.
     */
    public function canView(BlogAuthor $user, object $post): bool;

    /**
     * Check if the given user can create blog posts.
     */
    public function canCreate(BlogAuthor $user): bool;

    /**
     * Check if the given user can update the blog post.
     */
    public function canUpdate(BlogAuthor $user, object $post): bool;

    /**
     * Check if the given user can delete the blog post.
     */
    public function canDelete(BlogAuthor $user, object $post): bool;

    /**
     * Check if the given user can publish/unpublish blog posts.
     */
    public function canPublish(BlogAuthor $user): bool;
}
