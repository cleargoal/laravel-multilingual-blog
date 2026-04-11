<?php

declare(strict_types=1);

namespace YourVendor\Blog\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use YourVendor\Blog\Models\BlogPost;

class BlogPostUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public BlogPost $post
    ) {}
}
