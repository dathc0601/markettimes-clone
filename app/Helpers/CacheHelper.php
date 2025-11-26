<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    /**
     * Clear article-related caches
     *
     * @param int $articleId
     * @return void
     */
    public static function clearArticleCache(int $articleId): void
    {
        // Clear article cache
        Cache::forget("article.{$articleId}");

        // Clear related articles cache
        Cache::forget("article.{$articleId}.related");
    }

    /**
     * Clear category-related caches
     *
     * @param int $categoryId
     * @return void
     */
    public static function clearCategoryCache(int $categoryId): void
    {
        // Clear featured article cache
        Cache::forget("category.{$categoryId}.featured");

        // Clear paginated article caches (clear first 10 pages)
        for ($page = 1; $page <= 10; $page++) {
            Cache::forget("category.{$categoryId}.articles.page.{$page}");
        }
    }

    /**
     * Clear homepage caches
     *
     * @return void
     */
    public static function clearHomepageCache(): void
    {
        // Clear legacy cache keys
        Cache::forget('homepage.categories');
        Cache::forget('homepage.featured_category_articles');
        Cache::forget('homepage.featured_articles');
        Cache::forget('homepage.latest_articles');
        Cache::forget('homepage.most_read');
        Cache::forget('homepage.mostRead');

        // Clear new section-based cache keys
        self::clearHomepageSectionCaches();
    }

    /**
     * Clear homepage section caches (new configurable sections)
     *
     * @return void
     */
    public static function clearHomepageSectionCaches(): void
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

        // Clear homepage config cache
        Cache::forget('setting.homepage_config');
    }

    /**
     * Clear most read articles cache
     *
     * @return void
     */
    public static function clearMostReadCache(): void
    {
        Cache::forget('articles.most_read');
    }

    /**
     * Clear all article-related caches (useful when articles are updated)
     *
     * @param int|null $articleId
     * @param int|null $categoryId
     * @return void
     */
    public static function clearAllArticleCaches(?int $articleId = null, ?int $categoryId = null): void
    {
        if ($articleId) {
            self::clearArticleCache($articleId);
        }

        if ($categoryId) {
            self::clearCategoryCache($categoryId);
        }

        self::clearHomepageCache();
        self::clearMostReadCache();
    }

    /**
     * Clear all caches in the application
     *
     * @return void
     */
    public static function clearAllCaches(): void
    {
        Cache::flush();
    }
}
