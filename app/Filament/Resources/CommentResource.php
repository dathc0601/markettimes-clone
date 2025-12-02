<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentResource\Pages;
use App\Filament\Resources\CommentResource\RelationManagers;
use App\Models\Comment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Nội dung';

    protected static ?string $navigationLabel = 'Bình luận';

    protected static ?string $modelLabel = 'Bình luận';

    protected static ?string $pluralModelLabel = 'Bình luận';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationBadgeTooltip = 'Bình luận chờ duyệt';

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'editor']);
    }

    public static function getNavigationBadge(): ?string
    {
        $query = static::getModel()::where('is_approved', false);

        // Editor can only see comments on their own articles
        if (auth()->user()?->role === 'editor') {
            $query->whereHas('article', function ($q) {
                $q->where('author_id', auth()->id());
            });
        }

        return $query->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $query = static::getModel()::where('is_approved', false);

        // Editor can only see comments on their own articles
        if (auth()->user()?->role === 'editor') {
            $query->whereHas('article', function ($q) {
                $q->where('author_id', auth()->id());
            });
        }

        $count = $query->count();
        return $count > 0 ? 'warning' : null;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin bình luận')
                    ->schema([
                        Forms\Components\Select::make('article_id')
                            ->label('Bài viết')
                            ->relationship('article', 'title')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('user_id')
                            ->label('Người dùng')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Textarea::make('content')
                            ->label('Nội dung')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_approved')
                            ->label('Đã duyệt')
                            ->default(false)
                            ->required(),

                        Forms\Components\Select::make('parent_id')
                            ->label('Bình luận cha (Trả lời)')
                            ->relationship('parent', 'id')
                            ->searchable()
                            ->nullable()
                            ->helperText('Để trống nếu đây không phải là trả lời'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('article.title')
                    ->label('Bài viết')
                    ->sortable()
                    ->searchable()
                    ->limit(30)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Người dùng')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('content')
                    ->label('Nội dung')
                    ->limit(50)
                    ->wrap()
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_approved')
                    ->boolean()
                    ->sortable()
                    ->label('Trạng thái')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('parent_id')
                    ->label('Trả lời')
                    ->formatStateUsing(fn ($state) => $state ? 'Có' : 'Không')
                    ->badge()
                    ->color(fn ($state) => $state ? 'primary' : 'gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->label('Đăng lúc'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Ngày cập nhật')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label('Trạng thái duyệt')
                    ->placeholder('Tất cả bình luận')
                    ->trueLabel('Đã duyệt')
                    ->falseLabel('Chờ duyệt')
                    ->native(false),

                Tables\Filters\SelectFilter::make('article')
                    ->label('Bài viết')
                    ->relationship('article', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('user')
                    ->label('Người dùng')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TrashedFilter::make()
                    ->label('Đã xóa'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Duyệt')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Duyệt bình luận')
                    ->modalDescription('Bạn có chắc chắn muốn duyệt bình luận này?')
                    ->modalSubmitActionLabel('Duyệt')
                    ->modalCancelActionLabel('Hủy')
                    ->action(fn (Comment $record) => $record->update(['is_approved' => true]))
                    ->visible(fn (Comment $record) => !$record->is_approved),

                Tables\Actions\Action::make('reject')
                    ->label('Từ chối')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Từ chối bình luận')
                    ->modalDescription('Bạn có chắc chắn muốn từ chối bình luận này?')
                    ->modalSubmitActionLabel('Từ chối')
                    ->modalCancelActionLabel('Hủy')
                    ->action(fn (Comment $record) => $record->update(['is_approved' => false]))
                    ->visible(fn (Comment $record) => $record->is_approved),

                Tables\Actions\ViewAction::make()
                    ->label('Xem'),
                Tables\Actions\EditAction::make()
                    ->label('Sửa'),
                Tables\Actions\DeleteAction::make()
                    ->label('Xóa'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Duyệt')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Duyệt bình luận')
                        ->modalDescription('Bạn có chắc chắn muốn duyệt các bình luận đã chọn?')
                        ->modalSubmitActionLabel('Duyệt')
                        ->modalCancelActionLabel('Hủy')
                        ->action(fn ($records) => $records->each->update(['is_approved' => true]))
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('reject')
                        ->label('Từ chối')
                        ->icon('heroicon-o-x-mark')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Từ chối bình luận')
                        ->modalDescription('Bạn có chắc chắn muốn từ chối các bình luận đã chọn?')
                        ->modalSubmitActionLabel('Từ chối')
                        ->modalCancelActionLabel('Hủy')
                        ->action(fn ($records) => $records->each->update(['is_approved' => false]))
                        ->deselectRecordsAfterCompletion(),

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
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        // Editor can only see comments on their own articles
        if (auth()->user()?->role === 'editor') {
            $query->whereHas('article', function ($q) {
                $q->where('author_id', auth()->id());
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComments::route('/'),
            'create' => Pages\CreateComment::route('/create'),
            'edit' => Pages\EditComment::route('/{record}/edit'),
        ];
    }
}
