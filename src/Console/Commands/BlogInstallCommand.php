<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Console\Commands;

use Illuminate\Console\Command;

class BlogInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blog:install
                            {--force : Overwrite existing files}
                            {--seed : Seed the database with sample blog data}';

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

        // Prompt for migrations reminder
        $this->showMigrationsReminder();

        // Publish views (optional)
        $this->publishViews();

        // Propose Filament installation (optional)
        $this->proposeFilamentInstallation();

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
     * Show migrations reminder.
     */
    protected function showMigrationsReminder(): void
    {
        $this->info('Migrations are auto-loaded from the package.');

        if ($this->confirm('Run migrations now?', true)) {
            $this->call('migrate', ['--force' => true]);
            $this->info('✓ Migrations completed');
        } else {
            $this->warn('Remember to run migrations later: php artisan migrate');
        }

        $this->newLine();

        // Optionally publish migrations for customization
        if ($this->confirm('Publish migration files for customization? (optional)', false)) {
            $params = ['--tag' => 'blog-migrations'];
            if ($this->option('force')) {
                $params['--force'] = true;
            }
            $this->call('vendor:publish', $params);
            $this->info('✓ Migration files published to database/migrations');
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
     * Propose Filament admin panel installation.
     */
    protected function proposeFilamentInstallation(): void
    {
        // Check if Filament is already installed
        if (class_exists('Filament\FilamentServiceProvider')) {
            $this->info('✓ Filament is already installed. Blog admin resources are available.');
            $this->newLine();

            return;
        }

        // Propose Filament installation
        if ($this->confirm('Would you like to install Filament admin panel for blog management?', false)) {
            $this->info('Installing Filament...');
            $this->newLine();

            try {
                // Install Filament via composer
                $this->call('composer:require', ['packages' => ['filament/filament'], '--no-interaction' => true]);

                // Run Filament install
                $this->call('filament:install', ['--no-interaction' => true]);

                $this->info('✓ Filament installed successfully!');
                $this->info('  Access your admin panel at /admin');
                $this->info('  Blog resources are available under the "Blog" navigation group.');
            } catch (\Exception $e) {
                $this->warn('Could not install Filament automatically.');
                $this->line('To install manually, run:');
                $this->line('  composer require filament/filament');
                $this->line('  php artisan filament:install');
            }

            $this->newLine();
        } else {
            $this->info('You can install Filament later by running:');
            $this->line('  composer require filament/filament');
            $this->line('  php artisan filament:install');
            $this->newLine();
        }
    }

    /**
     * Seed the database with sample data.
     */
    protected function seedDatabase(): void
    {
        if (! class_exists('Cleargoal\\Blog\\Database\\Seeders\\BlogSeeder')) {
            $this->warn('BlogSeeder not found. Skipping seeding.');

            return;
        }

        $this->info('Seeding database with sample blog data...');
        $this->call('db:seed', ['--class' => 'Cleargoal\\Blog\\Database\\Seeders\\BlogSeeder']);
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
        $this->line('   - use Cleargoal\Blog\Contracts\BlogAuthor;');
        $this->line('   - class User extends Authenticatable implements BlogAuthor');
        $this->line('   - Implement required methods: getId(), getName(), getEmail(), canManageBlogPosts()');
        $this->newLine();

        $this->line('2. Add HasBlogPosts trait to your User model:');
        $this->line('   - use Cleargoal\Blog\Traits\HasBlogPosts;');
        $this->newLine();

        $this->line('3. Configure the package in config/blog.php:');
        $this->line('   - Set your User model');
        $this->line('   - Configure features you want to enable');
        $this->line('   - Customize routes, pagination, etc.');
        $this->newLine();

        $this->line('4. (Optional) Install Filament admin panel:');
        $this->line('   - composer require filament/filament');
        $this->line('   - php artisan filament:install');
        $this->line('   - Access blog management at /admin');
        $this->newLine();

        $this->line('5. (Optional) Enable blog automation in config/blog-automation.php:');
        $this->line('   - Configure AI content generation');
        $this->line('   - Set up RSS feed imports');
        $this->line('   - Configure translation services');
        $this->newLine();

        $this->line('6. Visit /blog to see your blog in action!');
        $this->newLine();

        $this->comment('For more information, visit the documentation or README.md');
    }
}
