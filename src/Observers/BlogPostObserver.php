<?php

namespace Cleargoal\Blog\Observers;

use Cleargoal\Blog\Events\BlogPostCreated;
use Cleargoal\Blog\Events\BlogPostDeleted;
use Cleargoal\Blog\Events\BlogPostPublished;
use Cleargoal\Blog\Events\BlogPostUpdated;
use Cleargoal\Blog\Models\BlogPost;

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
