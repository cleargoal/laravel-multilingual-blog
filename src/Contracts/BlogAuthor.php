<?php

declare(strict_types=1);

namespace YourVendor\Blog\Contracts;

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
}
