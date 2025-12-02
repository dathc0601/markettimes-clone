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
    protected static ?string $navigationGroup = 'Management';
    protected static ?int $navigationSort = 11;
    protected static ?string $navigationLabel = 'Navigation';
    protected static ?string $modelLabel = 'Navigation Item';
    protected static ?string $pluralModelLabel = 'Navigation Items';

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
                    ->label('Label')
                    ->searchable()
                    ->sortable()
                    ->description(fn (NavigationItem $record): ?string =>
                        $record->parent ? "↳ Child of: {$record->parent->label}" : null
                    ),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
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
                        'category' => 'Category',
                        'page' => 'Page',
                        'custom' => 'Custom',
                        'divider' => 'Divider',
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
                    ->label('Parent')
                    ->placeholder('Top Level')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order')
                    ->label('Order')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order') // Enable drag-drop reordering
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'category' => 'Category',
                        'page' => 'Page',
                        'custom' => 'Custom',
                        'divider' => 'Divider',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All items')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Level')
                    ->options([
                        'null' => 'Top Level',
                        'not_null' => 'Child Items',
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ReplicateAction::make()
                    ->excludeAttributes(['order'])
                    ->beforeReplicaSaved(function (NavigationItem $replica): void {
                        $replica->label = $replica->label . ' (Copy)';
                        $replica->order = NavigationItem::max('order') + 1;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
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
