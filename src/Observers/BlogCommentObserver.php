<?php

namespace YourVendor\Blog\Observers;

use YourVendor\Blog\Events\BlogCommentApproved;
use YourVendor\Blog\Events\BlogCommentPosted;
use YourVendor\Blog\Models\BlogComment;

class BlogCommentObserver
{
    public function created(BlogComment $comment): void
    {
        event(new BlogCommentPosted($comment));
    }

    public function updated(BlogComment $comment): void
    {
        // Check if status changed to approved
        if ($comment->isDirty('status') && $comment->status === 'approved') {
            event(new BlogCommentApproved($comment));
        }
    }
}
