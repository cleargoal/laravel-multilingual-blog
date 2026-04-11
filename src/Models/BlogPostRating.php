<?php

declare(strict_types=1);

namespace YourVendor\Blog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPostRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'blog_post_id',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    /**
     * Get the user who gave the rating
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('blog.models.user'));
    }

    /**
     * Get the blog post that was rated
     */
    public function blogPost(): BelongsTo
    {
        return $this->belongsTo(config('blog.models.blog_post', BlogPost::class));
    }

    // Override table name (configurable)
    public function getTable()
    {
        return config('blog.tables.blog_post_ratings', parent::getTable());
    }
}
