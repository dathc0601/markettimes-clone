<?php

namespace App\Filament\Pages;

use App\Models\Article;
use App\Models\Category;
use App\Services\HomepageConfigService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class HomepageManagement extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.homepage-management';
    protected static ?string $navigationGroup = 'Management';
    protected static ?int $navigationSort = 5;
    protected static ?string $title = 'Quản lý Trang chủ';
    protected static ?string $navigationLabel = 'Trang chủ';

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public ?array $data = [];
    public bool $showPreview = false;
    public ?string $previewUrl = null;

    protected function getHomepageConfigService(): HomepageConfigService
    {
        return app(HomepageConfigService::class);
    }

    public function mount(): void
    {
        $config = $this->getHomepageConfigService()->getConfig();
        $sections = $config['sections'] ?? $this->getHomepageConfigService()->getDefaultConfig()['sections'];

        // Transform sidebar sections to sidebar_blocks array for the Repeater
        $sidebarKeys = ['sidebar_most_read', 'sidebar_valuation', 'sidebar_business', 'sidebar_special'];
        $sidebarBlocks = [];

        foreach ($sidebarKeys as $index => $key) {
            if (isset($sections[$key])) {
                $sidebarBlocks[] = array_merge(
                    ['key' => $key, 'order' => $index],
                    $sections[$key]
                );
            }
        }

        // Check if there's already a sidebar_blocks array (new format)
        if (!empty($sections['sidebar_blocks'])) {
            $sidebarBlocks = $sections['sidebar_blocks'];
        }

        $sections['sidebar_blocks'] = $sidebarBlocks;

        $this->form->fill($sections);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        // Left column - Main content tabs (2/3 width)
                        Forms\Components\Tabs::make('Homepage Sections')
                            ->tabs([
                                $this->createHeroTab(),
                                $this->createFeaturedGridTab(),
                                $this->createMostReadTealTab(),
                                $this->createLatestArticlesTab(),
                                $this->createCategoryBlocksTab(),
                            ])
                            ->columnSpan(2)
                            ->persistTabInQueryString(),

                        // Right column - Sidebar blocks (1/3 width)
                        $this->createSidebarPanel(),
                    ]),
            ])
            ->statePath('data');
    }

    protected function createHeroTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Hero')
            ->icon('heroicon-o-star')
            ->schema([
                Forms\Components\Section::make('Bài viết Hero')
                    ->description('Bài viết chính hiển thị lớn nhất trên trang chủ')
                    ->schema([
                        Forms\Components\Toggle::make('hero.enabled')
                            ->label('Hiển thị')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\Select::make('hero.source_type')
                            ->label('Nguồn bài viết')
                            ->options($this->getHomepageConfigService()->getSourceTypeOptions())
                            ->default('featured')
                            ->live()
                            ->required(),

                        Forms\Components\Group::make()
                            ->schema(fn(Forms\Get $get) => $this->getSourceConfigFields('hero', $get('hero.source_type')))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    protected function createFeaturedGridTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Nổi bật')
            ->icon('heroicon-o-squares-2x2')
            ->schema([
                Forms\Components\Section::make('Lưới bài viết nổi bật')
                    ->description('Các bài viết nổi bật hiển thị dạng lưới bên dưới Hero')
                    ->schema([
                        Forms\Components\Toggle::make('featured_grid.enabled')
                            ->label('Hiển thị')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\TextInput::make('featured_grid.count')
                            ->label('Số lượng bài viết')
                            ->numeric()
                            ->default(10)
                            ->minValue(1)
                            ->maxValue(20)
                            ->required(),

                        Forms\Components\TextInput::make('featured_grid.skip')
                            ->label('Bỏ qua N bài đầu')
                            ->numeric()
                            ->default(1)
                            ->minValue(0)
                            ->maxValue(10)
                            ->helperText('Bỏ qua bài viết Hero để không bị trùng'),

                        Forms\Components\Select::make('featured_grid.source_type')
                            ->label('Nguồn bài viết')
                            ->options($this->getHomepageConfigService()->getSourceTypeOptions())
                            ->default('featured')
                            ->live()
                            ->required(),

                        Forms\Components\Group::make()
                            ->schema(fn(Forms\Get $get) => $this->getSourceConfigFields('featured_grid', $get('featured_grid.source_type')))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    protected function createMostReadTealTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Đọc nhiều')
            ->icon('heroicon-o-fire')
            ->schema([
                Forms\Components\Section::make('Tin đọc nhiều (Nền xanh)')
                    ->description('Phần tin đọc nhiều với nền màu xanh teal')
                    ->schema([
                        Forms\Components\Toggle::make('most_read_teal.enabled')
                            ->label('Hiển thị')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\TextInput::make('most_read_teal.count')
                            ->label('Số lượng bài viết')
                            ->numeric()
                            ->default(4)
                            ->minValue(1)
                            ->maxValue(10)
                            ->required(),

                        Forms\Components\Select::make('most_read_teal.source_type')
                            ->label('Nguồn bài viết')
                            ->options($this->getHomepageConfigService()->getSourceTypeOptions())
                            ->default('most_read')
                            ->live()
                            ->required(),

                        Forms\Components\Group::make()
                            ->schema(fn(Forms\Get $get) => $this->getSourceConfigFields('most_read_teal', $get('most_read_teal.source_type')))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    protected function createLatestArticlesTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Mới nhất')
            ->icon('heroicon-o-clock')
            ->schema([
                Forms\Components\Section::make('Bài viết mới nhất')
                    ->description('Danh sách bài viết mới nhất với phân trang và nút Xem thêm')
                    ->schema([
                        Forms\Components\Toggle::make('latest_articles.enabled')
                            ->label('Hiển thị')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\TextInput::make('latest_articles.count')
                            ->label('Số bài mỗi trang')
                            ->numeric()
                            ->default(12)
                            ->minValue(6)
                            ->maxValue(24)
                            ->required()
                            ->helperText('Số bài viết hiển thị mỗi lần tải'),

                        Forms\Components\Select::make('latest_articles.source_config.category_ids')
                            ->label('Lọc theo danh mục (tùy chọn)')
                            ->multiple()
                            ->options(Category::pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Tất cả danh mục'),
                    ])
                    ->columns(2),
            ]);
    }

    protected function createSidebarPanel(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Sidebar Blocks')
            ->description('Kéo thả để sắp xếp thứ tự hiển thị')
            ->icon('heroicon-o-bars-3-center-left')
            ->schema([
                Forms\Components\Repeater::make('sidebar_blocks')
                    ->schema([
                        Forms\Components\Hidden::make('key'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Toggle::make('enabled')
                                    ->label('Hiển thị')
                                    ->default(true)
                                    ->inline(false),

                                Forms\Components\TextInput::make('count')
                                    ->label('Số lượng')
                                    ->numeric()
                                    ->default(5)
                                    ->minValue(1)
                                    ->maxValue(10),
                            ]),

                        Forms\Components\TextInput::make('title')
                            ->label('Tiêu đề')
                            ->maxLength(100),

                        Forms\Components\Select::make('source_type')
                            ->label('Nguồn bài viết')
                            ->options($this->getHomepageConfigService()->getSourceTypeOptions())
                            ->live(),

                        Forms\Components\Group::make()
                            ->schema(fn(Forms\Get $get) => $this->getSidebarSourceConfigFields($get('source_type'))),
                    ])
                    ->reorderable()
                    ->reorderableWithDragAndDrop()
                    ->collapsible()
                    ->collapsed()
                    ->itemLabel(fn(array $state): ?string => $state['title'] ?? 'Block')
                    ->addable(false)
                    ->deletable(false)
                    ->defaultItems(0),
            ])
            ->columnSpan(1)
            ->collapsible();
    }

    /**
     * Get source config fields for sidebar blocks (used in Repeater)
     */
    protected function getSidebarSourceConfigFields(?string $sourceType): array
    {
        return match ($sourceType) {
            'category' => [
                Forms\Components\Select::make('source_config.category_ids')
                    ->label('Chọn danh mục')
                    ->multiple()
                    ->options(Category::pluck('name', 'id'))
                    ->searchable(),
            ],
            'manual' => [
                Forms\Components\Select::make('source_config.manual_article_ids')
                    ->label('Chọn bài viết')
                    ->multiple()
                    ->options(fn() => Article::published()
                        ->latest('published_at')
                        ->limit(200)
                        ->get()
                        ->mapWithKeys(fn($article) => [
                            $article->id => "[{$article->category?->name}] {$article->title}"
                        ]))
                    ->searchable(),
            ],
            'most_read' => [
                Forms\Components\TextInput::make('source_config.filters.date_range')
                    ->label('Trong N ngày')
                    ->numeric()
                    ->default(30)
                    ->suffix('ngày'),
            ],
            'featured', 'latest' => [
                Forms\Components\Select::make('source_config.category_ids')
                    ->label('Lọc theo danh mục')
                    ->multiple()
                    ->options(Category::pluck('name', 'id'))
                    ->searchable(),
            ],
            default => [],
        };
    }

    protected function createCategoryBlocksTab(): Forms\Components\Tabs\Tab
    {
        return Forms\Components\Tabs\Tab::make('Danh mục')
            ->icon('heroicon-o-rectangle-stack')
            ->schema([
                Forms\Components\Section::make('Khối danh mục')
                    ->description('Hiển thị các danh mục cha với bài viết của chúng')
                    ->schema([
                        Forms\Components\Toggle::make('category_blocks.enabled')
                            ->label('Hiển thị')
                            ->default(false)
                            ->inline(false),

                        Forms\Components\TextInput::make('category_blocks.count')
                            ->label('Số bài mỗi danh mục')
                            ->numeric()
                            ->default(4)
                            ->minValue(1)
                            ->maxValue(10)
                            ->required(),

                        Forms\Components\Select::make('category_blocks.source_config.excluded_category_ids')
                            ->label('Loại trừ danh mục')
                            ->multiple()
                            ->options(Category::whereNull('parent_id')->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Không loại trừ danh mục nào'),
                    ])
                    ->columns(2),
            ]);
    }

    /**
     * Get dynamic source configuration fields based on source type
     */
    protected function getSourceConfigFields(string $key, ?string $sourceType): array
    {
        return match ($sourceType) {
            'category' => [
                Forms\Components\Select::make("{$key}.source_config.category_ids")
                    ->label('Chọn danh mục')
                    ->multiple()
                    ->options(Category::pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),
            ],
            'manual' => [
                Forms\Components\Select::make("{$key}.source_config.manual_article_ids")
                    ->label('Chọn bài viết')
                    ->multiple()
                    ->options(fn() => Article::published()
                        ->latest('published_at')
                        ->limit(200)
                        ->get()
                        ->mapWithKeys(fn($article) => [
                            $article->id => "[{$article->category?->name}] {$article->title}"
                        ]))
                    ->searchable()
                    ->required()
                    ->helperText('Chọn các bài viết theo thứ tự mong muốn')
                    ->columnSpanFull(),
            ],
            'most_read' => [
                Forms\Components\TextInput::make("{$key}.source_config.filters.date_range")
                    ->label('Trong N ngày gần nhất')
                    ->numeric()
                    ->default(30)
                    ->minValue(1)
                    ->maxValue(365)
                    ->suffix('ngày')
                    ->helperText('Chỉ tính lượt xem trong khoảng thời gian này'),
            ],
            'featured', 'latest' => [
                Forms\Components\Select::make("{$key}.source_config.category_ids")
                    ->label('Lọc theo danh mục (tùy chọn)')
                    ->multiple()
                    ->options(Category::pluck('name', 'id'))
                    ->searchable()
                    ->placeholder('Tất cả danh mục'),

                Forms\Components\Toggle::make("{$key}.source_config.filters.is_featured")
                    ->label('Chỉ bài nổi bật')
                    ->default($sourceType === 'featured')
                    ->inline(false),
            ],
            'special_publication' => [
                Forms\Components\Placeholder::make("{$key}.source_info")
                    ->label('')
                    ->content('Tự động lấy các bài viết được đánh dấu là Đặc biệt'),
            ],
            default => [],
        };
    }

    /**
     * Transform form data to storage format (sidebar_blocks array to individual sections)
     */
    protected function transformFormDataForStorage(array $data): array
    {
        // Keep sidebar_blocks array for the new format
        if (!empty($data['sidebar_blocks'])) {
            // Reindex with order
            $sidebarBlocks = [];
            foreach ($data['sidebar_blocks'] as $index => $block) {
                $block['order'] = $index;
                $sidebarBlocks[] = $block;
            }
            $data['sidebar_blocks'] = $sidebarBlocks;
        }

        return $data;
    }

    /**
     * Save as draft
     */
    public function saveDraft(): void
    {
        $data = $this->form->getState();
        $data = $this->transformFormDataForStorage($data);
        $this->getHomepageConfigService()->saveDraft($data);

        Notification::make()
            ->success()
            ->title('Đã lưu bản nháp')
            ->body('Các thay đổi đã được lưu. Nhấn "Xem trước" để kiểm tra hoặc "Xuất bản" để áp dụng.')
            ->send();
    }

    /**
     * Publish changes
     */
    public function publish(): void
    {
        $data = $this->form->getState();
        $data = $this->transformFormDataForStorage($data);
        $this->getHomepageConfigService()->saveSections($data);

        Notification::make()
            ->success()
            ->title('Đã xuất bản')
            ->body('Cấu hình trang chủ đã được cập nhật và đang hiển thị trên website.')
            ->send();
    }

    /**
     * Open preview
     */
    public function openPreview(): void
    {
        $data = $this->form->getState();
        $data = $this->transformFormDataForStorage($data);
        $this->getHomepageConfigService()->saveDraft($data);

        $token = $this->generatePreviewToken();
        $this->previewUrl = route('home') . '?preview=draft&token=' . $token;
        $this->showPreview = true;
    }

    /**
     * Close preview modal
     */
    public function closePreview(): void
    {
        $this->showPreview = false;
        $this->previewUrl = null;
    }

    /**
     * Generate secure preview token
     */
    protected function generatePreviewToken(): string
    {
        $data = auth()->id() . '|' . now()->timestamp;
        return hash_hmac('sha256', $data, config('app.key'));
    }

    /**
     * Validate preview token
     */
    public static function validatePreviewToken(string $token, int $userId): bool
    {
        // Token valid for 1 hour
        for ($i = 0; $i < 60; $i++) {
            $timestamp = now()->subMinutes($i)->timestamp;
            $data = $userId . '|' . $timestamp;
            $expectedToken = hash_hmac('sha256', $data, config('app.key'));
            if (hash_equals($expectedToken, $token)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Clear all homepage caches
     */
    public function clearCache(): void
    {
        $this->getHomepageConfigService()->clearCache();

        Notification::make()
            ->success()
            ->title('Đã xóa cache')
            ->body('Cache trang chủ đã được xóa. Dữ liệu sẽ được tải mới.')
            ->send();
    }

    /**
     * Discard draft
     */
    public function discardDraft(): void
    {
        $this->getHomepageConfigService()->discardDraft();

        // Reload published config with sidebar_blocks transformation
        $config = $this->getHomepageConfigService()->getConfig();
        $sections = $config['sections'];

        // Transform to sidebar_blocks format for the form
        $sections = $this->transformSectionsToFormData($sections);

        $this->form->fill($sections);

        Notification::make()
            ->warning()
            ->title('Đã hủy bản nháp')
            ->body('Các thay đổi chưa xuất bản đã được hủy.')
            ->send();
    }

    /**
     * Transform stored sections to form data format (with sidebar_blocks)
     */
    protected function transformSectionsToFormData(array $sections): array
    {
        $sidebarKeys = ['sidebar_most_read', 'sidebar_valuation', 'sidebar_business', 'sidebar_special'];
        $sidebarBlocks = [];

        // Check if already in new format
        if (!empty($sections['sidebar_blocks'])) {
            return $sections;
        }

        foreach ($sidebarKeys as $index => $key) {
            if (isset($sections[$key])) {
                $sidebarBlocks[] = array_merge(
                    ['key' => $key, 'order' => $index],
                    $sections[$key]
                );
            }
        }

        $sections['sidebar_blocks'] = $sidebarBlocks;
        return $sections;
    }

    /**
     * Get header actions
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearCache')
                ->label('Xóa Cache')
                ->icon('heroicon-o-arrow-path')
                ->action('clearCache')
                ->color('gray')
                ->tooltip('Xóa cache để tải lại dữ liệu mới'),

            Action::make('preview')
                ->label('Xem trước')
                ->icon('heroicon-o-eye')
                ->action('openPreview')
                ->color('info'),

            Action::make('saveDraft')
                ->label('Lưu nháp')
                ->icon('heroicon-o-document')
                ->action('saveDraft')
                ->color('warning'),

            Action::make('publish')
                ->label('Xuất bản')
                ->icon('heroicon-o-rocket-launch')
                ->action('publish')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Xuất bản cấu hình')
                ->modalDescription('Bạn có chắc muốn áp dụng cấu hình này cho trang chủ? Thay đổi sẽ hiển thị ngay lập tức trên website.')
                ->modalSubmitActionLabel('Xuất bản'),
        ];
    }

    /**
     * Get form actions (displayed below the form)
     */
    protected function getFormActions(): array
    {
        return [];
    }
}
