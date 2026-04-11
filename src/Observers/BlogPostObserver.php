<?php

namespace YourVendor\Blog\Observers;

use YourVendor\Blog\Events\BlogPostCreated;
use YourVendor\Blog\Events\BlogPostDeleted;
use YourVendor\Blog\Events\BlogPostPublished;
use YourVendor\Blog\Events\BlogPostUpdated;
use YourVendor\Blog\Models\BlogPost;

class BlogPostObserver
{
    public function created(BlogPost $post): void
    {
        event(new BlogPostCreated($post));
    }

    public function updated(BlogPost $post): void
    {
        event(new BlogPostUpdated($post));

        // Check if status changed to published
        if ($post->isDirty('status') && $post->status === 'published') {
            event(new BlogPostPublished($post));
        }
    }

    public function deleted(BlogPost $post): void
    {
        event(new BlogPostDeleted($post));
    }
}
