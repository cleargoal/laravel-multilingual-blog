<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Events;

use Cleargoal\Blog\Models\BlogPost;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BlogPostUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public BlogPost $post
    ) {}
}
