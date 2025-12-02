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

    protected static ?string $navigationGroup = 'Nội dung';

    protected static ?string $navigationLabel = 'Bài viết';

    protected static ?string $modelLabel = 'Bài viết';

    protected static ?string $pluralModelLabel = 'Bài viết';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        if (auth()->user()?->role !== 'admin') {
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
                Forms\Components\Section::make('Thông tin chung')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Tiêu đề')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),

                        Forms\Components\TextInput::make('slug')
                            ->label('Đường dẫn')
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated()
                            ->unique(Article::class, 'slug', ignoreRecord: true),

                        Forms\Components\Select::make('category_id')
                            ->label('Danh mục')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('author_id')
                            ->label('Tác giả')
                            ->relationship('author', 'name')
                            ->required()
                            ->default(auth()->id())
                            ->searchable()
                            ->preload()
                            ->hidden(fn () => in_array(auth()->user()?->role, ['editor', 'author']))
                            ->dehydrated(),

                        Forms\Components\Select::make('tags')
                            ->label('Thẻ')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Tên')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Forms\Components\FileUpload::make('featured_image')
                            ->label('Ảnh đại diện')
                            ->image()
                            ->disk('s3')
                            ->directory('articles')
                            ->imageEditor()
                            ->formatStateUsing(function ($state) {
                                if (!$state) {
                                    return [];
                                }
                                // If it's JSON (legacy format), extract the original path
                                if (is_string($state) && str_starts_with($state, '{')) {
                                    $decoded = json_decode($state, true);
                                    if (is_array($decoded) && isset($decoded['original'])) {
                                        return [$decoded['original']];
                                    }
                                }
                                // Return as array for FileUpload
                                return is_array($state) ? $state : [$state];
                            })
                            ->saveUploadedFileUsing(function ($file) {
                                $imageService = app(ImageService::class);
                                $paths = $imageService->processUpload($file, 'articles');
                                // Return ONLY the original path (string, not JSON)
                                return $paths['original'];
                            })
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('summary')
                            ->label('Tóm tắt')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('content')
                            ->label('Nội dung')
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

                Forms\Components\Section::make('Xuất bản')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Trạng thái')
                            ->options(function () {
                                $user = auth()->user();

                                // Admin: all options
                                if ($user?->role === 'admin') {
                                    return [
                                        'draft' => 'Bản nháp',
                                        'pending' => 'Chờ duyệt',
                                        'approved' => 'Đã duyệt',
                                        'rejected' => 'Từ chối',
                                    ];
                                }

                                // Editor: draft and approved only
                                return [
                                    'draft' => 'Bản nháp',
                                    'approved' => 'Hiển thị',
                                ];
                            })
                            ->default(fn () => auth()->user()?->role === 'editor' ? 'approved' : 'draft'),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Ngày xuất bản')
                            ->default(now()),

                        Forms\Components\Toggle::make('is_published')
                            ->label('Hiển thị')
                            ->default(fn () => in_array(auth()->user()?->role, ['admin', 'editor'])),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Bài viết nổi bật')
                            ->default(false),

                        Forms\Components\TextInput::make('view_count')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false)
                            ->label('Lượt xem'),
                    ])
                    ->columns(2)
                    ->visible(fn () => in_array(auth()->user()?->role, ['admin', 'editor'])),

                Forms\Components\Section::make('Trạng thái bài viết')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Trạng thái')
                            ->options(function ($record) {
                                // If article was previously approved (has approved_at)
                                if ($record?->approved_at) {
                                    return [
                                        'draft' => 'Bản nháp',
                                        'approved' => 'Hiển thị',
                                    ];
                                }

                                // Not yet approved: draft or submit for approval
                                return [
                                    'draft' => 'Bản nháp',
                                    'pending' => 'Chờ duyệt',
                                ];
                            })
                            ->default(function ($record) {
                                // If was approved, default to approved; otherwise pending
                                return $record?->approved_at ? 'approved' : 'pending';
                            })
                            ->helperText(function ($record) {
                                if ($record?->approved_at) {
                                    return 'Bài viết đã được duyệt. Bạn có thể ẩn bằng cách chọn "Bản nháp"';
                                }
                                return 'Chọn "Chờ duyệt" để admin xem xét và phê duyệt bài viết';
                            }),

                        Forms\Components\Placeholder::make('rejection_reason_display')
                            ->label('Lý do từ chối')
                            ->content(fn ($record) => $record?->rejection_reason)
                            ->visible(fn ($record) => $record?->status === 'rejected'),
                    ])
                    ->visible(fn () => auth()->user()?->role === 'author'),

                Forms\Components\Section::make('SEO')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->maxLength(255)
                            ->label('Tiêu đề Meta'),

                        Forms\Components\Textarea::make('meta_description')
                            ->rows(3)
                            ->label('Mô tả Meta')
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
                    ->label('Ảnh')
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
                    ->label('Tiêu đề')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Danh mục')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('author.name')
                    ->label('Tác giả')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Trạng thái')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Bản nháp',
                        'pending' => 'Chờ duyệt',
                        'approved' => 'Đã duyệt',
                        'rejected' => 'Từ chối',
                        default => $state,
                    })
                    ->visible(fn () => auth()->user()?->role === 'admin'),

                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->sortable()
                    ->label('Xuất bản')
                    ->visible(fn () => in_array(auth()->user()?->role, ['admin', 'editor'])),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->sortable()
                    ->label('Nổi bật')
                    ->visible(fn () => in_array(auth()->user()?->role, ['admin', 'editor'])),

                Tables\Columns\TextColumn::make('view_count')
                    ->numeric()
                    ->sortable()
                    ->label('Lượt xem'),

                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->label('Ngày xuất bản'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Ngày cập nhật')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Trạng thái')
                    ->options([
                        'draft' => 'Bản nháp',
                        'pending' => 'Chờ duyệt',
                        'approved' => 'Đã duyệt',
                        'rejected' => 'Từ chối',
                    ])
                    ->visible(fn () => auth()->user()?->role === 'admin'),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Xuất bản')
                    ->boolean()
                    ->trueLabel('Hiển thị')
                    ->falseLabel('Đang ẩn')
                    ->native(false)
                    ->visible(fn () => auth()->user()?->role === 'admin'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Nổi bật')
                    ->boolean()
                    ->trueLabel('Nổi bật')
                    ->falseLabel('Không nổi bật')
                    ->native(false)
                    ->visible(fn () => auth()->user()?->role === 'admin'),

                Tables\Filters\SelectFilter::make('category')
                    ->label('Danh mục')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('author')
                    ->label('Tác giả')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => auth()->user()?->role === 'admin'),

                Tables\Filters\TrashedFilter::make()
                    ->label('Đã xóa')
                    ->visible(fn () => auth()->user()?->role === 'admin'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Duyệt')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Duyệt bài viết')
                    ->modalDescription('Bạn có chắc chắn muốn duyệt bài viết này? Bài viết sẽ được xuất bản ngay lập tức.')
                    ->modalSubmitActionLabel('Duyệt')
                    ->modalCancelActionLabel('Hủy')
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
                    ->visible(fn (Article $record) => $record->status === 'pending' && auth()->user()?->role === 'admin'),

                Tables\Actions\Action::make('reject')
                    ->label('Từ chối')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->modalHeading('Từ chối bài viết')
                    ->modalSubmitActionLabel('Từ chối')
                    ->modalCancelActionLabel('Hủy')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Lý do từ chối')
                            ->required()
                            ->rows(3)
                            ->placeholder('Giải thích lý do bài viết bị từ chối...'),
                    ])
                    ->action(function (Article $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'is_published' => false,
                        ]);
                    })
                    ->visible(fn (Article $record) => $record->status === 'pending' && auth()->user()?->role === 'admin'),

                Tables\Actions\ViewAction::make()
                    ->label('Xem'),
                Tables\Actions\EditAction::make()
                    ->label('Sửa'),
                Tables\Actions\DeleteAction::make()
                    ->label('Xóa'),
                Tables\Actions\ForceDeleteAction::make()
                    ->label('Xóa vĩnh viễn'),
                Tables\Actions\RestoreAction::make()
                    ->label('Khôi phục'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Xóa'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label('Xóa vĩnh viễn'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label('Khôi phục'),
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
        $user = auth()->user();

        // Admin can see trashed articles
        if ($user?->role === 'admin') {
            $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

            // Admin sees: own articles (any status) + others' non-draft articles
            $query->where(function ($q) use ($user) {
                $q->where('author_id', $user->id)
                  ->orWhere('status', '!=', 'draft');
            });
        }

        // Editors and authors can only see their own articles
        if (in_array($user?->role, ['editor', 'author'])) {
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
