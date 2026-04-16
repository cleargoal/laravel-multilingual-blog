<?php

declare(strict_types=1);

namespace Cleargoal\Blog;

use Cleargoal\Blog\Console\Commands\BlogInstallCommand;
use Cleargoal\Blog\Contracts\BlogAuthorizer;
use Cleargoal\Blog\Contracts\BlogTranslationProvider;
use Cleargoal\Blog\Contracts\ContentSanitizer;
use Cleargoal\Blog\Filament\Resources\BlogCategoryResource;
use Cleargoal\Blog\Filament\Resources\BlogPostResource;
use Cleargoal\Blog\Models\BlogComment;
use Cleargoal\Blog\Models\BlogPost;
use Cleargoal\Blog\Observers\BlogCommentObserver;
use Cleargoal\Blog\Observers\BlogPostCacheObserver;
use Cleargoal\Blog\Observers\BlogPostObserver;
use Cleargoal\Blog\Services\DefaultBlogAuthorizer;
use Cleargoal\Blog\Services\DefaultContentSanitizer;
use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;

class BlogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge configurations
        $this->mergeConfigFrom(__DIR__.'/../config/blog.php', 'blog');
        $this->mergeConfigFrom(__DIR__.'/../config/blog-automation.php', 'blog-automation');

        // Bind interfaces to implementations
        $this->app->singleton(ContentSanitizer::class, function ($app) {
            return $app->make(config('blog.sanitizer', DefaultContentSanitizer::class));
        });

        $this->app->singleton(BlogAuthorizer::class, function ($app) {
            return $app->make(config('blog.authorization.authorizer', DefaultBlogAuthorizer::class));
        });

        // Bind translation provider (if configured)
        if (config('blog.translation.provider')) {
            $this->app->singleton(BlogTranslationProvider::class, function ($app) {
                return $app->make(config('blog.translation.provider'));
            });
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configurations
        $this->publishes([
            __DIR__.'/../config/blog.php' => config_path('blog.php'),
        ], ['blog-config', 'config']);

        $this->publishes([
            __DIR__.'/../config/blog-automation.php' => config_path('blog-automation.php'),
        ], ['blog-automation-config', 'config']);

        // Auto-load migrations from package (no need to publish unless customizing)
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Publish migrations (optional - only if user wants to customize)
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], ['blog-migrations', 'migrations']);

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/blog'),
        ], ['blog-views', 'views']);

        // Publish language files
        if (is_dir(__DIR__.'/../resources/lang')) {
            $this->publishes([
                __DIR__.'/../resources/lang' => $this->app->langPath('vendor/blog'),
            ], ['blog-lang', 'lang']);
        }

        // Load package routes
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__.'/../routes/blog.php');
        }

        // Load package views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'blog');

        // Load translations
        if (is_dir(__DIR__.'/../resources/lang')) {
            $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'blog');
        }

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                BlogInstallCommand::class,
            ]);

            // Show installation reminder on first install
            $this->showInstallationReminder();
        }

        // Register observers
        BlogPost::observe(BlogPostObserver::class);
        BlogComment::observe(BlogCommentObserver::class);

        if (config('blog.cache.enabled')) {
            BlogPost::observe(BlogPostCacheObserver::class);
        }

        // Boot Filament resources (if Filament is installed)
        if (class_exists('\\Filament\\Facades\\Filament')) {
            $this->bootFilament();
        }
    }

    /**
     * Boot Filament resources.
     */
    protected function bootFilament(): void
    {
        Filament::serving(function () {
            Filament::registerResources([
                BlogPostResource::class,
                BlogCategoryResource::class,
            ]);
        });
    }

    /**
     * Show installation reminder when migrations have not been run yet.
     */
    protected function showInstallationReminder(): void
    {
        // Output a helpful reminder after app is booted
        $this->app->booted(function () {
            fwrite(STDOUT, "\n");
            fwrite(STDOUT, "  \033[32mINFO\033[0m  Laravel Blog Package installed.\n");
            fwrite(STDOUT, "        Run [php artisan migrate] to create blog tables.\n");
            fwrite(STDOUT, "\n");
        });
    }
}
