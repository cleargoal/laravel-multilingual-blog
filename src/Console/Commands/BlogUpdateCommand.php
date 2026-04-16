<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class BlogUpdateCommand extends Command
{
    protected $signature = 'blog:update 
                            {--force : Force update without confirmation}
                            {--dry-run : Show what would be updated without making changes}';

    protected $description = 'Update the Laravel Blog package to the latest version';

    public function handle(): int
    {
        $this->info('Checking for blog package updates...');

        // Show current version
        $currentVersion = $this->getCurrentVersion();
        $this->info("Current version: {$currentVersion}");

        if ($this->option('dry-run')) {
            $this->showPendingUpdates();

            return Command::SUCCESS;
        }

        // Check for new migrations
        $pendingMigrations = $this->getPendingMigrations();

        if (empty($pendingMigrations) && ! $this->option('force')) {
            $this->info('✓ Package is up to date! No migrations needed.');

            return Command::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm(
            'Found '.count($pendingMigrations).' pending migration(s). Run update?',
            true
        )) {
            $this->warn('Update cancelled.');

            return Command::SUCCESS;
        }

        // Run migrations
        $this->info('Running migrations...');
        Artisan::call('migrate', ['--force' => true, '--path' => 'vendor/cleargoal/laravel-multilingual-blog/database/migrations']);
        $this->info('✓ Database updated');

        // Clear cache
        $this->info('Clearing cache...');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');
        $this->info('✓ Cache cleared');

        // Update version file
        $this->updateVersionFile();

        $this->newLine();
        $this->info('✅ Blog package updated successfully!');

        // Show changelog
        $this->showChangelog();

        return Command::SUCCESS;
    }

    protected function getCurrentVersion(): string
    {
        $versionFile = base_path('vendor/cleargoal/laravel-multilingual-blog/composer.json');
        if (file_exists($versionFile)) {
            $composer = json_decode(file_get_contents($versionFile), true);

            return $composer['version'] ?? 'unknown';
        }

        return 'unknown';
    }

    protected function getPendingMigrations(): array
    {
        // Get all migration files from package
        $packageMigrations = glob(base_path('vendor/cleargoal/laravel-multilingual-blog/database/migrations/*.php'));

        // Get already run migrations
        $runMigrations = DB::table('migrations')
            ->where('migration', 'like', '%blog%')
            ->pluck('migration')
            ->toArray();

        $pending = [];
        foreach ($packageMigrations as $file) {
            $filename = basename($file, '.php');
            if (! in_array($filename, $runMigrations)) {
                $pending[] = $filename;
            }
        }

        return $pending;
    }

    protected function showPendingUpdates(): void
    {
        $pending = $this->getPendingMigrations();

        if (empty($pending)) {
            $this->info('No pending updates.');

            return;
        }

        $this->warn('Pending migrations:');
        foreach ($pending as $migration) {
            $this->line("  - {$migration}");
        }
    }

    protected function updateVersionFile(): void
    {
        $versionFile = storage_path('app/blog-package-version.txt');
        $version = $this->getCurrentVersion();
        file_put_contents($versionFile, $version);
    }

    protected function showChangelog(): void
    {
        $changelogFile = base_path('vendor/cleargoal/laravel-multilingual-blog/CHANGELOG.md');

        if (file_exists($changelogFile)) {
            $this->newLine();
            $this->info('Recent changes:');
            $changelog = file_get_contents($changelogFile);
            // Show only first version section
            preg_match('/## \[.*?\].*?(?=## \[|$)/s', $changelog, $matches);
            if ($matches) {
                $lines = explode("\n", trim($matches[0]));
                foreach (array_slice($lines, 0, 20) as $line) {
                    $this->line($line);
                }
            }
        }
    }
}
