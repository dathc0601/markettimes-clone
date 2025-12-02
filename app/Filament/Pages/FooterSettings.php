<?php

namespace App\Filament\Pages;

use App\Services\SettingsService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class FooterSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.footer-settings';
    protected static ?string $navigationGroup = 'Quản lý';
    protected static ?int $navigationSort = 11;
    protected static ?string $title = 'Cài đặt Footer';
    protected static ?string $navigationLabel = 'Cài đặt Footer';
    protected static ?string $slug = 'footer';

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public ?array $data = [];

    protected function getSettingsService(): SettingsService
    {
        return app(SettingsService::class);
    }

    public function mount(): void
    {
        $settings = $this->getSettingsService()->all('footer');

        // Decode JSON fields
        if (isset($settings['footer_editors']) && is_string($settings['footer_editors'])) {
            $settings['footer_editors'] = json_decode($settings['footer_editors'], true) ?? [];
        }
        if (isset($settings['footer_offices']) && is_string($settings['footer_offices'])) {
            $settings['footer_offices'] = json_decode($settings['footer_offices'], true) ?? [];
        }

        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Footer Settings')
                    ->tabs([
                        // Editorial Info Tab
                        Forms\Components\Tabs\Tab::make('Thông tin tòa soạn')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Forms\Components\Section::make('Thông tin chung')
                                    ->schema([
                                        Forms\Components\TextInput::make('footer_magazine_title')
                                            ->label('Tên tạp chí')
                                            ->placeholder('Tạp chí điện tử Nhịp sống thị trường')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('footer_organization')
                                            ->label('Cơ quan chủ quản')
                                            ->placeholder('Cơ quan của Hội Thẩm định giá Việt Nam')
                                            ->maxLength(255),
                                    ])
                                    ->columns(1),

                                Forms\Components\Section::make('Ban biên tập')
                                    ->schema([
                                        Forms\Components\Repeater::make('footer_editors')
                                            ->label('Danh sách biên tập viên')
                                            ->schema([
                                                Forms\Components\Select::make('role')
                                                    ->label('Chức vụ')
                                                    ->options([
                                                        'Tổng Biên tập' => 'Tổng Biên tập',
                                                        'Phó Tổng Biên tập' => 'Phó Tổng Biên tập',
                                                        'Thư ký tòa soạn' => 'Thư ký tòa soạn',
                                                        'Biên tập viên' => 'Biên tập viên',
                                                    ])
                                                    ->required(),
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Họ và tên')
                                                    ->required()
                                                    ->maxLength(255),
                                            ])
                                            ->columns(2)
                                            ->reorderable()
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['role'] ?? null)
                                            ->addActionLabel('Thêm biên tập viên')
                                            ->defaultItems(0),
                                    ])
                                    ->columns(1),
                            ]),

                        // Contact Tab
                        Forms\Components\Tabs\Tab::make('Liên hệ')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                Forms\Components\Section::make('Văn phòng')
                                    ->schema([
                                        Forms\Components\Repeater::make('footer_offices')
                                            ->label('Danh sách văn phòng')
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Tên văn phòng')
                                                    ->placeholder('Văn phòng Hà Nội')
                                                    ->required()
                                                    ->maxLength(255),
                                                Forms\Components\Textarea::make('address')
                                                    ->label('Địa chỉ')
                                                    ->placeholder('Số 8, Phạm Hùng, Mỹ Đình, Nam Từ Liêm, Hà Nội')
                                                    ->required()
                                                    ->rows(2)
                                                    ->maxLength(500),
                                            ])
                                            ->columns(1)
                                            ->reorderable()
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                                            ->addActionLabel('Thêm văn phòng')
                                            ->defaultItems(0),
                                    ])
                                    ->columns(1),

                                Forms\Components\Section::make('Thông tin liên hệ')
                                    ->schema([
                                        Forms\Components\TextInput::make('footer_phone')
                                            ->label('Số điện thoại')
                                            ->tel()
                                            ->placeholder('(024) 1234 5678')
                                            ->maxLength(50),

                                        Forms\Components\TextInput::make('footer_email')
                                            ->label('Email')
                                            ->email()
                                            ->placeholder('info@markettimes.vn')
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),
                            ]),

                        // Legal Tab
                        Forms\Components\Tabs\Tab::make('Thông tin pháp lý')
                            ->icon('heroicon-o-document-check')
                            ->schema([
                                Forms\Components\Section::make('Giấy phép hoạt động')
                                    ->schema([
                                        Forms\Components\TextInput::make('footer_license_number')
                                            ->label('Số giấy phép')
                                            ->placeholder('535/GP-BTTTT')
                                            ->maxLength(100),

                                        Forms\Components\TextInput::make('footer_license_date')
                                            ->label('Ngày cấp')
                                            ->placeholder('21/08/2021')
                                            ->maxLength(50),

                                        Forms\Components\TextInput::make('footer_license_issuer')
                                            ->label('Cơ quan cấp')
                                            ->placeholder('Bộ Thông tin và Truyền thông')
                                            ->maxLength(255),
                                    ])
                                    ->columns(1),
                            ]),

                        // Social Media Tab
                        Forms\Components\Tabs\Tab::make('Mạng xã hội')
                            ->icon('heroicon-o-share')
                            ->schema([
                                Forms\Components\Section::make('Liên kết mạng xã hội')
                                    ->description('Chỉ những liên kết được điền sẽ hiển thị trên footer')
                                    ->schema([
                                        Forms\Components\TextInput::make('footer_facebook_url')
                                            ->label('Facebook')
                                            ->url()
                                            ->placeholder('https://facebook.com/yourpage')
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-link'),

                                        Forms\Components\TextInput::make('footer_twitter_url')
                                            ->label('Twitter/X')
                                            ->url()
                                            ->placeholder('https://twitter.com/yourhandle')
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-link'),

                                        Forms\Components\TextInput::make('footer_youtube_url')
                                            ->label('YouTube')
                                            ->url()
                                            ->placeholder('https://youtube.com/c/yourchannel')
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-link'),

                                        Forms\Components\TextInput::make('footer_instagram_url')
                                            ->label('Instagram')
                                            ->url()
                                            ->placeholder('https://instagram.com/yourhandle')
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-link'),

                                        Forms\Components\TextInput::make('footer_linkedin_url')
                                            ->label('LinkedIn')
                                            ->url()
                                            ->placeholder('https://linkedin.com/company/yourcompany')
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-link'),

                                        Forms\Components\TextInput::make('footer_tiktok_url')
                                            ->label('TikTok')
                                            ->url()
                                            ->placeholder('https://tiktok.com/@yourhandle')
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-link'),
                                    ])
                                    ->columns(2),
                            ]),

                        // Copyright Tab
                        Forms\Components\Tabs\Tab::make('Bản quyền')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Forms\Components\Section::make('Thông tin bản quyền')
                                    ->schema([
                                        Forms\Components\TextInput::make('footer_copyright_text')
                                            ->label('Nội dung bản quyền')
                                            ->placeholder('Toàn bộ bản quyền thuộc Nhịp sống thị trường')
                                            ->helperText('Năm sẽ được tự động thêm vào trước nội dung')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('footer_powered_by')
                                            ->label('Powered by')
                                            ->placeholder('POWERED BY ONECMS')
                                            ->maxLength(255),
                                    ])
                                    ->columns(1),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settingsService = $this->getSettingsService();

        foreach ($data as $key => $value) {
            // Determine the type based on value
            $type = match (true) {
                is_array($value) => 'json',
                is_bool($value) => 'boolean',
                default => 'string',
            };

            $settingsService->set($key, $value, $type, 'footer');
        }

        // Clear all caches
        $settingsService->clearCache();

        Notification::make()
            ->success()
            ->title('Đã lưu cài đặt Footer')
            ->body('Tất cả các cài đặt footer đã được lưu thành công.')
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('save')
                ->label('Lưu cài đặt')
                ->action('save')
                ->color('primary')
                ->icon('heroicon-o-check'),
        ];
    }
}
