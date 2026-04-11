<?php

declare(strict_types=1);

namespace YourVendor\Blog\Database\Seeders;

use Illuminate\Database\Seeder;
use YourVendor\Blog\Models\BlogCategory;
use YourVendor\Blog\Models\BlogPost;
use YourVendor\Blog\Models\PostTag;

class BlogSeeder extends Seeder
{
    /**
     * Seed the blog database with sample data.
     *
     * This seeder creates:
     * - Blog categories (with hierarchy)
     * - Sample blog posts
     * - Tags
     */
    public function run(): void
    {
        $this->command?->info('Seeding blog data...');

        // Get or create a user to be the author
        $userModel = config('blog.models.user');
        $author = $userModel::first();

        if (! $author) {
            $this->command->warn('No users found. Please create a user first.');
            return;
        }

        $this->command?->info("Using user '{$author->getName()}' (ID: {$author->getId()}) as blog author");

        // Create categories
        $this->command?->info('Creating blog categories...');
        $categories = $this->createCategories();

        // Create tags
        $this->command?->info('Creating tags...');
        $tags = $this->createTags();

        // Create blog posts
        $this->command?->info('Creating sample blog posts...');
        $posts = $this->createBlogPosts($author, $categories, $tags);

        // Create comments
        $this->command?->info('Creating sample comments...');
        $this->createComments($author, $posts);

        $this->command?->info('Blog seeding completed successfully!');
    }

    /**
     * Create blog categories with hierarchy.
     */
    protected function createCategories(): array
    {
        $categories = [];

        // Parent categories
        $categories['tutorials'] = BlogCategory::create([
            'name' => ['en' => 'Tutorials', 'uk' => 'Підручники'],
            'description' => ['en' => 'Step-by-step guides and tutorials', 'uk' => 'Покрокові посібники та підручники'],
            'slug' => 'tutorials',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $categories['news'] = BlogCategory::create([
            'name' => ['en' => 'News', 'uk' => 'Новини'],
            'description' => ['en' => 'Latest news and updates', 'uk' => 'Останні новини та оновлення'],
            'slug' => 'news',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $categories['guides'] = BlogCategory::create([
            'name' => ['en' => 'Guides', 'uk' => 'Посібники'],
            'description' => ['en' => 'Comprehensive guides and how-tos', 'uk' => 'Вичерпні посібники'],
            'slug' => 'guides',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        // Child category (for hierarchical testing)
        $categories['php-tutorials'] = BlogCategory::create([
            'parent_id' => $categories['tutorials']->id,
            'name' => ['en' => 'PHP Tutorials', 'uk' => 'PHP Підручники'],
            'description' => ['en' => 'PHP specific tutorials', 'uk' => 'Підручники по PHP'],
            'slug' => 'php-tutorials',
            'is_active' => true,
            'sort_order' => 4,
        ]);

        return $categories;
    }

    /**
     * Create sample tags.
     */
    protected function createTags(): array
    {
        $tagNames = [
            'Laravel' => 'Laravel',
            'PHP' => 'PHP',
            'JavaScript' => 'JavaScript',
            'Vue.js' => 'Vue.js',
            'Best Practices' => 'Кращі практики',
            'Performance' => 'Продуктивність',
            'Security' => 'Безпека',
            'Testing' => 'Тестування',
        ];

        $tags = [];
        foreach ($tagNames as $en => $uk) {
            $tags[] = PostTag::create([
                'name' => ['en' => $en, 'uk' => $uk],
                'slug' => \Illuminate\Support\Str::slug($en),
                'usage_count' => 0,
            ]);
        }

        return $tags;
    }

    /**
     * Create sample blog posts.
     */
    protected function createBlogPosts($author, array $categories, array $tags): array
    {
        $createdPosts = [];
        $posts = [
            [
                'title' => ['en' => 'Getting Started with Laravel', 'uk' => 'Початок роботи з Laravel'],
                'excerpt' => [
                    'en' => 'Learn the basics of Laravel framework and build your first application',
                    'uk' => 'Вивчіть основи фреймворку Laravel та створіть свій перший додаток',
                ],
                'content' => [
                    'en' => '<p>Laravel is a powerful PHP framework that makes web development enjoyable. In this tutorial, we\'ll cover the fundamentals...</p>',
                    'uk' => '<p>Laravel - це потужний PHP-фреймворк, який робить веб-розробку приємною. У цьому підручнику ми розглянемо основи...</p>',
                ],
                'category' => $categories['tutorials'],
                'tags' => [$tags[0], $tags[1]], // Laravel, PHP
                'is_featured' => true,
            ],
            [
                'title' => ['en' => 'Laravel 11 Released', 'uk' => 'Випущено Laravel 11'],
                'excerpt' => [
                    'en' => 'Explore the new features and improvements in Laravel 11',
                    'uk' => 'Дослідіть нові функції та покращення в Laravel 11',
                ],
                'content' => [
                    'en' => '<p>Laravel 11 brings exciting new features including improved performance, new Artisan commands, and better developer experience...</p>',
                    'uk' => '<p>Laravel 11 приносить захоплюючі нові функції, включаючи покращену продуктивність, нові команди Artisan та кращий досвід розробника...</p>',
                ],
                'category' => $categories['news'],
                'tags' => [$tags[0], $tags[1]], // Laravel, PHP
                'is_featured' => true,
            ],
            [
                'title' => ['en' => 'Vue.js Component Best Practices', 'uk' => 'Кращі практики компонентів Vue.js'],
                'excerpt' => [
                    'en' => 'Learn how to write maintainable and reusable Vue.js components',
                    'uk' => 'Навчіться писати підтримувані та багаторазові компоненти Vue.js',
                ],
                'content' => [
                    'en' => '<p>Building scalable Vue.js applications requires following best practices for component architecture...</p>',
                    'uk' => '<p>Створення масштабованих додатків Vue.js вимагає дотримання кращих практик для архітектури компонентів...</p>',
                ],
                'category' => $categories['guides'],
                'tags' => [$tags[2], $tags[3], $tags[4]], // JavaScript, Vue.js, Best Practices
                'is_featured' => false,
            ],
            [
                'title' => ['en' => 'Draft Post Example', 'uk' => 'Приклад чернетки'],
                'excerpt' => [
                    'en' => 'This is a draft post',
                    'uk' => 'Це чернетка',
                ],
                'content' => [
                    'en' => '<p>Draft content...</p>',
                    'uk' => '<p>Вміст чернетки...</p>',
                ],
                'category' => $categories['tutorials'],
                'tags' => [$tags[0]],
                'is_featured' => false,
                'status' => 'draft',
                'is_demo' => false,
            ],
            [
                'title' => ['en' => 'Demo Post for Testing', 'uk' => 'Демо-пост для тестування'],
                'excerpt' => [
                    'en' => 'This is a demo post',
                    'uk' => 'Це демо-пост',
                ],
                'content' => [
                    'en' => '<p>Demo content...</p>',
                    'uk' => '<p>Демо-вміст...</p>',
                ],
                'category' => $categories['news'],
                'tags' => [$tags[1]],
                'is_featured' => false,
                'status' => 'published',
                'is_demo' => true,
            ],
        ];

        foreach ($posts as $postData) {
            $status = $postData['status'] ?? 'published';
            $isDemo = $postData['is_demo'] ?? false;

            $post = BlogPost::create([
                'author_id' => $author->getId(),
                'category_id' => $postData['category']->id,
                'title' => $postData['title'],
                'excerpt' => $postData['excerpt'],
                'content' => $postData['content'],
                'status' => $status,
                'original_locale' => 'en',
                'published_at' => $status === 'published' ? now()->subDays(rand(1, 30)) : null,
                'is_featured' => $postData['is_featured'],
                'is_demo' => $isDemo,
                'views_count' => rand(100, 5000),
            ]);

            // Attach tags
            $tagIds = collect($postData['tags'])->pluck('id')->toArray();
            $post->tags()->attach($tagIds);

            // Update tag usage counts
            foreach ($postData['tags'] as $tag) {
                $tag->incrementUsage();
            }

            $createdPosts[] = $post;
        }

        return $createdPosts;
    }

    /**
     * Create sample comments.
     */
    protected function createComments($author, array $posts): void
    {
        $blogCommentModel = config('blog.models.blog_comment', \YourVendor\Blog\Models\BlogComment::class);

        foreach ($posts as $post) {
            // Skip draft and demo posts
            if ($post->status !== 'published' || $post->is_demo) {
                continue;
            }

            // Create parent comment
            $parentComment = $blogCommentModel::create([
                'blog_post_id' => $post->id,
                'author_id' => $author->getId(),
                'content' => 'Great article! Very helpful.',
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            // Create threaded reply
            $blogCommentModel::create([
                'blog_post_id' => $post->id,
                'author_id' => $author->getId(),
                'parent_id' => $parentComment->id,
                'content' => 'Thank you for your feedback!',
                'status' => 'approved',
                'approved_at' => now(),
            ]);
        }
    }
}
