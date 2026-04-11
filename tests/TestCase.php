<?php

namespace Cleargoal\Blog\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;
use Cleargoal\Blog\BlogServiceProvider;
use Cleargoal\Blog\Contracts\BlogAuthor;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Enable foreign key constraints for SQLite
        if (config('database.connections.testing.driver') === 'sqlite') {
            \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = ON;');
        }

        // Run blog migration manually
        $migration = include __DIR__.'/../database/migrations/2024_01_01_000000_create_blog_tables.php';
        $migration->up();

        // Create users table for testing
        \Illuminate\Support\Facades\Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->boolean('is_admin')->default(false);
            $table->boolean('can_blog')->default(false);
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
        });

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Cleargoal\\Blog\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            BlogServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        config()->set('database.default', 'testing');

        // Try SQLite first, fallback to MySQL if SQLite is not available
        if (extension_loaded('pdo_sqlite')) {
            config()->set('database.connections.testing', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]);
        } else {
            // Fallback to MySQL
            config()->set('database.connections.testing', [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => 'blog_package_testing',
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
            ]);

            // Create test database if using MySQL
            try {
                $pdo = new \PDO(
                    'mysql:host='.config('database.connections.testing.host'),
                    config('database.connections.testing.username'),
                    config('database.connections.testing.password')
                );
                $pdo->exec('DROP DATABASE IF EXISTS blog_package_testing');
                $pdo->exec('CREATE DATABASE blog_package_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            } catch (\Exception $e) {
                // If MySQL connection fails, skip
            }
        }

        // Set blog configuration for testing
        config()->set('blog.models.user', TestUser::class);
        config()->set('blog.default_locale', 'en');
        config()->set('blog.languages', ['en', 'uk', 'de']);
        config()->set('blog.cache.enabled', false); // Disable cache in tests
    }

    /**
     * Create a test user.
     */
    protected function createUser(array $attributes = []): TestUser
    {
        static $counter = 0;
        $counter++;

        return TestUser::create(array_merge([
            'name' => 'Test User ' . $counter,
            'email' => 'test' . $counter . '@example.com',
            'is_admin' => false,
            'can_blog' => true,
        ], $attributes));
    }

    /**
     * Create an admin user.
     */
    protected function createAdmin(array $attributes = []): TestUser
    {
        return $this->createUser(array_merge([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'is_admin' => true,
            'can_blog' => true,
        ], $attributes));
    }
}

/**
 * Test User Model implementing BlogAuthor interface.
 */
class TestUser extends \Illuminate\Database\Eloquent\Model implements BlogAuthor, \Illuminate\Contracts\Auth\Authenticatable
{
    use \Cleargoal\Blog\Traits\HasBlogPosts;
    use \Illuminate\Auth\Authenticatable;

    protected $table = 'users';
    protected $guarded = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function canManageBlogPosts(): bool
    {
        return $this->can_blog || $this->is_admin;
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function isModerator(): bool
    {
        return false; // No moderator role in test user
    }
}
