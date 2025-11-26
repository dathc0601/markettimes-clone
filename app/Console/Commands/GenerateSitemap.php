<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sitemap\Tags\News;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:sitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate XML sitemap for search engines';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating sitemap...');

        $sitemap = Sitemap::create();

        // Add homepage
        $sitemap->add(
            Url::create(route('home'))
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(1.0)
        );

        // Add search page
        $sitemap->add(
            Url::create(route('search'))
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.5)
        );

        // Add all active categories
        $this->info('Adding categories...');
        $categories = Category::all();
        foreach ($categories as $category) {
            $sitemap->add(
                Url::create(route('category.show', $category->slug))
                    ->setLastModificationDate($category->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                    ->setPriority(0.8)
            );
        }
        $this->info("Added {$categories->count()} categories");

        // Add all published articles
        $this->info('Adding articles...');
        $articles = Article::published()
            ->with('category')
            ->orderBy('published_at', 'desc')
            ->get();

        foreach ($articles as $article) {
            $priority = $article->is_featured ? 0.9 : 0.6;

            $sitemap->add(
                Url::create(route('article.show', $article->slug . '-' . $article->id))
                    ->setLastModificationDate($article->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority($priority)
            );
        }
        $this->info("Added {$articles->count()} articles");

        // Write sitemap to public directory
        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully at: ' . public_path('sitemap.xml'));
        $this->info('Total URLs: ' . ($categories->count() + $articles->count() + 2));

        // Generate Google News sitemap
        $this->info('');
        $this->info('Generating Google News sitemap...');

        $newsSitemap = Sitemap::create();

        // Get articles published in the last 2 days for Google News
        $recentArticles = Article::published()
            ->where('published_at', '>=', now()->subDays(2))
            ->with('category')
            ->orderBy('published_at', 'desc')
            ->get();

        foreach ($recentArticles as $article) {
            $url = Url::create(route('article.show', $article->slug . '-' . $article->id))
                ->setLastModificationDate($article->updated_at)
                ->addNews(
                    'Market Times',           // Publication name
                    'vi',                     // Language
                    $article->title,          // Article title
                    $article->published_at    // Publication date
                );

            $newsSitemap->add($url);
        }

        $this->info("Added {$recentArticles->count()} recent articles (last 2 days)");

        // Write Google News sitemap to public directory
        $newsSitemap->writeToFile(public_path('google-news-sitemap.xml'));

        $this->info('Google News sitemap generated successfully at: ' . public_path('google-news-sitemap.xml'));

        return Command::SUCCESS;
    }
}
