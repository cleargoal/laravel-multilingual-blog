<?php

namespace Cleargoal\Blog\Observers;

use Cleargoal\Blog\Events\BlogCommentApproved;
use Cleargoal\Blog\Events\BlogCommentPosted;
use Cleargoal\Blog\Models\BlogComment;

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
