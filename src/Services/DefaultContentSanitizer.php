<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Services;

use Cleargoal\Blog\Contracts\ContentSanitizer;

/**
 * Default HTML content sanitizer using PHP's built-in strip_tags().
 *
 * For advanced sanitization with configurable rules, consider using:
 * - mews/purifier package for HTMLPurifier integration
 * - Custom implementation with your preferred sanitization library
 */
class DefaultContentSanitizer implements ContentSanitizer
{
    /**
     * Sanitize HTML content while preserving safe tags.
     *
     * This default implementation uses strip_tags() with allowed tags.
     * Override this service in config/blog.php for advanced sanitization.
     */
    public function sanitizeHtml(string $html): string
    {
        // Remove script tags and their content FIRST
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);

        // Remove style tags and their content
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);

        // Remove iframe tags
        $html = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $html);

        $allowedTags = '<p><a><strong><em><ul><ol><li><br><img><h1><h2><h3><h4><h5><h6><blockquote><code><pre><table><thead><tbody><tr><th><td><span><div>';

        // Strip disallowed tags
        $html = strip_tags($html, $allowedTags);

        // Remove dangerous event handlers and attributes
        $dangerousAttributes = [
            'onclick', 'ondblclick', 'onmousedown', 'onmouseup', 'onmouseover', 'onmousemove', 'onmouseout',
            'onkeydown', 'onkeypress', 'onkeyup', 'onload', 'onerror', 'onfocus', 'onblur', 'onchange',
            'onsubmit', 'onreset', 'onselect', 'onabort', 'onunload',
        ];

        foreach ($dangerousAttributes as $attr) {
            $html = preg_replace('/' . $attr . '\s*=\s*["\'][^"\']*["\']/i', '', $html);
            $html = preg_replace('/' . $attr . '\s*=\s*[^\s>]*/i', '', $html);
        }

        // Remove javascript: protocol from href and src attributes
        $html = preg_replace('/href\s*=\s*["\']javascript:[^"\']*["\']/i', '', $html);
        $html = preg_replace('/src\s*=\s*["\']javascript:[^"\']*["\']/i', '', $html);

        return $html;
    }

    /**
     * Strip all HTML tags from content.
     */
    public function stripAllTags(string $html): string
    {
        return strip_tags(html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
