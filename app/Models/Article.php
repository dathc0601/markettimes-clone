<?php

namespace App\Models;

use App\Helpers\CacheHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Article extends Model
{
    use HasSlug, SoftDeletes, Searchable;

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'featured_image',
        'author_id',
        'category_id',
        'published_at',
        'view_count',
        'is_featured',
        'is_special_publication',
        'is_published',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'approved_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_special_publication' => 'boolean',
        'is_published' => 'boolean',
        'view_count' => 'integer',
    ];

    /**
     * Boot the model and register event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when article is created or updated
        static::saved(function ($article) {
            CacheHelper::clearAllArticleCaches($article->id, $article->category_id);
        });

        // Clear cache when article is deleted
        static::deleted(function ($article) {
            CacheHelper::clearAllArticleCaches($article->id, $article->category_id);
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'content' => strip_tags($this->content),
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function approvedComments(): HasMany
    {
        return $this->hasMany(Comment::class)->where('is_approved', true);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('status', 'approved')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    /**
     * Get featured image paths (handles both old string format and new JSON array format)
     *
     * @return array|null
     */
    public function getFeaturedImagePathsAttribute()
    {
        $image = $this->attributes['featured_image'] ?? null;

        if (!$image) {
            return null;
        }

        // If it's already an array (JSON stored), return it
        if (is_array($image)) {
            return $image;
        }

        // Try to decode as JSON (new format)
        $decoded = json_decode($image, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Old format (simple string path) - return as legacy format
        return ['original' => $image];
    }

    /**
     * Extract the first image URL from article content
     *
     * @return string|null
     */
    protected function getFirstImageFromContent(string $size = 'thumbnail'): ?string
    {
        if (empty($this->content)) {
            return null;
        }

        // Use regex to find the first <img> tag's src attribute
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $this->content, $matches)) {
            // By default, assume that the matched image URL is a medium-size image
            $mediumImageUrl = $matches[1];

            if (str_contains($mediumImageUrl, '_medium.')) {
                return str_replace('_medium.', '_' . $size . '.', $mediumImageUrl);
            }
        }

        return null;
    }

    /**
     * Get the appropriate image URL for a given size
     *
     * @param string $size thumbnail|medium|large|original
     * @param bool $preferWebP
     * @return string|null
     */
    public function getImageUrl(string $size = 'thumbnail', bool $preferWebP = false): ?string
    {
        $paths = $this->featured_image_paths;

        if (!$paths) {
            return $this->getFirstImageFromContent();
        }

        // Try to get WebP version if preferred
        if ($preferWebP && isset($paths["{$size}_webp"])) {
            return \Storage::disk('s3')->url($paths["{$size}_webp"]);
        }

        // Fall back to regular version
        if (isset($paths[$size])) {
            return \Storage::disk('s3')->url($paths[$size]);
        }

        // Fall back to original
        if (isset($paths['original'])) {
            return \Storage::disk('s3')->url($paths['original']);
        }

        // Fall back to first image in content
        return $this->getFirstImageFromContent();
    }

    /**
     * Get responsive image data for picture element
     *
     * @return array
     */
    public function getResponsiveImageDataAttribute(): array
    {
        $paths = $this->featured_image_paths;

        if (!$paths) {
            return [
                'sources' => [],
                'fallback' => [
                    'src' => asset('images/placeholder.jpg'),
                    'alt' => $this->title,
                ],
            ];
        }

        return [
            'sources' => [
                [
                    'srcset' => $paths['large_webp'] ?? null ? \Storage::disk('s3')->url($paths['large_webp']) : null,
                    'media' => '(min-width: 1024px)',
                    'type' => 'image/webp',
                ],
                [
                    'srcset' => $paths['medium_webp'] ?? null ? \Storage::disk('s3')->url($paths['medium_webp']) : null,
                    'media' => '(min-width: 768px)',
                    'type' => 'image/webp',
                ],
                [
                    'srcset' => $paths['thumbnail_webp'] ?? null ? \Storage::disk('s3')->url($paths['thumbnail_webp']) : null,
                    'type' => 'image/webp',
                ],
            ],
            'fallback' => [
                'src' => $this->getImageUrl('thumbnail'),
                'alt' => $this->title,
            ],
        ];
    }

    /**
     * Get reading time in minutes
     *
     * @return int
     */
    public function getReadingTimeAttribute(): int
    {
        $words = str_word_count(strip_tags($this->content));
        return max(1, ceil($words / 200)); // 200 words per minute
    }
}
