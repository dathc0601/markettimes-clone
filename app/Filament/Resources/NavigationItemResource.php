<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NavigationItemResource\Pages;
use App\Models\Category;
use App\Models\NavigationItem;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NavigationItemResource extends Resource
{
    protected static ?string $model = NavigationItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-3';
    protected static ?string $navigationGroup = 'Quản lý';
    protected static ?int $navigationSort = 11;
    protected static ?string $navigationLabel = 'Menu điều hướng';
    protected static ?string $modelLabel = 'Menu điều hướng';
    protected static ?string $pluralModelLabel = 'Menu điều hướng';

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin Navigation Item')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('label')
                                    ->label('Label')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Tên hiển thị trong menu')
                                    ->helperText('Văn bản hiển thị trong menu'),

                                Forms\Components\Select::make('type')
                                    ->label('Loại liên kết')
                                    ->options([
                                        'category' => 'Danh mục (Category)',
                                        'page' => 'Trang tĩnh (Page)',
                                        'custom' => 'Liên kết tùy chỉnh',
                                        'divider' => 'Dấu phân cách',
                                    ])
                                    ->required()
                                    ->reactive()
                                    ->default('category')
                                    ->helperText('Chọn loại liên kết muốn tạo'),
                            ]),

                        // Conditional fields based on type
                        Forms\Components\Select::make('category_id')
                            ->label('Chọn danh mục')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Forms\Get $get) => $get('type') === 'category')
                            ->required(fn (Forms\Get $get) => $get('type') === 'category')
                            ->helperText('Liên kết đến danh mục bài viết'),

                        Forms\Components\Select::make('page_id')
                            ->label('Chọn trang')
                            ->relationship('page', 'title')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Forms\Get $get) => $get('type') === 'page')
                            ->required(fn (Forms\Get $get) => $get('type') === 'page')
                            ->helperText('Liên kết đến trang tĩnh'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('custom_url')
                                    ->label('URL tùy chỉnh')
                                    ->url()
                                    ->placeholder('https://example.com hoặc /custom-page')
                                    ->visible(fn (Forms\Get $get) => $get('type') === 'custom')
                                    ->required(fn (Forms\Get $get) => $get('type') === 'custom')
                                    ->helperText('Nhập URL đầy đủ hoặc đường dẫn tương đối'),

                                Forms\Components\Toggle::make('open_in_new_tab')
                                    ->label('Mở trong tab mới')
                                    ->default(false)
                                    ->visible(fn (Forms\Get $get) => $get('type') === 'custom')
                                    ->helperText('Thêm target="_blank" cho liên kết'),
                            ]),

                        Forms\Components\TextInput::make('css_classes')
                            ->label('CSS Classes')
                            ->placeholder('custom-class another-class')
                            ->visible(fn (Forms\Get $get) => $get('type') === 'divider')
                            ->helperText('Thêm class CSS tùy chỉnh cho divider'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Cấu trúc & Hiển thị')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('parent_id')
                                    ->label('Menu cha (Parent)')
                                    ->relationship(
                                        'parent',
                                        'label',
                                        fn (Builder $query, ?NavigationItem $record) =>
                                            $query->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Không có (Top level)')
                                    ->helperText('Để trống nếu đây là menu cấp cao nhất'),

                                Forms\Components\TextInput::make('order')
                                    ->label('Thứ tự')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Số thứ tự sắp xếp (càng nhỏ càng ưu tiên)'),
                            ]),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Kích hoạt')
                            ->default(true)
                            ->helperText('Hiển thị menu item này trên website'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Nhãn')
                    ->searchable()
                    ->sortable()
                    ->description(fn (NavigationItem $record): ?string =>
                        $record->parent ? "↳ Con của: {$record->parent->label}" : null
                    ),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Loại')
                    ->colors([
                        'primary' => 'category',
                        'success' => 'page',
                        'warning' => 'custom',
                        'secondary' => 'divider',
                    ])
                    ->icons([
                        'heroicon-o-folder' => 'category',
                        'heroicon-o-document-text' => 'page',
                        'heroicon-o-link' => 'custom',
                        'heroicon-o-minus' => 'divider',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'category' => 'Danh mục',
                        'page' => 'Trang',
                        'custom' => 'Tùy chỉnh',
                        'divider' => 'Phân cách',
                    }),

                Tables\Columns\TextColumn::make('url_preview')
                    ->label('URL')
                    ->getStateUsing(function (NavigationItem $record): ?string {
                        if ($record->type === 'category' && $record->category) {
                            return "/category/{$record->category->slug}";
                        }
                        if ($record->type === 'page' && $record->page) {
                            return "/page/{$record->page->slug}";
                        }
                        if ($record->type === 'custom') {
                            return $record->custom_url;
                        }
                        if ($record->type === 'divider') {
                            return '---';
                        }
                        return null;
                    })
                    ->limit(50)
                    ->tooltip(function (NavigationItem $record): ?string {
                        return $record->url;
                    }),

                Tables\Columns\TextColumn::make('parent.label')
                    ->label('Menu cha')
                    ->placeholder('Cấp cao nhất')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order')
                    ->label('Thứ tự')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Kích hoạt')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order') // Enable drag-drop reordering
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Loại')
                    ->options([
                        'category' => 'Danh mục',
                        'page' => 'Trang',
                        'custom' => 'Tùy chỉnh',
                        'divider' => 'Phân cách',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Trạng thái')
                    ->placeholder('Tất cả')
                    ->trueLabel('Đang kích hoạt')
                    ->falseLabel('Chưa kích hoạt'),

                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Cấp độ')
                    ->options([
                        'null' => 'Cấp cao nhất',
                        'not_null' => 'Menu con',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value']) {
                            'null' => $query->whereNull('parent_id'),
                            'not_null' => $query->whereNotNull('parent_id'),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Sửa'),
                Tables\Actions\DeleteAction::make()
                    ->label('Xóa'),
                Tables\Actions\ReplicateAction::make()
                    ->label('Nhân bản')
                    ->excludeAttributes(['order'])
                    ->beforeReplicaSaved(function (NavigationItem $replica): void {
                        $replica->label = $replica->label . ' (Bản sao)';
                        $replica->order = NavigationItem::max('order') + 1;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Xóa'),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Kích hoạt')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Vô hiệu hóa')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNavigationItems::route('/'),
            'create' => Pages\CreateNavigationItem::route('/create'),
            'edit' => Pages\EditNavigationItem::route('/{record}/edit'),
        ];
    }
}
