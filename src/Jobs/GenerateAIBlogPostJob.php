<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Jobs;

use Cleargoal\Blog\Services\BlogContentOrchestrator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateAIBlogPostJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 180; // 3 minutes

    public int $backoff = 60; // Retry after 60 seconds

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $topic,
        public string $category,
        public ?int $authorId = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(BlogContentOrchestrator $orchestrator): void
    {
        Log::channel(config('blog-automation.logging.channel'))
            ->info('[BlogAutomation] Generating AI blog post (job)', [
                'topic' => $this->topic,
                'category' => $this->category,
            ]);

        try {
            $post = $orchestrator->generateOriginalPost($this->topic, $this->category);

            Log::channel(config('blog-automation.logging.channel'))
                ->info('[BlogAutomation] AI blog post generated successfully', [
                    'post_id' => $post->id,
                    'slug' => $post->slug,
                ]);
        } catch (\Exception $e) {
            // Skip ERROR logs in testing to avoid polluting test output
            if (! app()->environment('testing')) {
                Log::channel(config('blog-automation.logging.channel'))
                    ->error('[BlogAutomation] AI blog post generation failed', [
                        'topic' => $this->topic,
                        'error' => $e->getMessage(),
                    ]);
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Skip ERROR logs in testing to avoid polluting test output
        if (! app()->environment('testing')) {
            Log::channel(config('blog-automation.logging.channel'))
                ->error('[BlogAutomation] AI blog post generation job failed permanently', [
                    'topic' => $this->topic,
                    'exception' => $exception->getMessage(),
                ]);
        }
    }
}
