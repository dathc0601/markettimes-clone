<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;

class HomepageConfigService
{
    protected SettingsService $settingsService;
    protected string $configKey = 'homepage_config';
    protected int $cacheDuration = 900; // 15 minutes

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Get the current published configuration
     */
    public function getConfig(): array
    {
        $config = $this->settingsService->get($this->configKey);
        return $config ?? $this->getDefaultConfig();
    }

    /**
     * Get draft configuration for preview
     */
    public function getDraftConfig(): ?array
    {
        $config = $this->getConfig();
        return $config['draft'] ?? null;
    }

    /**
     * Check if there's an unpublished draft
     */
    public function hasDraft(): bool
    {
        return $this->getDraftConfig() !== null;
    }

    /**
     * Save draft configuration
     */
    public function saveDraft(array $sections): void
    {
        $config = $this->getConfig();
        $config['draft'] = [
            'sections' => $sections,
            'saved_at' => now()->toIso8601String()
        ];
        $this->saveConfig($config);
    }

    /**
     * Publish draft to live
     */
    public function publishDraft(): void
    {
        $config = $this->getConfig();
        if ($config['draft']) {
            $config['sections'] = $config['draft']['sections'];
            $config['last_published_at'] = now()->toIso8601String();
            $config['draft'] = null;
            $this->saveConfig($config);
            $this->clearCache();
        }
    }

    /**
     * Save sections directly (publish immediately)
     */
    public function saveSections(array $sections): void
    {
        $config = $this->getConfig();
        $config['sections'] = $sections;
        $config['last_published_at'] = now()->toIso8601String();
        $config['draft'] = null;
        $this->saveConfig($config);
        $this->clearCache();
    }

    /**
     * Discard draft
     */
    public function discardDraft(): void
    {
        $config = $this->getConfig();
        $config['draft'] = null;
        $this->saveConfig($config);
    }

    /**
     * Get articles for a specific section
     */
    public function getArticlesForSection(string $sectionKey, ?array $overrideConfig = null): Collection
    {
        $config = $this->getConfig();
        $sectionConfig = $overrideConfig ?? ($config['sections'][$sectionKey] ?? null);

        if (!$sectionConfig || !($sectionConfig['enabled'] ?? true)) {
            return collect();
        }

        // For preview, don't use cache
        if ($overrideConfig) {
            return $this->fetchArticlesForSection($sectionConfig);
        }

        $cacheKey = "homepage.section.{$sectionKey}";
        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($sectionConfig) {
            return $this->fetchArticlesForSection($sectionConfig);
        });
    }

    /**
     * Fetch articles based on section configuration
     */
    protected function fetchArticlesForSection(array $sectionConfig): Collection
    {
        $sourceType = $sectionConfig['source_type'] ?? 'latest';
        $sourceConfig = $sectionConfig['source_config'] ?? [];
        $count = $sectionConfig['count'] ?? 10;
        $skip = $sectionConfig['skip'] ?? 0;

        // Handle manual selection first
        if ($sourceType === 'manual' && !empty($sourceConfig['manual_article_ids'])) {
            $ids = $sourceConfig['manual_article_ids'];
            return Article::with(['category', 'author'])
                ->published()
                ->whereIn('id', $ids)
                ->get()
                ->sortBy(function ($article) use ($ids) {
                    return array_search($article->id, $ids);
                })
                ->values()
                ->take($count);
        }

        $query = Article::with(['category', 'author'])->published();

        // Apply category filter (supports multiple categories)
        if (!empty($sourceConfig['category_ids'])) {
            $query->whereIn('category_id', (array) $sourceConfig['category_ids']);
        } elseif (!empty($sourceConfig['category_id'])) {
            // Backward compatibility for single category
            $query->where('category_id', $sourceConfig['category_id']);
        } elseif (!empty($sourceConfig['category_slug'])) {
            $query->whereHas('category', fn($q) => $q->where('slug', $sourceConfig['category_slug']));
        }

        // Apply filters
        $filters = $sourceConfig['filters'] ?? [];

        if (!empty($filters['is_featured'])) {
            $query->featured();
        }

        if (!empty($filters['is_special_publication'])) {
            $query->where('is_special_publication', true);
        }

        // Apply date_range filter only for non-most_read source types
        // For most_read, we want all articles ordered by view_count
        if (!empty($filters['date_range']) && $sourceType !== 'most_read') {
            $query->where('published_at', '>=', now()->subDays((int) $filters['date_range']));
        }

        // Apply ordering based on source type
        switch ($sourceType) {
            case 'most_read':
                $query->orderBy('view_count', 'desc');
                break;
            case 'featured':
            case 'latest':
            case 'category':
            case 'special_publication':
            default:
                $query->latest('published_at');
                break;
        }

        if ($skip > 0) {
            $query->skip($skip);
        }

        return $query->take($count)->get();
    }

    /**
     * Get latest articles with pagination
     */
    public function getLatestArticlesPaginated(?array $overrideConfig = null): LengthAwarePaginator
    {
        $config = $this->getConfig();
        $sectionConfig = $overrideConfig ?? ($config['sections']['latest_articles'] ?? []);
        $perPage = $sectionConfig['count'] ?? 12;

        $query = Article::with(['category', 'author'])->published();

        // Apply category filter if set (supports multiple)
        if (!empty($sectionConfig['source_config']['category_ids'])) {
            $query->whereIn('category_id', (array) $sectionConfig['source_config']['category_ids']);
        } elseif (!empty($sectionConfig['source_config']['category_id'])) {
            $query->where('category_id', $sectionConfig['source_config']['category_id']);
        }

        return $query->latest('published_at')->paginate($perPage);
    }

    /**
     * Get category blocks data
     */
    public function getCategoryBlocks(?array $overrideConfig = null): Collection
    {
        $config = $this->getConfig();
        $sectionConfig = $overrideConfig ?? ($config['sections']['category_blocks'] ?? []);

        if (!($sectionConfig['enabled'] ?? false)) {
            return collect();
        }

        $articlesPerCategory = $sectionConfig['count'] ?? 4;
        $excludedIds = $sectionConfig['source_config']['excluded_category_ids'] ?? [];

        $cacheKey = 'homepage.section.category_blocks';

        if ($overrideConfig) {
            return $this->fetchCategoryBlocks($articlesPerCategory, $excludedIds);
        }

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($articlesPerCategory, $excludedIds) {
            return $this->fetchCategoryBlocks($articlesPerCategory, $excludedIds);
        });
    }

    /**
     * Fetch category blocks from database
     */
    protected function fetchCategoryBlocks(int $articlesPerCategory, array $excludedIds): Collection
    {
        $query = Category::with(['articles' => function ($q) use ($articlesPerCategory) {
            $q->with(['category', 'author'])
                ->published()
                ->latest('published_at')
                ->take($articlesPerCategory);
        }])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order');

        if (!empty($excludedIds)) {
            $query->whereNotIn('id', $excludedIds);
        }

        return $query->get();
    }

    /**
     * Get all homepage data (for controller)
     */
    public function getHomepageData(bool $isPreview = false): array
    {
        $config = $this->getConfig();
        $sections = $isPreview && $this->getDraftConfig()
            ? $this->getDraftConfig()['sections']
            : $config['sections'];

        $overrideConfig = $isPreview ? $sections : null;

        // Build sidebar blocks data with ordering support
        $sidebarBlocks = $this->getSidebarBlocksData($sections, $isPreview);

        // Get sidebar configs (supports both old and new format)
        $sidebarMostReadConfig = $this->getSidebarConfig($sections, 'sidebar_most_read');
        $sidebarValuationConfig = $this->getSidebarConfig($sections, 'sidebar_valuation');
        $sidebarBusinessConfig = $this->getSidebarConfig($sections, 'sidebar_business');
        $sidebarSpecialConfig = $this->getSidebarConfig($sections, 'sidebar_special');

        return [
            'heroArticle' => $this->getArticlesForSection('hero', $overrideConfig ? $sections['hero'] : null)->first(),
            'featuredArticles' => $this->getArticlesForSection('featured_grid', $overrideConfig ? $sections['featured_grid'] : null),
            'mostReadTeal' => $this->getArticlesForSection('most_read_teal', $overrideConfig ? $sections['most_read_teal'] : null),
            'latestArticles' => $this->getLatestArticlesPaginated($overrideConfig ? $sections['latest_articles'] : null),
            // Keep legacy variables for backward compatibility - always use config from getSidebarConfig
            'sidebarMostRead' => $sidebarMostReadConfig ? $this->fetchSidebarArticles('sidebar_most_read', $sidebarMostReadConfig, $isPreview) : collect(),
            'valuationArticles' => $sidebarValuationConfig ? $this->fetchSidebarArticles('sidebar_valuation', $sidebarValuationConfig, $isPreview) : collect(),
            'businessArticles' => $sidebarBusinessConfig ? $this->fetchSidebarArticles('sidebar_business', $sidebarBusinessConfig, $isPreview) : collect(),
            'specialPublications' => $sidebarSpecialConfig ? $this->fetchSidebarArticles('sidebar_special', $sidebarSpecialConfig, $isPreview) : collect(),
            'categories' => $this->getCategoryBlocks($overrideConfig ? $sections['category_blocks'] : null),
            'sectionConfig' => $sections,
            // New sidebar blocks array with ordering
            'sidebarBlocks' => $sidebarBlocks,
        ];
    }

    /**
     * Get sidebar config by key (supports both old and new format)
     */
    protected function getSidebarConfig(array $sections, string $key): ?array
    {
        // New format: sidebar_blocks array
        if (!empty($sections['sidebar_blocks'])) {
            foreach ($sections['sidebar_blocks'] as $block) {
                if (($block['key'] ?? '') === $key) {
                    return $block;
                }
            }
            return null;
        }

        // Old format: direct key access
        return $sections[$key] ?? null;
    }

    /**
     * Get sidebar blocks data with articles, sorted by order
     */
    protected function getSidebarBlocksData(array $sections, bool $isPreview = false): array
    {
        $blocks = [];

        // New format: sidebar_blocks array
        if (!empty($sections['sidebar_blocks'])) {
            $sidebarBlocks = collect($sections['sidebar_blocks'])->sortBy('order')->values();

            foreach ($sidebarBlocks as $block) {
                if (!($block['enabled'] ?? true)) {
                    continue;
                }

                $key = $block['key'] ?? '';

                // Always pass block config, use isPreview flag to control caching
                $blocks[] = [
                    'key' => $key,
                    'title' => $block['title'] ?? $this->getSectionLabels()[$key] ?? 'Block',
                    'articles' => $this->fetchSidebarArticles($key, $block, $isPreview),
                    'config' => $block,
                ];
            }

            return $blocks;
        }

        // Old format: fallback to fixed order
        $sidebarKeys = ['sidebar_most_read', 'sidebar_valuation', 'sidebar_business', 'sidebar_special'];

        foreach ($sidebarKeys as $key) {
            $config = $sections[$key] ?? null;
            if (!$config || !($config['enabled'] ?? true)) {
                continue;
            }

            $blocks[] = [
                'key' => $key,
                'title' => $config['title'] ?? $this->getSectionLabels()[$key] ?? 'Block',
                'articles' => $this->fetchSidebarArticles($key, $config, $isPreview),
                'config' => $config,
            ];
        }

        return $blocks;
    }

    /**
     * Fetch sidebar articles with optional caching
     */
    protected function fetchSidebarArticles(string $key, array $config, bool $isPreview = false): Collection
    {
        // Skip cache in preview mode
        if ($isPreview) {
            return $this->fetchArticlesForSection($config);
        }

        // Use cache in normal mode
        $cacheKey = "homepage.section.{$key}";
        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($config) {
            return $this->fetchArticlesForSection($config);
        });
    }

    /**
     * Clear all homepage section caches
     */
    public function clearCache(): void
    {
        $sections = [
            'hero',
            'featured_grid',
            'most_read_teal',
            'sidebar_most_read',
            'sidebar_valuation',
            'sidebar_business',
            'sidebar_special',
            'category_blocks'
        ];

        foreach ($sections as $section) {
            Cache::forget("homepage.section.{$section}");
        }

        // Also clear legacy cache keys
        Cache::forget('homepage.categories');
        Cache::forget('homepage.mostRead');
        Cache::forget('homepage.featured_articles');
        Cache::forget('homepage.latest_articles');

        // Clear settings cache for homepage config
        $this->settingsService->forget($this->configKey);
    }

    /**
     * Get default configuration matching current hardcoded behavior
     */
    public function getDefaultConfig(): array
    {
        return [
            'version' => 1,
            'sections' => [
                'hero' => [
                    'enabled' => true,
                    'count' => 1,
                    'source_type' => 'featured',
                    'source_config' => [
                        'category_ids' => [],
                        'category_slug' => null,
                        'filters' => ['is_featured' => true],
                        'manual_article_ids' => []
                    ],
                    'fallback' => 'latest'
                ],
                'featured_grid' => [
                    'enabled' => true,
                    'count' => 10,
                    'skip' => 1,
                    'source_type' => 'featured',
                    'source_config' => [
                        'category_ids' => [],
                        'category_slug' => null,
                        'filters' => ['is_featured' => true],
                        'manual_article_ids' => []
                    ],
                    'fallback' => 'latest'
                ],
                'most_read_teal' => [
                    'enabled' => true,
                    'count' => 4,
                    'source_type' => 'most_read',
                    'source_config' => [
                        'category_ids' => [],
                        'category_slug' => null,
                        'filters' => ['date_range' => 30],
                        'manual_article_ids' => []
                    ],
                    'fallback' => 'latest'
                ],
                'latest_articles' => [
                    'enabled' => true,
                    'count' => 12,
                    'source_type' => 'latest',
                    'source_config' => [
                        'category_ids' => [],
                        'category_slug' => null,
                        'filters' => [],
                        'manual_article_ids' => []
                    ]
                ],
                'sidebar_most_read' => [
                    'enabled' => true,
                    'count' => 5,
                    'title' => 'Tin đọc nhiều',
                    'source_type' => 'most_read',
                    'source_config' => [
                        'category_ids' => [],
                        'category_slug' => null,
                        'filters' => ['date_range' => 7],
                        'manual_article_ids' => []
                    ],
                    'fallback' => 'latest'
                ],
                'sidebar_valuation' => [
                    'enabled' => true,
                    'count' => 4,
                    'title' => 'Diễn đàn Thẩm định giá',
                    'source_type' => 'category',
                    'source_config' => [
                        'category_ids' => [],
                        'category_slug' => 'tham-dinh-gia',
                        'filters' => [],
                        'manual_article_ids' => []
                    ],
                    'fallback' => 'latest'
                ],
                'sidebar_business' => [
                    'enabled' => true,
                    'count' => 5,
                    'title' => 'Nhịp cầu doanh nghiệp',
                    'source_type' => 'category',
                    'source_config' => [
                        'category_ids' => [],
                        'category_slug' => 'kinh-doanh',
                        'filters' => [],
                        'manual_article_ids' => []
                    ],
                    'fallback' => 'latest'
                ],
                'sidebar_special' => [
                    'enabled' => true,
                    'count' => 2,
                    'title' => 'Đặc biệt',
                    'source_type' => 'special_publication',
                    'source_config' => [
                        'category_ids' => [],
                        'category_slug' => null,
                        'filters' => ['is_special_publication' => true],
                        'manual_article_ids' => []
                    ],
                    'fallback' => 'latest'
                ],
                'category_blocks' => [
                    'enabled' => false,
                    'count' => 4,
                    'source_type' => 'parent_categories',
                    'source_config' => [
                        'excluded_category_ids' => [],
                        'manual_category_ids' => []
                    ]
                ]
            ],
            'draft' => null,
            'last_published_at' => null
        ];
    }

    /**
     * Save configuration to settings
     */
    protected function saveConfig(array $config): void
    {
        $this->settingsService->set($this->configKey, $config, 'json', 'homepage');
    }

    /**
     * Get section labels for admin display
     */
    public function getSectionLabels(): array
    {
        return [
            'hero' => 'Bài viết Hero',
            'featured_grid' => 'Lưới bài nổi bật',
            'most_read_teal' => 'Tin đọc nhiều (Xanh)',
            'latest_articles' => 'Bài viết mới nhất',
            'sidebar_most_read' => 'Sidebar - Đọc nhiều',
            'sidebar_valuation' => 'Sidebar - Thẩm định giá',
            'sidebar_business' => 'Sidebar - Kinh doanh',
            'sidebar_special' => 'Sidebar - Đặc biệt',
            'category_blocks' => 'Khối danh mục'
        ];
    }

    /**
     * Get source type options for admin
     */
    public function getSourceTypeOptions(): array
    {
        return [
            'featured' => 'Bài viết nổi bật',
            'latest' => 'Bài viết mới nhất',
            'most_read' => 'Đọc nhiều nhất',
            'category' => 'Theo danh mục',
            'special_publication' => 'Đặc biệt',
            'manual' => 'Chọn thủ công'
        ];
    }
}
