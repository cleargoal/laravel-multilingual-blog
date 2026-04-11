<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Contracts;

interface ContentSanitizer
{
    /**
     * Sanitize HTML content while preserving safe tags.
     *
     * @param  string  $html  Raw HTML content
     * @return string         Sanitized HTML
     */
    public function sanitizeHtml(string $html): string;

    /**
     * Strip all HTML tags from content.
     *
     * @param  string  $html  Raw HTML content
     * @return string         Plain text
     */
    public function stripAllTags(string $html): string;
}
