<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Filament\Resources\BlogPostResource\Pages;

use Cleargoal\Blog\Filament\Resources\BlogPostResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogPost extends CreateRecord
{
    protected static string $resource = BlogPostResource::class;
}
