<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Filament\Resources\ArticleResource\RelationManagers;
use App\Models\Article;
use App\Services\ImageService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        if (!in_array(auth()->user()?->role, ['admin', 'editor'])) {
            return null;
        }
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? 'warning' : null;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated()
                            ->unique(Article::class, 'slug', ignoreRecord: true),

                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('author_id')
                            ->relationship('author', 'name')
                            ->required()
                            ->default(auth()->id())
                            ->searchable()
                            ->preload()
                            ->hidden(fn () => auth()->user()?->role === 'author')
                            ->dehydrated(),

                        Forms\Components\Select::make('tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Forms\Components\FileUpload::make('featured_image')
                            ->image()
                            ->disk('s3')
                            ->directory('articles')
                            ->imageEditor()
                            ->afterStateHydrated(function (Forms\Components\FileUpload $component, $state) {
                                if (!$state) {
                                    return;
                                }
                                // If state is JSON, extract the original path
                                $paths = is_string($state) ? json_decode($state, true) : $state;
                                if (is_array($paths) && isset($paths['original'])) {
                                    $component->state([$paths['original']]);
                                }
                            })
                            ->saveUploadedFileUsing(function ($file) {
                                $imageService = app(ImageService::class);
                                $paths = $imageService->processUpload($file, 'articles');
                                return json_encode($paths);
                            })
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('summary')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'link',
                                'h1',
                                'h2',
                                'h3',
                                'bulletList',
                                'orderedList',
                                'blockquote',
                                'codeBlock',
                                'attachFiles',
                                'undo',
                                'redo',
                            ])
                            ->fileAttachmentsDirectory('articles/content')
                            ->fileAttachmentsDisk('public')
                            ->saveUploadedFileAttachmentsUsing(function ($file) {
                                // Use ImageService to optimize and store
                                $imageService = app(ImageService::class);
                                $paths = $imageService->processUpload($file, 'articles/content');
                                return json_encode($paths);
                            })
                            ->getUploadedAttachmentUrlUsing(function ($file) {
                                // Return the original WebP image URL (full quality, smaller file size)
                                $pathData = json_decode($file, true);
                                if (is_array($pathData) && isset($pathData['original_webp'])) {
                                    return Storage::disk('s3')->url($pathData['original_webp']);
                                }
                                // Fallback to original if WebP not available
                                if (is_array($pathData) && isset($pathData['original'])) {
                                    return Storage::disk('s3')->url($pathData['original']);
                                }
                                return Storage::disk('s3')->url($file);
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Publishing')
                    ->schema([
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Publish Date')
                            ->default(now()),

                        Forms\Components\Toggle::make('is_published')
                            ->label('Published')
                            ->default(false),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured Article')
                            ->default(false),

                        Forms\Components\TextInput::make('view_count')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false)
                            ->label('View Count'),
                    ])
                    ->columns(2)
                    ->visible(fn () => in_array(auth()->user()?->role, ['admin', 'editor'])),

                Forms\Components\Section::make('Article Status')
                    ->schema([
                        Forms\Components\Placeholder::make('status_display')
                            ->label('Status')
                            ->content(fn ($record) => match($record?->status) {
                                'pending' => 'Pending Approval',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                default => 'Draft',
                            }),
                        Forms\Components\Placeholder::make('rejection_reason_display')
                            ->label('Rejection Reason')
                            ->content(fn ($record) => $record?->rejection_reason)
                            ->visible(fn ($record) => $record?->status === 'rejected'),
                    ])
                    ->visible(fn () => auth()->user()?->role === 'author'),

                Forms\Components\Section::make('SEO')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->maxLength(255)
                            ->label('Meta Title'),

                        Forms\Components\Textarea::make('meta_description')
                            ->rows(3)
                            ->label('Meta Description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->square()
                    ->label('Image')
                    ->disk('s3')
                    ->getStateUsing(function ($record) {
                        if (!$record->featured_image) {
                            return $record->getFirstImageFromContent('thumbnail');
                        }
                        // Handle both old (string) and new (JSON) formats
                        $paths = is_string($record->featured_image) ? json_decode($record->featured_image, true) : $record->featured_image;
                        if (is_array($paths)) {
                            return $paths['thumbnail'] ?? $paths['original'] ?? null;
                        }
                        return $record->featured_image;
                    }),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('author.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->sortable()
                    ->label('Published')
                    ->visible(fn () => in_array(auth()->user()?->role, ['admin', 'editor'])),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->sortable()
                    ->label('Featured')
                    ->visible(fn () => in_array(auth()->user()?->role, ['admin', 'editor'])),

                Tables\Columns\TextColumn::make('view_count')
                    ->numeric()
                    ->sortable()
                    ->label('Views'),

                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->label('Published'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending Approval',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->visible(fn () => in_array(auth()->user()?->role, ['admin', 'editor'])),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published')
                    ->boolean()
                    ->trueLabel('Published only')
                    ->falseLabel('Drafts only')
                    ->native(false)
                    ->visible(fn () => in_array(auth()->user()?->role, ['admin', 'editor'])),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueLabel('Featured only')
                    ->falseLabel('Not featured')
                    ->native(false)
                    ->visible(fn () => in_array(auth()->user()?->role, ['admin', 'editor'])),

                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('author')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => in_array(auth()->user()?->role, ['admin', 'editor'])),

                Tables\Filters\TrashedFilter::make()
                    ->visible(fn () => in_array(auth()->user()?->role, ['admin', 'editor'])),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Article')
                    ->modalDescription('Are you sure you want to approve this article? It will be published immediately.')
                    ->action(function (Article $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                            'is_published' => true,
                            'published_at' => $record->published_at ?? now(),
                            'rejection_reason' => null,
                        ]);
                    })
                    ->visible(fn (Article $record) => $record->status === 'pending' && in_array(auth()->user()?->role, ['admin', 'editor'])),

                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3)
                            ->placeholder('Explain why this article is being rejected...'),
                    ])
                    ->action(function (Article $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'is_published' => false,
                        ]);
                    })
                    ->visible(fn (Article $record) => $record->status === 'pending' && in_array(auth()->user()?->role, ['admin', 'editor'])),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Only admins/editors can see trashed articles (they have TrashedFilter)
        if (in_array(auth()->user()?->role, ['admin', 'editor'])) {
            $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
        }

        // Authors can only see their own articles
        if (auth()->user()?->role === 'author') {
            $query->where('author_id', auth()->id());
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
