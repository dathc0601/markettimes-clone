<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;

class GenerateSitemap extends Command
{
    protected $signature = 'generate:sitemap';

    protected $description = 'Generate XML sitemaps for search engines (sitemap index structure)';

    private const PUBLICATION_NAME = 'X-Investor';
    private const LANGUAGE = 'vi';
    private const MONTHS_HISTORY = 6;
    private const TIMEZONE = '+07:00';
    private const GOOGLE_NEWS_LIMIT = 200;
    private const LATEST_NEWS_DAYS = 30;
    private const PRIORITY_HOMEPAGE = 1.0;
    private const PRIORITY_FEATURED = 0.9;
    private const PRIORITY_ARTICLE = 0.6;
    private const PRIORITY_SEARCH = 0.5;
    private const PRIORITY_CATEGORY = 0.4;

    /** @var array<int, array{filename: string, lastmod: Carbon, year: int, month: int, startDay: int}> */
    private array $dateRangeSitemapInfo = [];

    public function handle(): int
    {
        $this->info('Starting sitemap generation...');
        $this->newLine();

        $this->ensureSitemapsDirectoryExists();
        $this->cleanupOldSitemaps();

        $this->generateCategoriesSitemap();
        $this->generateGoogleNewsSitemap();
        $this->generateLatestNewsSitemap();
        $this->generateDateRangeSitemaps();
        $this->generateSitemapIndex();

        $this->newLine();
        $this->info('All sitemaps generated successfully!');

        return Command::SUCCESS;
    }

    private function ensureSitemapsDirectoryExists(): void
    {
        $path = public_path('sitemaps');
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
            $this->info('Created sitemaps directory');
        }
    }

    private function cleanupOldSitemaps(): void
    {
        $this->info('Cleaning up old sitemaps...');

        $cutoffDate = now()->subMonths(self::MONTHS_HISTORY)->startOfMonth();
        $files = File::glob(public_path('sitemaps/sitemaps-*.xml'));

        $deleted = 0;
        foreach ($files as $file) {
            $filename = basename($file);
            // Extract year and month from filename: sitemaps-YYYY-MM-D1-D2.xml
            if (preg_match('/sitemaps-(\d{4})-(\d{1,2})-\d+-\d+\.xml/', $filename, $matches)) {
                $fileDate = Carbon::createFromDate($matches[1], $matches[2], 1);
                if ($fileDate < $cutoffDate) {
                    File::delete($file);
                    $deleted++;
                }
            }
        }

        if ($deleted > 0) {
            $this->info("Deleted {$deleted} old sitemap files");
        }
    }

    private function generateCategoriesSitemap(): void
    {
        $this->info('Generating categories sitemap...');

        $categories = Category::active()->ordered()->get();

        $xml = $this->buildUrlsetHeader();

        foreach ($categories as $category) {
            $xml .= $this->buildUrlEntry(
                route('category.show', $category->slug),
                self::PRIORITY_CATEGORY,
                now(),
                'monthly'
            );
        }

        $xml .= '</urlset>';

        File::put(public_path('sitemaps/categories-sitemap.xml'), $xml);
        $this->info("Added {$categories->count()} categories");
    }

    private function generateGoogleNewsSitemap(): void
    {
        $this->info('Generating Google News sitemap...');

        $articles = Article::published()
            ->with(['tags'])
            ->orderBy('published_at', 'desc')
            ->limit(self::GOOGLE_NEWS_LIMIT)
            ->get();

        $xml = $this->buildGoogleNewsHeader();

        foreach ($articles as $article) {
            $xml .= $this->buildGoogleNewsEntry($article);
        }

        $xml .= '</urlset>';

        File::put(public_path('google-news-sitemap.xml'), $xml);
        $this->info("Added {$articles->count()} articles to Google News sitemap");
    }

    private function generateLatestNewsSitemap(): void
    {
        $this->info('Generating latest news sitemap...');

        $xml = $this->buildUrlsetHeader();

        // Add homepage
        $xml .= $this->buildUrlEntry(
            route('home'),
            self::PRIORITY_HOMEPAGE,
            now(),
            'daily'
        );

        // Add search page
        $xml .= $this->buildUrlEntry(
            route('search'),
            self::PRIORITY_SEARCH,
            now(),
            'weekly'
        );

        // Add recent articles
        $articles = Article::published()
            ->where('published_at', '>=', now()->subDays(self::LATEST_NEWS_DAYS))
            ->orderBy('published_at', 'desc')
            ->get();

        foreach ($articles as $article) {
            $priority = $article->is_featured ? self::PRIORITY_FEATURED : self::PRIORITY_ARTICLE;
            $xml .= $this->buildUrlEntryWithImage(
                route('article.show', $article->slug . '-' . $article->id),
                $priority,
                $article->updated_at,
                'daily',
                $article
            );
        }

        $xml .= '</urlset>';

        File::put(public_path('latest-news-sitemap.xml'), $xml);
        $this->info("Added " . ($articles->count() + 2) . " URLs to latest news sitemap");
    }

    private function generateDateRangeSitemaps(): void
    {
        $this->info('Generating date-range sitemaps...');

        $startDate = now()->subMonths(self::MONTHS_HISTORY)->startOfMonth();
        $endDate = now();
        $current = $startDate->copy();

        $totalSitemaps = 0;

        while ($current <= $endDate) {
            $ranges = $this->getMonthDateRanges($current);

            foreach ($ranges as $range) {
                // Skip future date ranges
                if ($range['start'] > now()) {
                    continue;
                }

                $articles = $this->getArticlesForDateRange($range['start'], $range['end']);

                if ($articles->isNotEmpty()) {
                    $filename = sprintf(
                        'sitemaps-%d-%d-%d-%d.xml',
                        $range['start']->year,
                        $range['start']->month,
                        $range['start']->day,
                        $range['end']->day
                    );

                    $this->generateDateRangeSitemap($articles, $filename);

                    $lastmod = $this->getLastmodForRange($articles, $range);

                    $this->dateRangeSitemapInfo[] = [
                        'filename' => $filename,
                        'lastmod' => $lastmod,
                        'year' => $range['start']->year,
                        'month' => $range['start']->month,
                        'startDay' => $range['start']->day,
                    ];

                    $totalSitemaps++;
                }
            }

            $current->addMonth();
        }

        $this->info("Generated {$totalSitemaps} date-range sitemaps");
    }

    private function getMonthDateRanges(Carbon $month): array
    {
        $daysInMonth = $month->daysInMonth;
        $ranges = [];

        // 1-5, 6-10, 11-15, 16-20, 21-25, 26-end
        $chunks = [
            [1, 5],
            [6, 10],
            [11, 15],
            [16, 20],
            [21, 25],
            [26, $daysInMonth],
        ];

        foreach ($chunks as [$start, $end]) {
            if ($start <= $daysInMonth) {
                $ranges[] = [
                    'start' => $month->copy()->day($start)->startOfDay(),
                    'end' => $month->copy()->day(min($end, $daysInMonth))->endOfDay(),
                ];
            }
        }

        return $ranges;
    }

    private function getArticlesForDateRange(Carbon $start, Carbon $end): Collection
    {
        return Article::published()
            ->whereBetween('published_at', [$start, $end])
            ->with(['tags'])
            ->orderBy('published_at', 'desc')
            ->get();
    }

    private function generateDateRangeSitemap(Collection $articles, string $filename): void
    {
        $xml = $this->buildUrlsetHeader();

        foreach ($articles as $article) {
            $xml .= $this->buildUrlEntryWithImage(
                route('article.show', $article->slug . '-' . $article->id),
                1,
                $article->updated_at,
                'daily',
                $article
            );
        }

        $xml .= '</urlset>';

        File::put(public_path('sitemaps/' . $filename), $xml);
    }

    private function getLastmodForRange(Collection $articles, array $range): Carbon
    {
        // Use the latest article update time if available
        $latestUpdate = $articles->max('updated_at');

        if ($latestUpdate) {
            return Carbon::parse($latestUpdate);
        }

        // If the range is in the past, use the end of that range
        if ($range['end'] < now()) {
            return $range['end'];
        }

        // For current/recent ranges, use now
        return now();
    }

    private function generateSitemapIndex(): void
    {
        $this->info('Generating sitemap index...');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
        $xml .= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
        $xml .= 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd">' . "\n";

        // Categories sitemap
        $xml .= $this->buildSitemapEntry(
            url('/sitemaps/categories-sitemap.xml'),
            now()
        );

        // Google News sitemap
        $xml .= $this->buildSitemapEntry(
            url('/google-news-sitemap.xml'),
            now()
        );

        // Latest News sitemap
        $xml .= $this->buildSitemapEntry(
            url('/latest-news-sitemap.xml'),
            now()
        );

        // Date-range sitemaps (sorted by date, most recent first)
        $sortedSitemaps = collect($this->dateRangeSitemapInfo)
            ->sortByDesc(function ($item) {
                return sprintf('%04d%02d%02d', $item['year'], $item['month'], $item['startDay']);
            })
            ->values();

        foreach ($sortedSitemaps as $info) {
            $xml .= $this->buildSitemapEntry(
                url('/sitemaps/' . $info['filename']),
                $info['lastmod']
            );
        }

        $xml .= '</sitemapindex>';

        File::put(public_path('sitemap.xml'), $xml);

        $totalSitemaps = 3 + count($this->dateRangeSitemapInfo);
        $this->info("Sitemap index created with {$totalSitemaps} sitemaps");
    }

    // ===================
    // XML Builder Helpers
    // ===================

    private function buildUrlsetHeader(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ' .
            'xmlns:xhtml="http://www.w3.org/1999/xhtml" ' .
            'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" ' .
            'xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">' . "\n";
    }

    private function buildGoogleNewsHeader(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ' .
            'xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" ' .
            'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
    }

    private function buildUrlEntry(string $loc, float $priority, Carbon $lastmod, string $changefreq): string
    {
        $xml = "<url>\n";
        $xml .= "<loc>" . htmlspecialchars($loc, ENT_XML1, 'UTF-8') . "</loc>\n";
        $xml .= "<priority>{$priority}</priority>\n";
        $xml .= "<lastmod>" . $this->formatDate($lastmod) . "</lastmod>\n";
        $xml .= "<changefreq>{$changefreq}</changefreq>\n";
        $xml .= "</url>\n";

        return $xml;
    }

    private function buildUrlEntryWithImage(string $loc, float $priority, Carbon $lastmod, string $changefreq, Article $article): string
    {
        $xml = "<url>\n";
        $xml .= "<loc>" . htmlspecialchars($loc, ENT_XML1, 'UTF-8') . "</loc>\n";
        $xml .= "<priority>{$priority}</priority>\n";
        $xml .= "<lastmod>" . $this->formatDate($lastmod) . "</lastmod>\n";
        $xml .= "<changefreq>{$changefreq}</changefreq>\n";

        // Add image if available
        $imageUrl = $article->getImageUrl('large');
        if ($imageUrl) {
            $xml .= "<image:image>\n";
            $xml .= "<image:loc>" . htmlspecialchars($imageUrl, ENT_XML1, 'UTF-8') . "</image:loc>\n";
            $xml .= "<image:title><![CDATA[" . $this->escapeCdata($article->title) . "]]></image:title>\n";
            $xml .= "<image:caption><![CDATA[" . $this->escapeCdata($article->title) . "]]></image:caption>\n";
            $xml .= "</image:image>\n";
        }

        $xml .= "</url>\n";

        return $xml;
    }

    private function buildGoogleNewsEntry(Article $article): string
    {
        $url = route('article.show', $article->slug . '-' . $article->id);
        $keywords = $article->tags->pluck('name')->implode(';');

        $xml = "<url>\n";
        $xml .= "<loc>" . htmlspecialchars($url, ENT_XML1, 'UTF-8') . "</loc>\n";

        // News element
        $xml .= "<news:news>\n";
        $xml .= "<news:publication>\n";
        $xml .= "<news:name>" . self::PUBLICATION_NAME . "</news:name>\n";
        $xml .= "<news:language>" . self::LANGUAGE . "</news:language>\n";
        $xml .= "</news:publication>\n";
        $xml .= "<news:publication_date>" . $this->formatDate($article->published_at) . "</news:publication_date>\n";
        $xml .= "<news:title>" . htmlspecialchars($article->title, ENT_XML1, 'UTF-8') . "</news:title>\n";
        $xml .= "<news:access>Subscription</news:access>\n";

        if ($keywords) {
            $xml .= "<news:keywords>" . htmlspecialchars($keywords, ENT_XML1, 'UTF-8') . "</news:keywords>\n";
        }

        $xml .= "<news:genres>PressRelease</news:genres>\n";
        $xml .= "<news:stock_tickers>NASDAQ:A,NASDAQ:B</news:stock_tickers>\n";
        $xml .= "</news:news>\n";

        // Image element
        $imageUrl = $article->getImageUrl('large');
        if ($imageUrl) {
            $xml .= "<image:image>\n";
            $xml .= "<image:loc>" . htmlspecialchars($imageUrl, ENT_XML1, 'UTF-8') . "</image:loc>\n";
            $xml .= "</image:image>\n";
        }

        $xml .= "</url>\n";

        return $xml;
    }

    private function buildSitemapEntry(string $loc, Carbon $lastmod): string
    {
        $xml = "<sitemap>\n";
        $xml .= "<loc>" . htmlspecialchars($loc, ENT_XML1, 'UTF-8') . "</loc>\n";
        $xml .= "<lastmod>" . $this->formatDate($lastmod) . "</lastmod>\n";
        $xml .= "</sitemap>\n";

        return $xml;
    }

    private function formatDate(Carbon $date): string
    {
        return $date->format('Y-m-d\TH:i:s') . self::TIMEZONE;
    }

    private function escapeCdata(string $content): string
    {
        return str_replace(']]>', ']]]]><![CDATA[>', $content);
    }
}
