<?php

declare(strict_types=1);

namespace YourVendor\Blog\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BlogInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blog:install
                            {--force : Overwrite existing files}
                            {--seed : Seed the database with sample blog data}
                            {--migrate : Run migrations automatically}
                            {--skip-migrations : Skip running migrations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Laravel Blog package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing Laravel Blog Package...');
        $this->newLine();

        // Publish configurations
        $this->publishConfigurations();

        // Publish migrations
        if (! $this->option('skip-migrations')) {
            $this->publishMigrations();
            $this->runMigrations();
        } else {
            $this->warn('Skipping migrations (--skip-migrations flag set)');
        }

        // Publish views (optional)
        $this->publishViews();

        // Seed database (optional)
        if ($this->option('seed')) {
            $this->seedDatabase();
        }

        // Display next steps
        $this->displayNextSteps();

        return Command::SUCCESS;
    }

    /**
     * Publish configuration files.
     */
    protected function publishConfigurations(): void
    {
        $this->info('Publishing configuration files...');

        $params = ['--tag' => 'blog-config'];
        if ($this->option('force')) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);

        $this->info('✓ Configuration files published to config/blog.php and config/blog-automation.php');
        $this->newLine();
    }

    /**
     * Publish migration files.
     */
    protected function publishMigrations(): void
    {
        $this->info('Publishing migration files...');

        $params = ['--tag' => 'blog-migrations'];
        if ($this->option('force')) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);

        $this->info('✓ Migration files published');
        $this->newLine();
    }

    /**
     * Run database migrations.
     */
    protected function runMigrations(): void
    {
        $shouldMigrate = $this->option('migrate') || $this->confirm('Run migrations now?', true);

        if ($shouldMigrate) {
            $this->info('Running migrations...');
            try {
                $this->call('migrate', ['--force' => true]);
                $this->info('✓ Migrations completed');
            } catch (\Exception $e) {
                $this->warn('Some migrations may have already been run: ' . $e->getMessage());
            }
            $this->newLine();
        } else {
            $this->warn('Remember to run migrations later: php artisan migrate');
            $this->newLine();
        }
    }

    /**
     * Publish view files (optional).
     */
    protected function publishViews(): void
    {
        if ($this->confirm('Publish view files for customization?', false)) {
            $this->info('Publishing view files...');

            $params = ['--tag' => 'blog-views'];
            if ($this->option('force')) {
                $params['--force'] = true;
            }

            $this->call('vendor:publish', $params);

            $this->info('✓ View files published to resources/views/vendor/blog');
            $this->newLine();
        }
    }

    /**
     * Seed the database with sample data.
     */
    protected function seedDatabase(): void
    {
        if (! class_exists('YourVendor\\Blog\\Database\\Seeders\\BlogSeeder')) {
            $this->warn('BlogSeeder not found. Skipping seeding.');
            return;
        }

        $this->info('Seeding database with sample blog data...');
        $this->call('db:seed', ['--class' => 'YourVendor\\Blog\\Database\\Seeders\\BlogSeeder']);
        $this->info('✓ Database seeded');
        $this->newLine();
    }

    /**
     * Display next steps for the user.
     */
    protected function displayNextSteps(): void
    {
        $this->info('✅ Blog package installed successfully!');
        $this->newLine();

        $this->comment('Next Steps:');
        $this->line('1. Add BlogAuthor interface to your User model:');
        $this->line('   - use YourVendor\Blog\Contracts\BlogAuthor;');
        $this->line('   - class User extends Authenticatable implements BlogAuthor');
        $this->line('   - Implement required methods: getId(), getName(), getEmail(), canManageBlogPosts()');
        $this->newLine();

        $this->line('2. Add HasBlogPosts trait to your User model:');
        $this->line('   - use YourVendor\Blog\Traits\HasBlogPosts;');
        $this->newLine();

        $this->line('3. Configure the package in config/blog.php:');
        $this->line('   - Set your User model');
        $this->line('   - Configure features you want to enable');
        $this->line('   - Customize routes, pagination, etc.');
        $this->newLine();

        $this->line('4. (Optional) Enable blog automation in config/blog-automation.php:');
        $this->line('   - Configure AI content generation');
        $this->line('   - Set up RSS feed imports');
        $this->line('   - Configure translation services');
        $this->newLine();

        $this->line('5. Visit /blog to see your blog in action!');
        $this->newLine();

        $this->comment('For more information, visit the documentation or README.md');
    }
}
