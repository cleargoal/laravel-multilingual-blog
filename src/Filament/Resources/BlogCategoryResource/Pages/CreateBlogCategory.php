<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Filament\Resources\BlogCategoryResource\Pages;

use Cleargoal\Blog\Filament\Resources\BlogCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlogCategory extends CreateRecord
{
    protected static string $resource = BlogCategoryResource::class;
}
