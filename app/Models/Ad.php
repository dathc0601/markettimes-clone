<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Ad extends Model
{
    protected $fillable = [
        'name',
        'type',
        'position',
        'page',
        'image_path',
        'click_url',
        'alt_text',
        'open_in_new_tab',
        'html_content',
        'width',
        'height',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'open_in_new_tab' => 'boolean',
        'width' => 'integer',
        'height' => 'integer',
        'priority' => 'integer',
    ];

    /**
     * Get all available ad positions organized by page
     */
    public static function getPositions(): array
    {
        return [
            'homepage' => [
                'home_header_banner' => ['label' => 'Banner header', 'size' => '970x90', 'description' => 'Phía dưới header, trên hero'],
                'home_hero_below' => ['label' => 'Dưới hero', 'size' => '970x250', 'description' => 'Giữa hero và featured grid'],
                'home_sidebar_top' => ['label' => 'Sidebar trên', 'size' => '300x250', 'description' => 'Đầu sidebar'],
                'home_sidebar_middle_1' => ['label' => 'Sidebar giữa 1', 'size' => '300x250', 'description' => 'Trước mục Thẩm định giá'],
                'home_sidebar_middle_2' => ['label' => 'Sidebar giữa 2', 'size' => '300x250', 'description' => 'Trước mục Kinh doanh'],
                'home_sidebar_middle_3' => ['label' => 'Sidebar giữa 3', 'size' => '300x250', 'description' => 'Trước mục Đặc biệt'],
                'home_infeed' => ['label' => 'Trong danh sách', 'size' => '300x250', 'description' => 'Giữa các bài viết mới nhất'],
            ],
            'article' => [
                'article_header_banner' => ['label' => 'Banner header', 'size' => '970x90', 'description' => 'Phía dưới header'],
                'article_before_content' => ['label' => 'Trước nội dung', 'size' => '728x90', 'description' => 'Sau tiêu đề, trước bài viết'],
                'article_in_content' => ['label' => 'Trong nội dung', 'size' => '300x250', 'description' => 'Giữa bài viết (sau đoạn 3-5)'],
                'article_after_content' => ['label' => 'Sau nội dung', 'size' => '728x90', 'description' => 'Sau bài viết, trước tags'],
                'article_sidebar_sticky' => ['label' => 'Sidebar dính', 'size' => '300x600', 'description' => 'Sidebar dính khi cuộn'],
                'article_related_below' => ['label' => 'Dưới bài liên quan', 'size' => '728x90', 'description' => 'Giữa bài liên quan và bình luận'],
            ],
            'category' => [
                'category_header_banner' => ['label' => 'Banner header', 'size' => '970x90', 'description' => 'Phía dưới header'],
                'category_featured_below' => ['label' => 'Dưới nổi bật', 'size' => '970x250', 'description' => 'Giữa nổi bật và danh sách'],
                'category_infeed' => ['label' => 'Trong danh sách', 'size' => '300x250', 'description' => 'Giữa các bài viết'],
                'category_sidebar_top' => ['label' => 'Sidebar trên', 'size' => '300x250', 'description' => 'Đầu sidebar'],
            ],
        ];
    }

    /**
     * Get flattened position options for select field
     */
    public static function getPositionOptions(?string $page = null): array
    {
        $positions = self::getPositions();
        $options = [];

        if ($page && $page !== 'all' && isset($positions[$page])) {
            foreach ($positions[$page] as $key => $info) {
                $options[$key] = $info['label'] . ' (' . $info['size'] . ')';
            }
        } else {
            foreach ($positions as $pageName => $pagePositions) {
                $pageLabel = match($pageName) {
                    'homepage' => 'Trang chủ',
                    'article' => 'Bài viết',
                    'category' => 'Danh mục',
                    default => $pageName,
                };
                foreach ($pagePositions as $key => $info) {
                    $options[$key] = "[{$pageLabel}] " . $info['label'] . ' (' . $info['size'] . ')';
                }
            }
        }

        return $options;
    }

    /**
     * Get position info by key
     */
    public static function getPositionInfo(string $positionKey): ?array
    {
        foreach (self::getPositions() as $pagePositions) {
            if (isset($pagePositions[$positionKey])) {
                return $pagePositions[$positionKey];
            }
        }
        return null;
    }

    /**
     * Scope: only active ads
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: filter by position
     */
    public function scopeForPosition($query, string $position)
    {
        return $query->where('position', $position);
    }

    /**
     * Scope: filter by page (includes 'all' page)
     */
    public function scopeForPage($query, string $page)
    {
        return $query->where(function ($q) use ($page) {
            $q->where('page', $page)
              ->orWhere('page', 'all');
        });
    }

    /**
     * Get the full image URL from S3
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return Storage::disk('s3')->url($this->image_path);
    }

    /**
     * Get display size string
     */
    public function getDisplaySizeAttribute(): string
    {
        if ($this->width && $this->height) {
            return "{$this->width}x{$this->height}";
        }

        $info = self::getPositionInfo($this->position);
        return $info['size'] ?? 'Auto';
    }

    /**
     * Get position label
     */
    public function getPositionLabelAttribute(): string
    {
        $info = self::getPositionInfo($this->position);
        return $info['label'] ?? $this->position;
    }

    /**
     * Get page label
     */
    public function getPageLabelAttribute(): string
    {
        return match($this->page) {
            'homepage' => 'Trang chủ',
            'article' => 'Bài viết',
            'category' => 'Danh mục',
            'all' => 'Tất cả',
            default => $this->page,
        };
    }
}
