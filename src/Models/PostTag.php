<?php

declare(strict_types=1);

namespace YourVendor\Blog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;
use YourVendor\Blog\Traits\HasTranslationFallback;

class PostTag extends Model
{
    use HasFactory, HasSlug, HasTranslationFallback, HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = [
        'slug',
        'name',
        'usage_count',
    ];

    protected function casts(): array
    {
        return [
            'usage_count' => 'integer',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    // Relationships
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(
            config('blog.models.blog_post', BlogPost::class),
            config('blog.tables.post_tag_pivot', 'post_tag'),
            'post_tag_id',
            'blog_post_id'
        )->withTimestamps();
    }

    // Helper Methods

    /**
     * Increment the usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Decrement the usage count
     */
    public function decrementUsage(): void
    {
        if ($this->usage_count > 0) {
            $this->decrement('usage_count');
        }
    }

    /**
     * Get popular tags (by usage count)
     */
    public static function popular(int $limit = 20)
    {
        return static::where('usage_count', '>', 0)
            ->orderBy('usage_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get trending tags (most used in last 30 days)
     * For Phase 1, uses popular() - can be enhanced later with timestamp tracking
     */
    public static function trending(int $limit = 10)
    {
        return static::popular($limit);
    }

    /**
     * Find or create tag by name (supports multilingual)
     */
    public static function findOrCreateByName(string $name, string $locale = 'en'): self
    {
        // Create slug from name
        $slug = \Illuminate\Support\Str::slug($name);

        // Try to find by slug first (most efficient)
        $tag = static::where('slug', $slug)->first();

        if ($tag) {
            return $tag;
        }

        // Create new tag with initial translation
        $tagData = [
            'name' => [$locale => $name],
            'slug' => $slug,
            'usage_count' => 0,
        ];

        return static::create($tagData);
    }

    // Override table name (configurable)
    public function getTable()
    {
        return config('blog.tables.post_tags', parent::getTable());
    }
}
