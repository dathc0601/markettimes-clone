<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdResource\Pages;
use App\Models\Ad;
use App\Services\AdService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AdResource extends Resource
{
    protected static ?string $model = Ad::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Quảng cáo';

    protected static ?string $navigationLabel = 'Quản lý quảng cáo';

    protected static ?string $modelLabel = 'Quảng cáo';

    protected static ?string $pluralModelLabel = 'Quảng cáo';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Thông tin cơ bản')
                    ->description('Thông tin chính của quảng cáo')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Tên quảng cáo')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('VD: Banner header trang chủ - Tháng 12')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('type')
                            ->label('Loại quảng cáo')
                            ->options([
                                'image' => 'Hình ảnh (Banner với link)',
                                'html' => 'HTML/JavaScript (AdSense, Affiliate)',
                            ])
                            ->required()
                            ->live()
                            ->default('image')
                            ->helperText('Chọn loại nội dung quảng cáo'),

                        Forms\Components\Select::make('page')
                            ->label('Trang hiển thị')
                            ->options([
                                'all' => 'Tất cả các trang',
                                'homepage' => 'Chỉ trang chủ',
                                'article' => 'Chỉ trang bài viết',
                                'category' => 'Chỉ trang danh mục',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('position', null))
                            ->default('all')
                            ->helperText('Quảng cáo sẽ hiển thị ở trang nào'),

                        Forms\Components\Select::make('position')
                            ->label('Vị trí')
                            ->options(function (Forms\Get $get) {
                                $page = $get('page');
                                return Ad::getPositionOptions($page);
                            })
                            ->required()
                            ->searchable()
                            ->helperText('Vị trí cụ thể trên trang'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Nội dung quảng cáo hình ảnh')
                    ->description('Upload hình ảnh và thiết lập link')
                    ->schema([
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Hình ảnh quảng cáo')
                            ->image()
                            ->disk('s3')
                            ->directory('ads')
                            ->required()
                            ->imageEditor()
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                            ->helperText('Kích thước tối đa 2MB. Hỗ trợ: JPG, PNG, GIF, WebP')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('click_url')
                            ->label('URL khi click')
                            ->url()
                            ->required()
                            ->placeholder('https://example.com/landing-page')
                            ->helperText('Địa chỉ trang web khi người dùng click vào quảng cáo'),

                        Forms\Components\TextInput::make('alt_text')
                            ->label('Mô tả hình ảnh (Alt text)')
                            ->maxLength(255)
                            ->placeholder('Mô tả ngắn gọn nội dung quảng cáo')
                            ->helperText('Giúp SEO và người dùng khuyết tật'),

                        Forms\Components\Toggle::make('open_in_new_tab')
                            ->label('Mở link trong tab mới')
                            ->default(true)
                            ->helperText('Khuyến nghị bật để giữ người dùng trên trang'),
                    ])
                    ->columns(2)
                    ->visible(fn (Forms\Get $get) => $get('type') === 'image'),

                Forms\Components\Section::make('Nội dung HTML/JavaScript')
                    ->description('Dán mã quảng cáo từ AdSense, Affiliate hoặc mã tùy chỉnh')
                    ->schema([
                        Forms\Components\Textarea::make('html_content')
                            ->label('Mã HTML/JavaScript')
                            ->required()
                            ->rows(12)
                            ->placeholder('<!-- Dán mã Google AdSense, mã Affiliate hoặc HTML tùy chỉnh vào đây -->

<script async src="https://pagead2.googlesyndication.com/..."></script>
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-xxx"
     data-ad-slot="xxx"
     data-ad-format="auto"></ins>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({});
</script>')
                            ->helperText('Mã sẽ được render trực tiếp trên trang. Đảm bảo mã an toàn và hợp lệ.')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Forms\Get $get) => $get('type') === 'html'),

                Forms\Components\Section::make('Cài đặt hiển thị')
                    ->description('Tùy chỉnh kích thước và độ ưu tiên')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('width')
                                    ->label('Chiều rộng (px)')
                                    ->numeric()
                                    ->placeholder('Auto')
                                    ->helperText('Để trống = tự động'),

                                Forms\Components\TextInput::make('height')
                                    ->label('Chiều cao (px)')
                                    ->numeric()
                                    ->placeholder('Auto')
                                    ->helperText('Để trống = tự động'),

                                Forms\Components\TextInput::make('priority')
                                    ->label('Độ ưu tiên')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Cao hơn = xuất hiện nhiều hơn'),
                            ]),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Kích hoạt quảng cáo')
                            ->default(true)
                            ->helperText('Tắt để tạm dừng hiển thị quảng cáo'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Hình')
                    ->disk('s3')
                    ->square()
                    ->size(60)
                    ->defaultImageUrl(fn ($record) => $record->type === 'html' ? 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiM5Y2EzYWYiIHN0cm9rZS13aWR0aD0iMiI+PHBhdGggZD0iTTE2IDR2MTZhMiAyIDAgMCAxLTIgMkg2YTIgMiAwIDAgMS0yLTJWNmEyIDIgMCAwIDEgMi0yaDZsNCA0eiIvPjwvc3ZnPg==' : null),

                Tables\Columns\TextColumn::make('name')
                    ->label('Tên')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->name),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Loại')
                    ->formatStateUsing(fn (string $state) => $state === 'image' ? 'Hình ảnh' : 'HTML/JS')
                    ->colors([
                        'primary' => 'image',
                        'success' => 'html',
                    ]),

                Tables\Columns\TextColumn::make('position')
                    ->label('Vị trí')
                    ->formatStateUsing(fn ($record) => $record->position_label)
                    ->description(fn ($record) => $record->page_label)
                    ->sortable(),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Ưu tiên')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn ($state) => $state > 5 ? 'success' : ($state > 0 ? 'warning' : 'gray')),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Trạng thái')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Loại')
                    ->options([
                        'image' => 'Hình ảnh',
                        'html' => 'HTML/JS',
                    ]),

                Tables\Filters\SelectFilter::make('page')
                    ->label('Trang')
                    ->options([
                        'all' => 'Tất cả',
                        'homepage' => 'Trang chủ',
                        'article' => 'Bài viết',
                        'category' => 'Danh mục',
                    ]),

                Tables\Filters\SelectFilter::make('position')
                    ->label('Vị trí')
                    ->options(fn () => Ad::getPositionOptions()),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Trạng thái')
                    ->boolean()
                    ->trueLabel('Đang hoạt động')
                    ->falseLabel('Đã tắt')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle')
                    ->label(fn ($record) => $record->is_active ? 'Tắt' : 'Bật')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                    ->color(fn ($record) => $record->is_active ? 'warning' : 'success')
                    ->action(function ($record) {
                        $record->update(['is_active' => !$record->is_active]);
                        app(AdService::class)->clearPositionCache($record->position);

                        Notification::make()
                            ->title($record->is_active ? 'Đã kích hoạt quảng cáo' : 'Đã tắt quảng cáo')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('duplicate')
                    ->label('Nhân bản')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function ($record) {
                        $newAd = $record->replicate();
                        $newAd->name = $record->name . ' (Bản sao)';
                        $newAd->is_active = false;
                        $newAd->save();

                        Notification::make()
                            ->title('Đã nhân bản quảng cáo')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()
                    ->label('Sửa'),

                Tables\Actions\DeleteAction::make()
                    ->label('Xóa')
                    ->after(function ($record) {
                        app(AdService::class)->clearPositionCache($record->position);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Kích hoạt')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                            app(AdService::class)->clearCache();

                            Notification::make()
                                ->title('Đã kích hoạt ' . $records->count() . ' quảng cáo')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Tắt')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                            app(AdService::class)->clearCache();

                            Notification::make()
                                ->title('Đã tắt ' . $records->count() . ' quảng cáo')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Xóa')
                        ->after(function () {
                            app(AdService::class)->clearCache();
                        }),
                ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAds::route('/'),
            'create' => Pages\CreateAd::route('/create'),
            'edit' => Pages\EditAd::route('/{record}/edit'),
        ];
    }
}
