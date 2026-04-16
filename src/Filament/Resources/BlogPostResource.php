<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Filament\Resources;

use Cleargoal\Blog\Models\BlogPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Blog';

    public static function getNavigationLabel(): string
    {
        return 'Blog Posts';
    }

    public static function getModelLabel(): string
    {
        return 'Blog Post';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Blog Posts';
    }

    public static function form(Form $form): Form
    {
        $languages = config('blog.languages', ['en']);
        $defaultLocale = config('blog.default_locale', 'en');

        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('author_id')
                            ->label('Author')
                            ->relationship('author', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('No Category'),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'archived' => 'Archived',
                            ])
                            ->default('draft')
                            ->required(),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Published At')
                            ->placeholder('Schedule publication'),

                        Forms\Components\Select::make('original_locale')
                            ->label('Original Language')
                            ->options(array_combine($languages, array_map('strtoupper', $languages)))
                            ->default($defaultLocale)
                            ->required(),
                    ]),

                Forms\Components\Section::make('Content')
                    ->schema([
                        Forms\Components\Tabs::make('translations')
                            ->tabs(
                                collect($languages)->map(function ($locale) {
                                    return Forms\Components\Tabs\Tab::make(strtoupper($locale))
                                        ->schema([
                                            Forms\Components\TextInput::make("title.{$locale}")
                                                ->label('Title')
                                                ->required(fn () => $locale === config('blog.default_locale')),

                                            Forms\Components\Textarea::make("excerpt.{$locale}")
                                                ->label('Excerpt')
                                                ->rows(3),

                                            Forms\Components\RichEditor::make("content.{$locale}")
                                                ->label('Content')
                                                ->required(fn () => $locale === config('blog.default_locale'))
                                                ->toolbarButtons([
                                                    'bold',
                                                    'italic',
                                                    'underline',
                                                    'strike',
                                                    'link',
                                                    'orderedList',
                                                    'unorderedList',
                                                    'h2',
                                                    'h3',
                                                    'blockquote',
                                                    'codeBlock',
                                                ]),
                                        ]);
                                })->toArray()
                            ),
                    ]),

                Forms\Components\Section::make('Featured Image')
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('featured_image')
                            ->collection('featured_image')
                            ->image()
                            ->imageEditor()
                            ->maxFiles(1),
                    ])
                    ->visible(fn () => config('blog.features.media', true)),

                Forms\Components\Section::make('Tags')
                    ->visible(fn () => config('blog.features.tags', true))
                    ->schema([
                        Forms\Components\Select::make('tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->unique('post_tags', 'slug'),
                            ]),
                    ]),

                Forms\Components\Section::make('Settings')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured Post')
                            ->default(false),

                        Forms\Components\Toggle::make('is_demo')
                            ->label('Demo Post')
                            ->default(false),
                    ]),

                Forms\Components\Section::make('External Source')
                    ->visible(fn () => config('blog.features.automation', false))
                    ->schema([
                        Forms\Components\Toggle::make('is_external')
                            ->label('External Content'),

                        Forms\Components\TextInput::make('external_source_name')
                            ->label('Source Name')
                            ->visible(fn (Forms\Get $get) => $get('is_external')),

                        Forms\Components\TextInput::make('external_source_url')
                            ->label('Source URL')
                            ->url()
                            ->visible(fn (Forms\Get $get) => $get('is_external')),
                    ]),

                Forms\Components\Section::make('AI Generation')
                    ->visible(fn () => config('blog.features.automation', false))
                    ->schema([
                        Forms\Components\Toggle::make('generated_by_ai')
                            ->label('AI Generated'),

                        Forms\Components\TextInput::make('ai_model_used')
                            ->label('AI Model')
                            ->visible(fn (Forms\Get $get) => $get('generated_by_ai')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('featured_image')
                    ->collection('featured_image')
                    ->square()
                    ->size(40)
                    ->visible(fn () => config('blog.features.media', true)),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('author.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->placeholder('No Category'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                        'danger' => 'archived',
                    ])
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('author_id')
                    ->label('Author')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),

                Tables\Filters\Filter::make('published')
                    ->label('Published Only')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'published')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('view')
                    ->label('View on Site')
                    ->icon('heroicon-m-eye')
                    ->url(fn (BlogPost $record): string => route('blog.show', $record->slug))
                    ->openUrlInNewTab()
                    ->visible(fn (BlogPost $record): bool => $record->status === 'published'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish')
                        ->icon('heroicon-m-check')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'published',
                                    'published_at' => $record->published_at ?? now(),
                                ]);
                            }
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('archive')
                        ->label('Archive')
                        ->icon('heroicon-m-archive-box')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['status' => 'archived']);
                            }
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Relations can be added here if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes()
            ->with(['author', 'category']);
    }
}
