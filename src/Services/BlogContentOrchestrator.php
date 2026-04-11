<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Services;

use Cleargoal\Blog\Contracts\BlogAuthor;
use Cleargoal\Blog\Jobs\TranslateBlogPostJob;
use Cleargoal\Blog\Models\BlogCategory;
use Cleargoal\Blog\Models\BlogPost;
use Illuminate\Support\Facades\Log;

class BlogContentOrchestrator
{
    public function __construct(
        protected AIContentGenerationService $aiService
    ) {}

    /**
     * Generate an original AI blog post
     */
    public function generateOriginalPost(?string $topic = null, ?string $category = null): BlogPost
    {
        // Select topic if not provided
        if (! $topic || ! $category) {
            $topicData = $this->selectTopic();
            $topic = $topicData['topic'];
            $category = $topicData['category'];
        }

        Log::channel(config('blog-automation.logging.channel'))
            ->info('[BlogAutomation] Generating original post', [
                'topic' => $topic,
                'category' => $category,
            ]);

        // Generate content
        $content = $this->aiService->generateBlogPost($topic, $category);

        // Select author based on category
        $author = $this->selectAuthor($category);

        // Get category model using proper mapping
        $categoryModel = $this->getCategoryModel($category);

        // Create blog post
        $publishImmediately = config('blog-automation.scheduler.publish_immediately', false);
        $post = BlogPost::create([
            'author_id' => $author->id,
            'category_id' => $categoryModel->id,
            'title' => ['en' => $content['title']],
            'excerpt' => ['en' => $content['excerpt']],
            'content' => ['en' => $content['content']],
            'original_locale' => 'en',
            'status' => $publishImmediately ? 'published' : 'draft',
            'published_at' => $publishImmediately ? now() : null,
            'is_featured' => rand(1, 100) <= (config('blog-automation.scheduler.featured_probability') * 100),
            'generated_by_ai' => true,
            'ai_model_used' => config('blog-automation.ai.model'),
            'generation_prompt_version' => 'v1.0',
        ]);

        Log::channel(config('blog-automation.logging.channel'))
            ->info('[BlogAutomation] Original post created', [
                'post_id' => $post->id,
                'slug' => $post->slug,
                'topic' => $topic,
            ]);

        // Queue translation
        $this->queueTranslation($post);

        // Fetch and attach featured image from Unsplash (if enabled)
        if (config('blog-automation.image_fetching.enabled', false)) {
            try {
                $imageFetcher = app(UnsplashImageService::class);
                $imageFetcher->fetchAndAttachImage($post, $topic, $category);
            } catch (\Exception $e) {
                // Never let image fetching block post creation
                // Skip ERROR logs in testing to avoid polluting test output
                if (! app()->environment('testing')) {
                    Log::channel(config('blog-automation.logging.channel'))
                        ->error('[BlogAutomation] Featured image fetching exception (post created successfully)', [
                            'post_id' => $post->id,
                            'error' => $e->getMessage(),
                        ]);
                }
            }
        }

        // Process in-text images (if enabled)
        if (config('blog-automation.image_fetching.enabled', false)) {
            try {
                $this->processInTextImages($post);
            } catch (\Exception $e) {
                // Never let image processing block post creation
                // Skip ERROR logs in testing to avoid polluting test output
                if (! app()->environment('testing')) {
                    Log::channel(config('blog-automation.logging.channel'))
                        ->error('[BlogAutomation] In-text image processing exception (post created successfully)', [
                            'post_id' => $post->id,
                            'error' => $e->getMessage(),
                        ]);
                }
            }
        }

        // Process charts (convert to QuickChart URLs)
        try {
            $this->processCharts($post);
        } catch (\Exception $e) {
            // Never let chart processing block post creation
            // Skip ERROR logs in testing to avoid polluting test output
            if (! app()->environment('testing')) {
                Log::channel(config('blog-automation.logging.channel'))
                    ->error('[BlogAutomation] Chart processing exception (post created successfully)', [
                        'post_id' => $post->id,
                        'error' => $e->getMessage(),
                    ]);
            }
        }

        // Sanitize SVGs
        try {
            $this->sanitizeSVGs($post);
        } catch (\Exception $e) {
            // Never let SVG sanitization block post creation
            // Skip ERROR logs in testing to avoid polluting test output
            if (! app()->environment('testing')) {
                Log::channel(config('blog-automation.logging.channel'))
                    ->error('[BlogAutomation] SVG sanitization exception (post created successfully)', [
                        'post_id' => $post->id,
                        'error' => $e->getMessage(),
                    ]);
            }
        }

        return $post;
    }

    /**
     * Select a topic from configuration
     * Rule: At least 1 buyer-targeted post per month
     */
    public function selectTopic(): array
    {
        $topics = config('blog-automation.topics');

        // Check if we need to prioritize buyer-targeted post this month
        if ($this->needsBuyerPostThisMonth()) {
            // Select from buyer_guides category only
            $buyerTopics = $topics['buyer_guides'] ?? [];

            if (! empty($buyerTopics)) {
                $recentBuyerTopics = $this->getRecentTopics('buyer_guides');
                $availableBuyerTopics = array_diff($buyerTopics, $recentBuyerTopics);

                // If all buyer topics used recently, reset and allow all
                if (empty($availableBuyerTopics)) {
                    $availableBuyerTopics = $buyerTopics;
                }

                $topic = $availableBuyerTopics[array_rand($availableBuyerTopics)];

                Log::channel(config('blog-automation.logging.channel'))
                    ->info('[BlogAutomation] Selected buyer-targeted topic (monthly requirement)', [
                        'topic' => $topic,
                    ]);

                return [
                    'topic' => $topic,
                    'category' => 'buyer_guides',
                ];
            }
        }

        // Normal selection: all categories
        $recentTopics = $this->getRecentTopics();

        // Build flat list of all topics with their categories
        $allTopics = [];
        foreach ($topics as $category => $categoryTopics) {
            foreach ($categoryTopics as $topic) {
                // Skip if used recently
                if (! in_array($topic, $recentTopics)) {
                    $allTopics[] = [
                        'topic' => $topic,
                        'category' => $category,
                    ];
                }
            }
        }

        // If all topics used recently, reset and allow all
        if (empty($allTopics)) {
            foreach ($topics as $category => $categoryTopics) {
                foreach ($categoryTopics as $topic) {
                    $allTopics[] = [
                        'topic' => $topic,
                        'category' => $category,
                    ];
                }
            }
        }

        // Select random topic
        return $allTopics[array_rand($allTopics)];
    }

    /**
     * Check if we need a buyer-targeted post this month
     */
    protected function needsBuyerPostThisMonth(): bool
    {
        $currentMonth = now()->format('Y-m');

        // Count buyer-targeted posts created this month
        $buyerPostsThisMonth = BlogPost::where('generated_by_ai', true)
            ->whereHas('category', function ($query) {
                $query->where('slug', 'buyer-guides')
                    ->orWhere('name->en', 'ILIKE', '%buyer%')
                    ->orWhere('name->en', 'ILIKE', '%client%');
            })
            ->whereRaw("DATE_TRUNC('month', created_at) = ?", [$currentMonth.'-01'])
            ->count();

        return $buyerPostsThisMonth === 0;
    }

    /**
     * Get recently used topics (last 30 days)
     *
     * @param  string|null  $category  If provided, only return topics from this category
     */
    protected function getRecentTopics(?string $category = null): array
    {
        $query = BlogPost::where('generated_by_ai', true)
            ->where('created_at', '>=', now()->subDays(30))
            ->pluck('title');

        // Extract English titles
        $recentTitles = $query->map(function ($title) {
            return is_array($title) ? ($title['en'] ?? '') : (string) $title;
        })->filter()->toArray();

        // Match against topics
        $topics = config('blog-automation.topics');
        $allTopics = $category && isset($topics[$category])
            ? $topics[$category]
            : collect($topics)->flatten()->toArray();

        $recentTopics = [];
        foreach ($allTopics as $topic) {
            foreach ($recentTitles as $recentTitle) {
                // Check if topic is contained in recent title (fuzzy match)
                $topicWords = explode(' ', strtolower($topic));
                $titleWords = explode(' ', strtolower($recentTitle));

                // If at least 3 words match, consider it the same topic
                $matches = count(array_intersect($topicWords, $titleWords));
                if ($matches >= 3) {
                    $recentTopics[] = $topic;
                    break;
                }
            }
        }

        return array_unique($recentTopics);
    }

    /**
     * Select appropriate author based on content type
     *
     * Override this method or configure author selection via config/blog-automation.php
     */
    public function selectAuthor(string $contentType)
    {
        $userModel = config('blog.models.user');

        // Get configured author IDs for different content types (if available)
        $authorMapping = config('blog-automation.author_mapping', []);

        if (isset($authorMapping[$contentType])) {
            $authorIds = is_array($authorMapping[$contentType])
                ? $authorMapping[$contentType]
                : [$authorMapping[$contentType]];

            $author = $userModel::whereIn('id', $authorIds)
                ->where('is_demo', false)
                ->inRandomOrder()
                ->first();

            if ($author) {
                return $author;
            }
        }

        // Fallback: select any user who can manage blog posts
        $author = $userModel::where('is_demo', false)
            ->inRandomOrder()
            ->get()
            ->filter(fn ($user) => $user instanceof BlogAuthor && $user->canManageBlogPosts())
            ->first();

        if (! $author) {
            throw new \Exception('No suitable author found for blog post. Configure authors in config/blog-automation.php');
        }

        return $author;
    }

    /**
     * Get blog category model from config category name
     * Maps config category slugs to database category slugs
     */
    protected function getCategoryModel(string $configCategory): BlogCategory
    {
        // Map config categories to database category slugs
        $categoryMapping = [
            'platform_features' => 'tutorials', // Platform features go to Tutorials
            'freelancing_tips' => 'freelancing-tips',
            'tutorials' => 'tutorials',
            'buyer_guides' => 'client-relations', // Buyer guides go to Client Relations
            'client_relations' => 'client-relations',
            'tools' => 'tools-resources',
            'tools_resources' => 'tools-resources',
        ];

        // Normalize config category (convert underscores to hyphens)
        $normalizedConfig = str_replace('_', '-', $configCategory);

        // Get mapped database slug
        $dbSlug = $categoryMapping[$configCategory]
            ?? $categoryMapping[$normalizedConfig]
            ?? $normalizedConfig;

        // Find category by slug
        $category = BlogCategory::where('slug', $dbSlug)->first();

        // If not found, try by name (fallback)
        if (! $category) {
            $category = BlogCategory::where('name->en', 'ILIKE', "%{$configCategory}%")->first();
        }

        // Final fallback: first category
        if (! $category) {
            Log::warning('[BlogAutomation] Category not found, using fallback', [
                'config_category' => $configCategory,
                'mapped_slug' => $dbSlug,
            ]);
            $category = BlogCategory::first();
        }

        return $category;
    }

    /**
     * Queue translation job for a post
     */
    public function queueTranslation(BlogPost $post): void
    {
        $translationEnabled = config('blog-automation.translation.enabled', true);
        $translationQueue = config('blog-automation.translation.queue', true);

        if ($translationEnabled && $translationQueue) {
            $delay = (int) config('blog-automation.translation.delay_seconds', 5);

            TranslateBlogPostJob::dispatch($post)->delay(now()->addSeconds($delay));

            Log::channel(config('blog-automation.logging.channel'))
                ->info('[BlogAutomation] Translation queued', [
                    'post_id' => $post->id,
                    'delay' => $delay,
                ]);
        }
    }

    /**
     * Process in-text image placeholders and replace with Unsplash images
     */
    protected function processInTextImages(BlogPost $post): void
    {
        $content = $post->getTranslation('content', 'en');

        // Find all IMAGE:keyword patterns in the content
        // Pattern: ![alt text](IMAGE:search-keyword)
        preg_match_all('/!\[([^\]]*)\]\(IMAGE:([^)]+)\)/', $content, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            Log::channel(config('blog-automation.logging.channel'))
                ->info('[BlogAutomation] No in-text image placeholders found', [
                    'post_id' => $post->id,
                ]);

            return;
        }

        Log::channel(config('blog-automation.logging.channel'))
            ->info('[BlogAutomation] Processing in-text images', [
                'post_id' => $post->id,
                'count' => count($matches),
            ]);

        $imageFetcher = app(UnsplashImageService::class);
        $updatedContent = $content;

        foreach ($matches as $match) {
            $fullMatch = $match[0]; // ![alt text](IMAGE:keyword)
            $altText = $match[1]; // alt text
            $keyword = $match[2]; // keyword

            // Fetch image from Unsplash
            $imageUrl = $imageFetcher->fetchImageUrl($keyword);

            if ($imageUrl) {
                // Replace placeholder with actual image URL
                $replacement = "![{$altText}]({$imageUrl})";
                $updatedContent = str_replace($fullMatch, $replacement, $updatedContent);

                Log::channel(config('blog-automation.logging.channel'))
                    ->info('[BlogAutomation] In-text image replaced', [
                        'post_id' => $post->id,
                        'keyword' => $keyword,
                        'alt_text' => $altText,
                    ]);
            } else {
                // Remove placeholder if image fetch failed
                $updatedContent = str_replace($fullMatch, '', $updatedContent);

                Log::channel(config('blog-automation.logging.channel'))
                    ->warning('[BlogAutomation] In-text image fetch failed, placeholder removed', [
                        'post_id' => $post->id,
                        'keyword' => $keyword,
                    ]);
            }
        }

        // Refresh post to get latest translations (in case translation job already ran)
        // then update only English content while preserving other languages
        $post->refresh();
        $post->setTranslation('content', 'en', $updatedContent);
        $post->save();

        Log::channel(config('blog-automation.logging.channel'))
            ->info('[BlogAutomation] In-text images processed', [
                'post_id' => $post->id,
                'replaced' => count($matches),
            ]);
    }

    /**
     * Process chart placeholders and replace with QuickChart URLs
     */
    protected function processCharts(BlogPost $post): void
    {
        $content = $post->getTranslation('content', 'en');

        // Find all CHART:{json} patterns in the content
        // Pattern: ![alt text](CHART:{...json...})
        preg_match_all('/!\[([^\]]*)\]\(CHART:(\{[^)]+\})\)/', $content, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            Log::channel(config('blog-automation.logging.channel'))
                ->info('[BlogAutomation] No chart placeholders found', [
                    'post_id' => $post->id,
                ]);

            return;
        }

        Log::channel(config('blog-automation.logging.channel'))
            ->info('[BlogAutomation] Processing charts', [
                'post_id' => $post->id,
                'count' => count($matches),
            ]);

        $updatedContent = $content;

        foreach ($matches as $match) {
            $fullMatch = $match[0]; // ![alt text](CHART:{...})
            $altText = $match[1]; // alt text
            $chartConfig = $match[2]; // {...json...}

            // Validate JSON
            $config = json_decode($chartConfig, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::channel(config('blog-automation.logging.channel'))
                    ->warning('[BlogAutomation] Invalid chart JSON, placeholder removed', [
                        'post_id' => $post->id,
                        'error' => json_last_error_msg(),
                    ]);

                $updatedContent = str_replace($fullMatch, '', $updatedContent);

                continue;
            }

            // Generate QuickChart URL
            $chartUrl = 'https://quickchart.io/chart?c='.urlencode($chartConfig);

            // Replace placeholder with QuickChart image
            $replacement = "![{$altText}]({$chartUrl})";
            $updatedContent = str_replace($fullMatch, $replacement, $updatedContent);

            Log::channel(config('blog-automation.logging.channel'))
                ->info('[BlogAutomation] Chart replaced', [
                    'post_id' => $post->id,
                    'alt_text' => $altText,
                    'type' => $config['type'] ?? 'unknown',
                ]);
        }

        // Refresh post to get latest translations (in case translation job already ran)
        // then update only English content while preserving other languages
        $post->refresh();
        $post->setTranslation('content', 'en', $updatedContent);
        $post->save();

        Log::channel(config('blog-automation.logging.channel'))
            ->info('[BlogAutomation] Charts processed', [
                'post_id' => $post->id,
                'replaced' => count($matches),
            ]);
    }

    /**
     * Sanitize inline SVGs to prevent XSS
     */
    protected function sanitizeSVGs(BlogPost $post): void
    {
        $content = $post->getTranslation('content', 'en');

        // Find all SVG elements
        preg_match_all('/<svg[^>]*>.*?<\/svg>/is', $content, $matches);

        if (empty($matches[0])) {
            return;
        }

        Log::channel(config('blog-automation.logging.channel'))
            ->info('[BlogAutomation] Sanitizing SVGs', [
                'post_id' => $post->id,
                'count' => count($matches[0]),
            ]);

        $updatedContent = $content;

        foreach ($matches[0] as $svg) {
            // Check size constraint (max 200x200)
            if (preg_match('/width=["\']?(\d+)/', $svg, $widthMatch)) {
                if ((int) $widthMatch[1] > 200) {
                    Log::channel(config('blog-automation.logging.channel'))
                        ->warning('[BlogAutomation] SVG too large, removed', [
                            'post_id' => $post->id,
                            'width' => $widthMatch[1],
                        ]);
                    $updatedContent = str_replace($svg, '', $updatedContent);

                    continue;
                }
            }

            // Remove dangerous attributes/tags
            $sanitized = $svg;

            // Remove script tags
            $sanitized = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $sanitized);

            // Remove event handlers (onclick, onload, etc.)
            $sanitized = preg_replace('/\s+on\w+\s*=\s*["\'][^"\']*["\']/i', '', $sanitized);

            // Remove javascript: URLs
            $sanitized = preg_replace('/javascript:/i', '', $sanitized);

            // Allow only safe tags: svg, circle, rect, path, text, line, polyline, polygon, ellipse, g, defs
            $allowedTags = ['svg', 'circle', 'rect', 'path', 'text', 'line', 'polyline', 'polygon', 'ellipse', 'g', 'defs'];
            $pattern = '/<(?!\/?)(?!'.implode('|', $allowedTags).'\b)[^>]+>/i';
            $sanitized = preg_replace($pattern, '', $sanitized);

            // Replace original with sanitized version
            if ($sanitized !== $svg) {
                $updatedContent = str_replace($svg, $sanitized, $updatedContent);

                Log::channel(config('blog-automation.logging.channel'))
                    ->info('[BlogAutomation] SVG sanitized', [
                        'post_id' => $post->id,
                    ]);
            }
        }

        // Refresh post to get latest translations (in case translation job already ran)
        // then update only English content while preserving other languages
        $post->refresh();
        $post->setTranslation('content', 'en', $updatedContent);
        $post->save();
    }
}
