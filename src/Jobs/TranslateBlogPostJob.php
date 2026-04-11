<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Jobs;

use Cleargoal\Blog\Contracts\BlogTranslationProvider;
use Cleargoal\Blog\Models\BlogPost;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class TranslateBlogPostJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public BlogPost $blogPost
    ) {
        //
    }

    /**
     * Execute the job.
     * Implements graceful degradation - saves partial translations if some fields fail.
     */
    public function handle(BlogTranslationProvider $translationService): void
    {
        Log::info('Starting translation for blog post', [
            'id' => $this->blogPost->id,
            'original_locale' => $this->blogPost->original_locale,
        ]);

        $hasAnySuccess = false;

        // Translate title (with graceful failure)
        $titleTranslations = $translationService->translateToAll(
            $this->blogPost->getTranslation('title', $this->blogPost->original_locale),
            $this->blogPost->original_locale
        );

        if ($titleTranslations) {
            $this->blogPost->setTranslations('title', $titleTranslations);
            $hasAnySuccess = true;
        } else {
            Log::warning('Failed to translate blog post title - will remain in original language only', [
                'id' => $this->blogPost->id,
                'original_locale' => $this->blogPost->original_locale,
            ]);
        }

        // Translate excerpt (optional field — skip DeepL if empty)
        $originalExcerpt = $this->blogPost->getTranslation('excerpt', $this->blogPost->original_locale);
        if (! empty($originalExcerpt)) {
            $excerptTranslations = $translationService->translateToAll(
                $originalExcerpt,
                $this->blogPost->original_locale
            );

            if ($excerptTranslations) {
                $this->blogPost->setTranslations('excerpt', $excerptTranslations);
                $hasAnySuccess = true;
            } else {
                Log::warning('Failed to translate blog post excerpt - will remain in original language only', [
                    'id' => $this->blogPost->id,
                ]);
            }
        }

        // Translate content (with graceful failure)
        $contentTranslations = $translationService->translateToAll(
            $this->blogPost->getTranslation('content', $this->blogPost->original_locale),
            $this->blogPost->original_locale
        );

        if ($contentTranslations) {
            $this->blogPost->setTranslations('content', $contentTranslations);
            $hasAnySuccess = true;
        } else {
            Log::warning('Failed to translate blog post content - will remain in original language only', [
                'id' => $this->blogPost->id,
            ]);
        }

        // Save partial or full translations
        if ($hasAnySuccess) {
            $this->blogPost->save();
            Log::info('Successfully translated blog post (partial or full)', [
                'id' => $this->blogPost->id,
                'translated_fields' => collect([
                    'title' => $titleTranslations !== null,
                    'excerpt' => ! empty($originalExcerpt) && $excerptTranslations !== null,
                    'content' => $contentTranslations !== null,
                ])->filter()->keys()->toArray(),
            ]);
        } else {
            Log::warning('No fields were translated successfully - blog post remains in original language', [
                'id' => $this->blogPost->id,
                'original_locale' => $this->blogPost->original_locale,
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Blog post translation job failed', [
            'id' => $this->blogPost->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
