<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface BlogAuthor
{
    /**
     * Get the author's unique identifier.
     */
    public function getId(): int;

    /**
     * Get the author's display name.
     */
    public function getName(): string;

    /**
     * Get the author's email address.
     */
    public function getEmail(): string;

    /**
     * Check if the author can manage blog posts (create, edit, delete).
     */
    public function canManageBlogPosts(): bool;

    /**
     * Check if the author is an administrator.
     */
    public function isAdmin(): bool;

    /**
     * Check if the author is a moderator.
     */
    public function isModerator(): bool;

    /**
     * Get the author's blog posts relationship.
     */
    public function blogPosts(): HasMany;
}
