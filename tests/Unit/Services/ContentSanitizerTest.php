<?php

use Cleargoal\Blog\Services\DefaultContentSanitizer;

it('allows safe HTML tags', function () {
    $sanitizer = new DefaultContentSanitizer;

    $html = '<p>This is <strong>bold</strong> and <em>italic</em> text.</p>';
    $result = $sanitizer->sanitizeHtml($html);

    expect($result)->toBe($html);
});

it('removes dangerous script tags', function () {
    $sanitizer = new DefaultContentSanitizer;

    $html = '<p>Safe content</p><script>alert("XSS")</script>';
    $result = $sanitizer->sanitizeHtml($html);

    expect($result)->not()->toContain('<script>');
    expect($result)->not()->toContain('alert');
    expect($result)->toContain('<p>Safe content</p>');
});

it('removes onclick and other dangerous attributes', function () {
    $sanitizer = new DefaultContentSanitizer;

    $html = '<p onclick="alert(\'XSS\')">Click me</p>';
    $result = $sanitizer->sanitizeHtml($html);

    expect($result)->not()->toContain('onclick');
    expect($result)->toContain('Click me');
});

it('preserves allowed HTML structure', function () {
    $sanitizer = new DefaultContentSanitizer;

    $html = '<h1>Title</h1><p>Paragraph with <a href="#">link</a></p><ul><li>Item</li></ul>';
    $result = $sanitizer->sanitizeHtml($html);

    expect($result)->toContain('<h1>');
    expect($result)->toContain('<p>');
    expect($result)->toContain('<a href="#">');
    expect($result)->toContain('<ul>');
    expect($result)->toContain('<li>');
});

it('strips all tags when using stripAllTags', function () {
    $sanitizer = new DefaultContentSanitizer;

    $html = '<p>This is <strong>bold</strong> text with <script>evil()</script></p>';
    $result = $sanitizer->stripAllTags($html);

    expect($result)->toBe('This is bold text with evil()');
    expect($result)->not()->toContain('<');
    expect($result)->not()->toContain('>');
});

it('handles HTML entities correctly', function () {
    $sanitizer = new DefaultContentSanitizer;

    $html = '<p>Price: &pound;100 &amp; &euro;90</p>';
    $result = $sanitizer->stripAllTags($html);

    expect($result)->toContain('£100');
    expect($result)->toContain('&');
    expect($result)->toContain('€90');
});
