<?php

namespace App\Helpers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Str;

class SeoHelper
{
    /**
     * Generate SEO meta tags for a given model
     *
     * @param mixed $model The model instance (Article, Category, etc.)
     * @param array $defaults Default values for meta tags
     * @return array Array of meta information
     */
    public static function generateMeta($model = null, array $defaults = []): array
    {
        // Default values from settings
        $siteName = setting('site_name', config('app.name', 'Nhịp sống thị trường'));
        $defaultTitle = $defaults['title'] ?? setting('meta_title', $siteName);
        $defaultDescription = $defaults['description'] ?? setting('meta_description', 'Tin tức tài chính, kinh doanh, chứng khoán, bất động sản và phân tích thị trường.');
        $defaultImage = $defaults['image'] ?? (setting('og_image') ? \Storage::disk('s3')->url(setting('og_image')) : asset('images/default-og.jpg'));
        $defaultUrl = $defaults['url'] ?? url()->current();

        // If no model is provided, return defaults
        if (!$model) {
            return [
                'title' => $defaultTitle,
                'description' => $defaultDescription,
                'image' => $defaultImage,
                'url' => $defaultUrl,
                'type' => 'website',
            ];
        }

        // Initialize meta data
        $meta = [
            'title' => $defaultTitle,
            'description' => $defaultDescription,
            'image' => $defaultImage,
            'url' => $defaultUrl,
            'type' => 'website',
        ];

        // Handle Article model
        if ($model instanceof Article) {
            $meta['title'] = $model->meta_title ?: $model->title;
            $meta['description'] = $model->meta_description ?: Str::limit(strip_tags($model->summary ?: $model->content), 160);
            $meta['image'] = $model->featured_image ? asset($model->featured_image) : $defaultImage;
            $meta['url'] = route('article.show', $model->slug . '-' . $model->id);
            $meta['type'] = 'article';
            $meta['published_time'] = $model->published_at?->toIso8601String();
            $meta['author'] = $model->author?->name;
            $meta['section'] = $model->category?->name;
        }
        // Handle Category model
        elseif ($model instanceof Category) {
            $meta['title'] = $model->meta_title ?: ($model->name . ' - Tin tức và phân tích');
            $meta['description'] = $model->meta_description ?: Str::limit(strip_tags($model->description ?? ''), 160) ?: "Tin tức và phân tích về {$model->name} - {$siteName}";
            $meta['image'] = $defaultImage;
            $meta['url'] = route('category.show', $model->slug);
            $meta['type'] = 'website';
        }

        // Ensure full title includes site name
        if (!str_contains($meta['title'], $siteName)) {
            $meta['title'] = $meta['title'] . ' | ' . $siteName;
        }

        return $meta;
    }

    /**
     * Generate JSON-LD structured data for an article
     *
     * @param Article $article
     * @return string JSON-LD markup
     */
    public static function generateArticleSchema(Article $article): string
    {
        $siteName = setting('site_name', config('app.name', 'Nhịp sống thị trường'));
        $logoUrl = setting('site_logo') ? \Storage::disk('s3')->url(setting('site_logo')) : asset('images/logo.png');
        $defaultImage = setting('og_image') ? \Storage::disk('s3')->url(setting('og_image')) : asset('images/default-og.jpg');

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $article->title,
            'image' => $article->featured_image ? asset($article->featured_image) : $defaultImage,
            'datePublished' => $article->published_at?->toIso8601String(),
            'dateModified' => $article->updated_at->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $article->author?->name ?? 'Anonymous',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $siteName,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $logoUrl,
                ],
            ],
            'description' => Str::limit(strip_tags($article->summary ?: $article->content), 160),
        ];

        return json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * Generate JSON-LD structured data for breadcrumbs
     *
     * @param array $items Array of breadcrumb items ['name' => 'Title', 'url' => 'https://...']
     * @return string JSON-LD markup
     */
    public static function generateBreadcrumbSchema(array $items): string
    {
        $listItems = [];
        $position = 1;

        foreach ($items as $item) {
            $listItems[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $item['name'],
                'item' => $item['url'],
            ];
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $listItems,
        ];

        return json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
