<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Models;

use Cleargoal\Blog\Traits\HasTranslationFallback;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

class BlogCategory extends Model
{
    use HasFactory, HasSlug, HasTranslationFallback, HasTranslations;

    public array $translatable = ['name', 'description'];

    protected $fillable = [
        'parent_id',
        'slug',
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(BlogCategory::class, 'parent_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(config('blog.models.blog_post', BlogPost::class), 'category_id');
    }

    // Override table name (configurable)
    public function getTable()
    {
        return config('blog.tables.blog_categories', parent::getTable());
    }
}
