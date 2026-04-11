<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Cleargoal\Blog\Models\BlogPost;

class BlogPostPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public BlogPost $post
    ) {}
}
