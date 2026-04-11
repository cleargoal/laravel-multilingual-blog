<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Cleargoal\Blog\Models\BlogComment;

class BlogCommentPosted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public BlogComment $comment
    ) {}
}
