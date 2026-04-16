<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Filament\Resources\BlogPostResource\Pages;

use Cleargoal\Blog\Filament\Resources\BlogPostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBlogPosts extends ListRecords
{
    protected static string $resource = BlogPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
