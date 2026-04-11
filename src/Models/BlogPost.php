<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Models;

use Cleargoal\Blog\Contracts\BlogAuthor;
use Cleargoal\Blog\Contracts\ContentSanitizer;
use Cleargoal\Blog\Traits\HasTranslationFallback;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

/**
 * @property Carbon|null $published_at
 */
class BlogPost extends Model implements HasMedia
{
    use HasFactory, HasSlug, HasTranslationFallback, HasTranslations, InteractsWithMedia, SoftDeletes;

    public array $translatable = ['title', 'content', 'excerpt'];

    /**
     * Boot model and register event listeners for HTML sanitization.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Sanitize HTML fields before saving
        static::saving(function (BlogPost $post) {
            $sanitizer = app(ContentSanitizer::class);

            // Sanitize 'content' field (rich HTML)
            if ($post->isDirty('content')) {
                $value = $post->getAttributes()['content'] ?? null;

                if ($value && is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (is_array($decoded)) {
                        $sanitized = [];
                        foreach ($decoded as $locale => $content) {
                            $sanitized[$locale] = is_string($content) ? $sanitizer->sanitizeHtml($content) : $content;
                        }
                        $post->attributes['content'] = json_encode($sanitized);
                    }
                }
            }

            // Strip all HTML tags from 'excerpt' (plain text only)
            if ($post->isDirty('excerpt')) {
                $value = $post->getAttributes()['excerpt'] ?? null;

                if ($value && is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (is_array($decoded)) {
                        $sanitized = [];
                        foreach ($decoded as $locale => $excerpt) {
                            $sanitized[$locale] = is_string($excerpt) ? $sanitizer->stripAllTags($excerpt) : $excerpt;
                        }
                        $post->attributes['excerpt'] = json_encode($sanitized);
                    }
                }
            }

            // Auto-set published_at when status changes to 'published'
            if ($post->isDirty('status') && $post->status === 'published' && ! $post->published_at) {
                $post->published_at = now();
            }
        });
    }

    protected $fillable = [
        'author_id',
        'category_id',
        'slug',
        'title',
        'content',
        'excerpt',
        'status',
        'original_locale',
        'views_count',
        'rating_average',
        'rating_count',
        'completed_orders',
        'is_featured',
        'published_at',
        'is_external',
        'external_source_name',
        'external_source_url',
        'generated_by_ai',
        'ai_model_used',
        'generation_prompt_version',
        'referral_metadata',
        'is_demo',
    ];

    protected function casts(): array
    {
        return [
            // NOTE: Don't cast translatable fields - Spatie's HasTranslations trait handles this
            'views_count' => 'integer',
            'rating_average' => 'float',
            'rating_count' => 'integer',
            'completed_orders' => 'integer',
            'is_featured' => 'boolean',
            'is_external' => 'boolean',
            'generated_by_ai' => 'boolean',
            'published_at' => 'datetime',
            'referral_metadata' => 'array',
            'is_demo' => 'boolean',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function ($model) {
                return $model->getTranslation('title', config('blog.default_locale', 'en'));
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    // Relationships

    /**
     * Get the author of this blog post.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(config('blog.models.user'), 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(config('blog.models.blog_category', BlogCategory::class), 'category_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(config('blog.models.blog_comment', BlogComment::class), 'blog_post_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            config('blog.models.post_tag', PostTag::class),
            config('blog.tables.post_tag_pivot', 'post_tag'),
            'blog_post_id',
            'post_tag_id'
        )->withTimestamps();
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(config('blog.models.blog_post_rating', BlogPostRating::class));
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(
            config('blog.models.user'),
            config('blog.tables.blog_post_favorites', 'blog_post_favorites'),
            'blog_post_id',
            'user_id'
        )->withTimestamps();
    }

    public function rssImport(): HasOne
    {
        return $this->hasOne(BlogRssImport::class);
    }

    // Media collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')
            ->singleFile()
            ->registerMediaConversions(function (): void {
                $this->addMediaConversion('thumb')
                    ->width(400)
                    ->height(300);

                $this->addMediaConversion('large')
                    ->width(1200)
                    ->height(630);
            });

        $this->addMediaCollection('content_images');
    }

    // Helpers
    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at?->isPast();
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if the post has been translated to other languages
     */
    public function isTranslated(): bool
    {
        $titleTranslations = $this->getTranslations('title');

        // Check if there are translations beyond the original locale
        return count($titleTranslations) > 1;
    }

    /**
     * Get the average rating for this post
     */
    public function averageRating(): float
    {
        return round((float) ($this->ratings()->avg('rating') ?? 0), 1);
    }

    /**
     * Get the total number of ratings
     */
    public function ratingsCount(): int
    {
        return $this->ratings()->count();
    }

    /**
     * Check if the given user has rated this post
     */
    public function isRatedByUser(?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        return $this->ratings()->where('user_id', $userId)->exists();
    }

    /**
     * Get the rating given by a specific user
     */
    public function userRating(?int $userId): ?int
    {
        if (! $userId) {
            return null;
        }

        return $this->ratings()->where('user_id', $userId)->value('rating');
    }

    /**
     * Get the total number of favorites
     */
    public function favoritesCount(): int
    {
        return $this->favoritedBy()->count();
    }

    /**
     * Check if the given user has favorited this post
     */
    public function isFavoritedByUser(?int $userId): bool
    {
        if (! $userId) {
            return false;
        }

        return $this->favoritedBy()->where('user_id', $userId)->exists();
    }

    /**
     * Check if post was generated by AI
     */
    public function isAIGenerated(): bool
    {
        return $this->generated_by_ai;
    }

    /**
     * Check if post was imported from external RSS feed
     */
    public function isExternal(): bool
    {
        return $this->is_external;
    }

    /**
     * Get the external source attribution text
     */
    public function getExternalSourceAttribution(): ?string
    {
        if (! $this->is_external || ! $this->external_source_name) {
            return null;
        }

        $attribution = __('Originally published at :source', [
            'source' => $this->external_source_name,
        ]);

        if ($this->external_source_url) {
            return '<a href="'.$this->external_source_url.'" target="_blank" rel="nofollow noopener">'
                .$attribution.'</a>';
        }

        return $attribution;
    }

    // RSS Feed Methods
    /**
     * Get feed items for specified locale
     */
    public static function getFeedItemsForLocale(string $locale = 'en')
    {
        return self::where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('is_demo', false)
            ->whereRaw("title->>'{$locale}' IS NOT NULL")
            ->with(['author', 'category'])
            ->latest('published_at')
            ->limit(config('blog.rss.items_limit', 50))
            ->get()
            ->map(static function ($post) use ($locale): array {
                /** @var BlogPost $post */
                /** @var BlogAuthor|null $author */
                $author = $post->author;

                return [
                    'id' => route(config('blog.routes.name_prefix').'show', $post->slug),
                    'title' => $post->getTranslation('title', $locale),
                    'summary' => $post->getTranslation('excerpt', $locale),
                    'updated' => $post->updated_at,
                    'link' => route(config('blog.routes.name_prefix').'show', $post->slug),
                    'authorName' => $author?->getName() ?? '',
                    'category' => $post->category?->getTranslation('name', $locale),
                ];
            });
    }

    /**
     * Get feed items for English feed (excerpt only)
     */
    public static function getFeedItemsEn()
    {
        return self::getFeedItemsForLocale('en');
    }

    /**
     * Get feed items for Ukrainian feed (excerpt only)
     */
    public static function getFeedItemsUk()
    {
        return self::getFeedItemsForLocale('uk');
    }

    /**
     * Get feed items for German feed (excerpt only)
     */
    public static function getFeedItemsDe()
    {
        return self::getFeedItemsForLocale('de');
    }

    /**
     * Get feed items for French feed (excerpt only)
     */
    public static function getFeedItemsFr()
    {
        return self::getFeedItemsForLocale('fr');
    }

    /**
     * Get feed items for Spanish feed (excerpt only)
     */
    public static function getFeedItemsEs()
    {
        return self::getFeedItemsForLocale('es');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByTag($query, $tagId)
    {
        return $query->whereHas('tags', function ($q) use ($tagId) {
            $q->where('post_tags.id', $tagId);
        });
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
                ->orWhere('content', 'like', "%{$keyword}%");
        });
    }

    public function scopeByAuthor($query, $authorId)
    {
        return $query->where('author_id', $authorId);
    }

    public function scopeExcludeDemo($query)
    {
        return $query->where('is_demo', false);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeNotDemo($query)
    {
        return $query->where('is_demo', false);
    }

    // Override table name (configurable)
    public function getTable()
    {
        return config('blog.tables.blog_posts', parent::getTable());
    }
}
