<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Blog Categories Table
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('blog_categories')->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->json('name'); // translatable
            $table->json('description')->nullable(); // translatable
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });

        // 2. Post Tags Table
        Schema::create('post_tags', function (Blueprint $table) {
            $table->id();
            $table->json('name'); // translatable
            $table->string('slug')->unique();
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();

            $table->index('usage_count');
        });

        // 3. Blog Posts Table (main content table)
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();

            // Author relationship - using restrictOnDelete to prevent accidental author deletion
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('blog_categories')->nullOnDelete();

            // Core content fields
            $table->string('slug')->unique();
            $table->json('title'); // translatable
            $table->json('excerpt')->nullable(); // translatable
            $table->json('content'); // translatable

            // Publishing & status
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->string('original_locale', 2)->default('en');
            $table->timestamp('published_at')->nullable();

            // Metrics
            $table->unsignedInteger('views_count')->default(0);
            $table->decimal('rating_average', 3, 2)->nullable();
            $table->unsignedInteger('rating_count')->default(0);
            $table->unsignedInteger('completed_orders')->default(0);
            $table->boolean('is_featured')->default(false);

            // External/RSS import tracking
            $table->boolean('is_external')->default(false);
            $table->string('external_source_name')->nullable();
            $table->string('external_source_url')->nullable();

            // AI generation tracking
            $table->boolean('generated_by_ai')->default(false);
            $table->string('ai_model_used')->nullable();
            $table->string('generation_prompt_version')->nullable();

            // Referral tracking (if used with e-commerce)
            $table->json('referral_metadata')->nullable();

            // Demo data flag
            $table->boolean('is_demo')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index('status');
            $table->index('published_at');
            $table->index('original_locale');
            $table->index('is_external');
            $table->index('generated_by_ai');
            $table->index('is_featured');
            $table->index('is_demo');
        });

        // 4. Post-Tag Pivot Table
        Schema::create('post_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_tag_id')->constrained('post_tags')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['blog_post_id', 'post_tag_id']);
        });

        // 5. Blog Comments Table
        Schema::create('blog_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('blog_comments')->cascadeOnDelete();
            $table->text('content');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });

        // 6. Blog Post Ratings Table
        Schema::create('blog_post_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('rating')->unsigned()->comment('Rating from 1 to 5');
            $table->timestamps();

            // Ensure one user can only rate a post once
            $table->unique(['user_id', 'blog_post_id']);
        });

        // 7. Blog Post Favorites Table
        Schema::create('blog_post_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Ensure one user can only favorite a post once
            $table->unique(['user_id', 'blog_post_id']);
        });

        // 8. Blog RSS Imports Table (for automation)
        Schema::create('blog_rss_imports', function (Blueprint $table) {
            $table->id();
            $table->string('feed_url');
            $table->string('item_guid')->unique();
            $table->string('item_url')->nullable();
            $table->string('title_hash');
            $table->foreignId('blog_post_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('imported_at');
            $table->timestamps();

            $table->index(['feed_url', 'imported_at']);
            $table->index('title_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_rss_imports');
        Schema::dropIfExists('blog_post_favorites');
        Schema::dropIfExists('blog_post_ratings');
        Schema::dropIfExists('blog_comments');
        Schema::dropIfExists('post_tag');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('post_tags');
        Schema::dropIfExists('blog_categories');
    }
};
