<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Models;

use Cleargoal\Blog\Contracts\ContentSanitizer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $blog_post_id
 * @property int $author_id
 * @property int|null $parent_id
 * @property string $content
 * @property string $status
 * @property Carbon|null $approved_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class BlogComment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Boot model and register event listeners for HTML sanitization.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Sanitize HTML content before saving
        static::saving(function (BlogComment $comment) {
            $sanitizer = app(ContentSanitizer::class);

            if ($comment->isDirty('content')) {
                $comment->content = $sanitizer->sanitizeHtml($comment->content);
            }
        });

        // Cascade soft deletes to child comments
        static::deleting(function (BlogComment $comment) {
            // Delete all child comments (replies)
            $comment->replies()->each(function ($reply) {
                $reply->delete();
            });
        });
    }

    protected $fillable = [
        'blog_post_id',
        'author_id',
        'parent_id',
        'content',
        'status',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    // Relationships
    public function post(): BelongsTo
    {
        return $this->belongsTo(config('blog.models.blog_post', BlogPost::class), 'blog_post_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(config('blog.models.user'), 'author_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(BlogComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(BlogComment::class, 'parent_id');
    }

    // Helpers
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    // Override table name (configurable)
    public function getTable()
    {
        return config('blog.tables.blog_comments', parent::getTable());
    }
}
