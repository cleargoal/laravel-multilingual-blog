<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Filament\Resources\BlogPostResource\Pages;

use Cleargoal\Blog\Filament\Resources\BlogPostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBlogPost extends EditRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('view')
                ->label('View on Site')
                ->icon('heroicon-m-eye')
                ->url(fn (): string => route('blog.show', $this->record->slug))
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->record->status === 'published'),
        ];
    }
}
