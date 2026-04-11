<?php

declare(strict_types=1);

namespace YourVendor\Blog\Services;

use App\Enums\UserRole;
use YourVendor\Blog\Models\BlogCategory;
use YourVendor\Blog\Models\BlogPost;
use YourVendor\Blog\Models\BlogRssImport;
use YourVendor\Blog\Models\PlatformSetting;
use YourVendor\Blog\Models\User;
use App\Notifications\RSSImportError;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use willvincent\Feeds\Facades\FeedsFacade as Feeds;

class RSSFeedImportService
{
    /**
     * Import posts from RSS feed
     */
    public function importFromFeed(array $feedConfig): Collection
    {
        $imported = collect();
        $hasErrors = false;

        try {
            $feed = Feeds::make(
                $feedConfig['url'],
                PlatformSetting::get('blog_rss_cache_ttl', config('blog-automation.rss_feeds.cache_ttl'))
            );

            if (! $feed) {
                throw new \Exception('Failed to fetch RSS feed');
            }

            $items = $feed->get_items(0, config('blog-automation.rss_feeds.max_items_per_feed', 5));

            foreach ($items as $item) {
                try {
                    $post = $this->processItem($item, $feedConfig);

                    if ($post) {
                        $imported->push($post);
                    }
                } catch (\Exception $e) {
                    $hasErrors = true;

                    Log::channel(config('blog-automation.logging.channel'))
                        ->warning('[BlogAutomation] Failed to process RSS item', [
                            'feed' => $feedConfig['name'],
                            'error' => $e->getMessage(),
                        ]);
                }
            }
        } catch (\Exception $e) {
            // Skip ERROR logs in testing to avoid polluting test output
            if (! app()->environment('testing')) {
                Log::channel(config('blog-automation.logging.channel'))
                    ->error('[BlogAutomation] RSS feed import failed', [
                        'feed' => $feedConfig['name'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ]);
            }

            // Notify admins about feed import failure
            $this->notifyAdmins(new RSSImportError(
                $feedConfig['name'] ?? 'unknown',
                $feedConfig['url'] ?? 'unknown',
                $e->getMessage()
            ));

            throw $e;
        }

        return $imported;
    }

    /**
     * Process a single RSS item
     */
    protected function processItem($item, array $feedConfig): ?BlogPost
    {
        $guid = $item->get_id();
        $url = $item->get_permalink();
        $title = $item->get_title();

        // Check for duplicates
        if ($this->isDuplicate($guid, $url, $title)) {
            return null;
        }

        // Extract featured image from RSS enclosure or media:content
        $featuredImageUrl = $this->extractFeaturedImageFromRSS($item);

        // Extract content
        $content = $this->extractContent($item);

        // Remove featured image from content if it's the first element
        if ($featuredImageUrl) {
            $content = $this->removeFeaturedImageFromContent($content, $featuredImageUrl);
        }

        // Check minimum word count
        $wordCount = str_word_count(strip_tags($content));
        $minWords = PlatformSetting::get('blog_rss_content_min_words', config('blog-automation.rss_feeds.content_min_words', 300));
        if ($wordCount < $minWords) {
            return null;
        }

        // Get category
        $category = BlogCategory::where('slug', str($feedConfig['category'])->slug())
            ->orWhere('name->en', $feedConfig['category'])
            ->first();

        if (! $category) {
            throw new \Exception("Category not found: {$feedConfig['category']}");
        }

        // Get dedicated RSS author user
        $author = \App\Models\User::where('email', 'rss-content@freelanc.io')->first();

        // Fallback to first available content creator if RSS user doesn't exist
        if (! $author) {
            $author = \App\Models\User::where('role', \App\Enums\UserRole::CONTENT_CREATOR)
                ->where('is_demo', false)
                ->first();
        }

        // Final fallback: any moderator
        if (! $author) {
            $author = \App\Models\User::where('role', \App\Enums\UserRole::MODERATOR)
                ->where('is_demo', false)
                ->first();
        }

        if (! $author) {
            throw new \Exception('No suitable author found for RSS import. Run: php artisan user:create-rss-author');
        }

        // Generate excerpt
        $excerpt = $this->generateExcerpt($content);

        // Create blog post
        $publishImmediately = PlatformSetting::get('blog_publish_immediately', config('blog-automation.scheduler.publish_immediately', false));
        $post = BlogPost::create([
            'user_id' => $author->id,
            'category_id' => $category->id,
            'title' => [$feedConfig['language'] => $title],
            'excerpt' => [$feedConfig['language'] => $excerpt],
            'content' => [$feedConfig['language'] => $content],
            'original_locale' => $feedConfig['language'],
            'status' => $publishImmediately ? 'published' : 'draft',
            'published_at' => $publishImmediately ? now() : null,
            'is_external' => true,
            'external_source_name' => $feedConfig['name'],
            'external_source_url' => $url,
        ]);

        // Attach featured image if found
        if ($featuredImageUrl) {
            $this->attachFeaturedImage($post, $featuredImageUrl);
        }

        // Track import
        BlogRssImport::create([
            'feed_url' => $feedConfig['url'],
            'item_guid' => $guid,
            'item_url' => $url,
            'title_hash' => md5($title),
            'blog_post_id' => $post->id,
            'imported_at' => now(),
        ]);

        return $post;
    }

    /**
     * Check if RSS item is already imported
     */
    protected function isDuplicate(string $guid, string $url, string $title): bool
    {
        // Check GUID
        if (BlogRssImport::where('item_guid', $guid)->exists()) {
            return true;
        }

        // Check URL
        if (BlogPost::where('external_source_url', $url)->exists()) {
            return true;
        }

        // Check title similarity
        $titleHash = md5($title);
        if (BlogRssImport::where('title_hash', $titleHash)->exists()) {
            return true;
        }

        // Check fuzzy title match (Levenshtein distance)
        $existingTitles = BlogPost::where('is_external', true)
            ->pluck('title')
            ->map(fn ($t) => is_array($t) ? ($t['en'] ?? reset($t)) : $t)
            ->filter();

        foreach ($existingTitles as $existingTitle) {
            $similarity = $this->calculateSimilarity($title, (string) $existingTitle);
            if ($similarity > 0.90) { // 90% similar
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate string similarity using Levenshtein distance
     */
    protected function calculateSimilarity(string $str1, string $str2): float
    {
        $str1 = strtolower($str1);
        $str2 = strtolower($str2);

        $maxLen = max(strlen($str1), strlen($str2));
        if ($maxLen === 0) {
            return 1.0;
        }

        $distance = levenshtein($str1, $str2);

        return 1 - ($distance / $maxLen);
    }

    /**
     * Extract and clean content from RSS item
     */
    protected function extractContent($item): string
    {
        // Try multiple sources to get the fullest content possible
        $content = '';

        // 1. Try content:encoded (full content in many feeds)
        $contentEncoded = $item->get_item_tags('http://purl.org/rss/1.0/modules/content/', 'encoded');
        if ($contentEncoded && isset($contentEncoded[0]['data'])) {
            $content = $contentEncoded[0]['data'];
        }

        // 2. Try get_content() if content:encoded is empty
        if (empty($content)) {
            $content = $item->get_content();
        }

        // 3. Fall back to description if content is still empty or too short
        if (empty($content) || strlen(strip_tags($content)) < 100) {
            $description = $item->get_description();
            // Only use description if it's longer than current content
            if ($description && strlen(strip_tags($description)) > strlen(strip_tags($content))) {
                $content = $description;
            }
        }

        // Decode HTML entities (fixes &lt; → <, &amp; → &, etc.)
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Clean HTML
        $content = $this->cleanHtml($content);

        return $content;
    }

    /**
     * Clean HTML content
     */
    protected function cleanHtml(string $html): string
    {
        // Remove dangerous tags
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $html);
        $html = preg_replace('/<object\b[^>]*>(.*?)<\/object>/is', '', $html);
        $html = preg_replace('/<embed\b[^>]*>/is', '', $html);

        // Clean up whitespace
        $html = preg_replace('/\s+/', ' ', $html);

        return trim($html);
    }

    /**
     * Generate excerpt from content
     */
    protected function generateExcerpt(string $content, int $maxLength = 200): string
    {
        // Decode HTML entities first (fixes &lt; → <, &amp; → &, etc.)
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Strip all HTML tags
        $text = strip_tags($content);

        // Clean up whitespace
        $text = preg_replace('/\s+/', ' ', trim($text));

        if (strlen($text) <= $maxLength) {
            return $text;
        }

        $excerpt = substr($text, 0, $maxLength);
        $lastSpace = strrpos($excerpt, ' ');

        if ($lastSpace !== false) {
            return substr($text, 0, $lastSpace).'...';
        }

        return $excerpt.'...';
    }

    /**
     * Extract featured image from RSS item properties
     * Checks: enclosure, media:content, media:thumbnail, then HTML content
     */
    protected function extractFeaturedImageFromRSS($item): ?string
    {
        // 1. Try RSS enclosure (standard RSS property)
        $enclosure = $item->get_enclosure();
        if ($enclosure) {
            $link = $enclosure->get_link();
            if ($link && filter_var($link, FILTER_VALIDATE_URL)) {
                return $link;
            }
        }

        // 2. Try media:content (Media RSS extension)
        $mediaContent = $item->get_item_tags('http://search.yahoo.com/mrss/', 'content');
        if ($mediaContent && isset($mediaContent[0]['attribs']['']['url'])) {
            $url = $mediaContent[0]['attribs']['']['url'];
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                return $url;
            }
        }

        // 3. Try media:thumbnail
        $mediaThumbnail = $item->get_item_tags('http://search.yahoo.com/mrss/', 'thumbnail');
        if ($mediaThumbnail && isset($mediaThumbnail[0]['attribs']['']['url'])) {
            $url = $mediaThumbnail[0]['attribs']['']['url'];
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                return $url;
            }
        }

        // 4. Fallback: Extract from HTML content
        $content = $item->get_content() ?: $item->get_description();
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches)) {
            $imageUrl = $matches[1];
            if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                return $imageUrl;
            }
        }

        return null;
    }

    /**
     * Remove featured image from content if it's the first element
     * Prevents duplication when RSS includes featured image in content
     */
    protected function removeFeaturedImageFromContent(string $content, string $featuredImageUrl): string
    {
        // Escape special regex characters in URL
        $escapedUrl = preg_quote($featuredImageUrl, '/');

        // Remove first img tag if it matches the featured image URL
        $pattern = '/^\s*<img[^>]+src=["\']'.$escapedUrl.'["\'][^>]*>\s*/i';
        $cleaned = preg_replace($pattern, '', $content, 1);

        return $cleaned;
    }

    /**
     * Attach featured image from URL to blog post
     */
    protected function attachFeaturedImage(BlogPost $post, string $imageUrl): void
    {
        try {
            $post->addMediaFromUrl($imageUrl)
                ->toMediaCollection('featured_image');

            Log::channel(config('blog-automation.logging.channel'))
                ->info('[BlogAutomation] Featured image attached', [
                    'post_id' => $post->id,
                    'image_url' => $imageUrl,
                ]);
        } catch (\Exception $e) {
            Log::channel(config('blog-automation.logging.channel'))
                ->warning('[BlogAutomation] Failed to attach featured image', [
                    'post_id' => $post->id,
                    'image_url' => $imageUrl,
                    'error' => $e->getMessage(),
                ]);
        }
    }

    /**
     * Notify all admin users
     */
    protected function notifyAdmins($notification): void
    {
        $admins = User::where('role', UserRole::ADMIN)->get();

        foreach ($admins as $admin) {
            $admin->notify($notification);
        }
    }
}
