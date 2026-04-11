<?php

declare(strict_types=1);

namespace YourVendor\Blog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogRssImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'feed_url',
        'item_guid',
        'item_url',
        'title_hash',
        'blog_post_id',
        'imported_at',
    ];

    protected function casts(): array
    {
        return [
            'imported_at' => 'datetime',
        ];
    }

    public function blogPost(): BelongsTo
    {
        return $this->belongsTo(config('blog.models.blog_post', BlogPost::class));
    }

    /**
     * Check if an RSS item has already been imported
     */
    public static function isImported(string $guid, string $url): bool
    {
        return static::where('item_guid', $guid)
            ->orWhere('item_url', $url)
            ->exists();
    }

    // Override table name (configurable)
    public function getTable()
    {
        return config('blog.tables.blog_rss_imports', parent::getTable());
    }
}
