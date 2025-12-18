<?php

namespace App\Filament\Pages;

use App\Services\SettingsService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class Settings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $view = 'filament.pages.settings';
    protected static ?string $navigationGroup = 'Quản lý';
    protected static ?int $navigationSort = 10;
    protected static ?string $title = 'Cài đặt chung';
    protected static ?string $navigationLabel = 'Cài đặt chung';

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
        $this->form->fill($this->getSettingsService()->all());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        // General Information Tab
                        Forms\Components\Tabs\Tab::make('Thông tin chung')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Section::make('Thông tin website')
                                    ->schema([
                                        Forms\Components\TextInput::make('site_name')
                                            ->label('Tên website')
                                            ->placeholder('Market Times')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('site_tagline')
                                            ->label('Slogan')
                                            ->placeholder('Cập nhật tin tức kinh tế mỗi ngày')
                                            ->maxLength(255),

                                        Forms\Components\Textarea::make('site_description')
                                            ->label('Mô tả')
                                            ->placeholder('Mô tả ngắn về website')
                                            ->rows(3)
                                            ->maxLength(500),
                                    ])
                                    ->columns(1),

                                Forms\Components\Section::make('Thông tin liên hệ')
                                    ->schema([
                                        Forms\Components\TextInput::make('contact_email')
                                            ->label('Email liên hệ')
                                            ->email()
                                            ->placeholder('contact@example.com')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('contact_phone')
                                            ->label('Số điện thoại')
                                            ->tel()
                                            ->placeholder('+84 123 456 789')
                                            ->maxLength(20),

                                        Forms\Components\Textarea::make('contact_address')
                                            ->label('Địa chỉ')
                                            ->placeholder('Địa chỉ văn phòng')
                                            ->rows(2)
                                            ->maxLength(500),
                                    ])
                                    ->columns(2),
                            ]),

                        // SEO & Meta Tab
                        Forms\Components\Tabs\Tab::make('SEO & Thẻ Meta')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Forms\Components\Section::make('Meta Tags mặc định')
                                    ->schema([
                                        Forms\Components\TextInput::make('meta_title')
                                            ->label('Meta Title')
                                            ->placeholder('Tiêu đề mặc định cho trang')
                                            ->maxLength(60)
                                            ->helperText('Độ dài tối ưu: 50-60 ký tự'),

                                        Forms\Components\Textarea::make('meta_description')
                                            ->label('Meta Description')
                                            ->placeholder('Mô tả mặc định cho trang')
                                            ->rows(3)
                                            ->maxLength(160)
                                            ->helperText('Độ dài tối ưu: 150-160 ký tự'),

                                        Forms\Components\TagsInput::make('meta_keywords')
                                            ->label('Meta Keywords')
                                            ->placeholder('Nhập từ khóa và nhấn Enter')
                                            ->helperText('Các từ khóa chính của website'),

                                        Forms\Components\Select::make('meta_robots')
                                            ->label('Meta Robots')
                                            ->options([
                                                'index, follow' => 'Index, Follow (mặc định)',
                                                'noindex, follow' => 'No Index, Follow',
                                                'index, nofollow' => 'Index, No Follow',
                                                'noindex, nofollow' => 'No Index, No Follow',
                                            ])
                                            ->default('index, follow'),
                                    ])
                                    ->columns(1),
                            ]),

                        // Social Media Tab
                        Forms\Components\Tabs\Tab::make('Mạng xã hội')
                            ->icon('heroicon-o-share')
                            ->schema([
                                Forms\Components\Section::make('Liên kết mạng xã hội')
                                    ->schema([
                                        Forms\Components\TextInput::make('facebook_url')
                                            ->label('Facebook URL')
                                            ->url()
                                            ->placeholder('https://facebook.com/yourpage')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('twitter_url')
                                            ->label('Twitter/X URL')
                                            ->url()
                                            ->placeholder('https://twitter.com/yourhandle')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('instagram_url')
                                            ->label('Instagram URL')
                                            ->url()
                                            ->placeholder('https://instagram.com/yourhandle')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('youtube_url')
                                            ->label('YouTube URL')
                                            ->url()
                                            ->placeholder('https://youtube.com/c/yourchannel')
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('linkedin_url')
                                            ->label('LinkedIn URL')
                                            ->url()
                                            ->placeholder('https://linkedin.com/company/yourcompany')
                                            ->maxLength(255),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Cài đặt Open Graph')
                                    ->schema([
                                        Forms\Components\TextInput::make('og_title')
                                            ->label('OG Title (mặc định)')
                                            ->placeholder('Tiêu đề khi chia sẻ trên mạng xã hội')
                                            ->maxLength(60),

                                        Forms\Components\Textarea::make('og_description')
                                            ->label('OG Description (mặc định)')
                                            ->placeholder('Mô tả khi chia sẻ trên mạng xã hội')
                                            ->rows(2)
                                            ->maxLength(200),
                                    ])
                                    ->columns(1),
                            ]),

                        // Branding Tab
                        Forms\Components\Tabs\Tab::make('Logo & Hình ảnh')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Forms\Components\Section::make('Logo & Biểu tượng')
                                    ->schema([
                                        Forms\Components\FileUpload::make('site_logo')
                                            ->label('Logo website')
                                            ->image()
                                            ->disk('s3')
                                            ->directory('settings/logos')
                                            ->maxSize(2048)
                                            ->helperText('Tải lên logo của website (PNG/JPG, tối đa 2MB)'),

                                        Forms\Components\FileUpload::make('site_favicon')
                                            ->label('Favicon')
                                            ->image()
                                            ->disk('s3')
                                            ->directory('settings/favicons')
                                            ->maxSize(512)
                                            ->imageResizeMode('contain')
                                            ->imageCropAspectRatio('1:1')
                                            ->helperText('Icon nhỏ hiển thị trên tab trình duyệt (PNG/ICO, 32x32 hoặc 64x64)'),

                                        Forms\Components\FileUpload::make('og_image')
                                            ->label('OG Image (mặc định)')
                                            ->image()
                                            ->disk('s3')
                                            ->directory('settings/og-images')
                                            ->maxSize(2048)
                                            ->imageResizeMode('contain')
                                            ->imageCropAspectRatio('1.91:1')
                                            ->helperText('Hình ảnh hiển thị khi chia sẻ trên mạng xã hội (1200x630px, tối đa 2MB)'),
                                    ])
                                    ->columns(1),
                            ]),

                        // Analytics Tab
                        Forms\Components\Tabs\Tab::make('Thống kê & Tracking')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Forms\Components\Section::make('Mã theo dõi & Phân tích')
                                    ->schema([
                                        Forms\Components\TextInput::make('google_analytics_id')
                                            ->label('Google Analytics ID')
                                            ->placeholder('G-XXXXXXXXXX hoặc UA-XXXXXXXXX-X')
                                            ->maxLength(50)
                                            ->helperText('Measurement ID từ Google Analytics 4'),

                                        Forms\Components\TextInput::make('google_tag_manager_id')
                                            ->label('Google Tag Manager ID')
                                            ->placeholder('GTM-XXXXXXX')
                                            ->maxLength(50)
                                            ->helperText('Container ID từ Google Tag Manager'),

                                        Forms\Components\TextInput::make('facebook_pixel_id')
                                            ->label('Facebook Pixel ID')
                                            ->placeholder('XXXXXXXXXXXXXXXXX')
                                            ->maxLength(50)
                                            ->helperText('Pixel ID từ Facebook Business'),

                                        Forms\Components\Textarea::make('custom_head_scripts')
                                            ->label('Custom Scripts (Head)')
                                            ->placeholder('<script>...</script>')
                                            ->rows(4)
                                            ->helperText('Mã scripts tùy chỉnh thêm vào <head>'),

                                        Forms\Components\Textarea::make('custom_body_scripts')
                                            ->label('Custom Scripts (Body)')
                                            ->placeholder('<script>...</script>')
                                            ->rows(4)
                                            ->helperText('Mã scripts tùy chỉnh thêm vào cuối <body>'),
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

        // Save each setting with proper type and group
        foreach ($data as $key => $value) {
            // Determine the group based on key prefix
            $group = match (true) {
                str_starts_with($key, 'site_') || str_starts_with($key, 'contact_') => 'general',
                str_starts_with($key, 'meta_') => 'seo',
                str_starts_with($key, 'facebook_') || str_starts_with($key, 'twitter_') || str_starts_with($key, 'instagram_') || str_starts_with($key, 'youtube_') || str_starts_with($key, 'linkedin_') || str_starts_with($key, 'og_') => 'social',
                str_starts_with($key, 'google_') || str_starts_with($key, 'custom_') => 'analytics',
                default => 'general',
            };

            // Determine the type based on value
            $type = match (true) {
                is_array($value) => 'json',
                is_bool($value) => 'boolean',
                str_ends_with($key, '_logo') || str_ends_with($key, '_favicon') || str_ends_with($key, '_image') => 'image',
                default => 'string',
            };

            $settingsService->set($key, $value, $type, $group);
        }

        // Clear all caches
        $settingsService->clearCache();

        Notification::make()
            ->success()
            ->title('Đã lưu cài đặt')
            ->body('Tất cả các cài đặt đã được lưu thành công.')
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
