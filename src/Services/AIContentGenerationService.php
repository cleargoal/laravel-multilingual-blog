<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Services;

use Cleargoal\Blog\Models\PlatformSetting;
use App\Services\AI\AnthropicContentProvider;
use Illuminate\Support\Facades\Log;

class AIContentGenerationService
{
    protected $provider;

    public function __construct()
    {
        $providerName = PlatformSetting::get(
            'blog_ai_provider',
            config('blog-automation.ai.provider', 'anthropic')
        );

        $this->provider = match ($providerName) {
            'anthropic' => app(AnthropicContentProvider::class),
            default => throw new \Exception("Unsupported AI provider: {$providerName}"),
        };
    }

    /**
     * Generate a complete blog post using AI
     */
    public function generateBlogPost(string $topic, string $category, array $context = []): array
    {
        $retries = 3;
        $lastException = null;

        for ($attempt = 1; $attempt <= $retries; $attempt++) {
            try {
                Log::channel(config('blog-automation.logging.channel'))
                    ->info('[BlogAutomation] Generating blog post', [
                        'topic' => $topic,
                        'category' => $category,
                        'attempt' => $attempt,
                    ]);

                $content = $this->provider->generateContent($topic, $category, $context);

                // Validate generated content
                $this->validateContent($content);

                return $content;
            } catch (\Exception $e) {
                $lastException = $e;

                Log::channel(config('blog-automation.logging.channel'))
                    ->warning('[BlogAutomation] AI generation attempt failed', [
                        'attempt' => $attempt,
                        'error' => $e->getMessage(),
                    ]);

                if ($attempt < $retries) {
                    sleep($attempt * 2); // Exponential backoff
                }
            }
        }

        throw new \Exception("Failed to generate blog post after {$retries} attempts: ".$lastException->getMessage());
    }

    /**
     * Generate excerpt from content
     */
    public function generateExcerpt(string $content, int $maxLength = 200): string
    {
        // Strip HTML tags
        $text = strip_tags($content);

        // Truncate to max length
        if (strlen($text) <= $maxLength) {
            return $text;
        }

        // Find last complete sentence within limit
        $excerpt = substr($text, 0, $maxLength);
        $lastPeriod = strrpos($excerpt, '.');
        $lastQuestion = strrpos($excerpt, '?');
        $lastExclamation = strrpos($excerpt, '!');

        $lastSentenceEnd = max($lastPeriod, $lastQuestion, $lastExclamation);

        if ($lastSentenceEnd !== false && $lastSentenceEnd > $maxLength * 0.7) {
            return substr($text, 0, $lastSentenceEnd + 1);
        }

        // Fall back to word boundary
        $excerpt = substr($text, 0, $maxLength);
        $lastSpace = strrpos($excerpt, ' ');

        if ($lastSpace !== false) {
            return substr($text, 0, $lastSpace).'...';
        }

        return $excerpt.'...';
    }

    /**
     * Validate generated content structure
     */
    protected function validateContent(array $content): void
    {
        $required = ['title', 'excerpt', 'content'];

        foreach ($required as $field) {
            if (! isset($content[$field]) || empty($content[$field])) {
                throw new \Exception("Generated content missing required field: {$field}");
            }
        }

        // Validate minimum lengths
        if (strlen($content['title']) < 10) {
            throw new \Exception('Generated title too short');
        }

        if (strlen($content['content']) < 500) {
            throw new \Exception('Generated content too short');
        }
    }
}
