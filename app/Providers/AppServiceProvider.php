<?php

namespace App\Providers;

use App\Models\Ad;
use App\Models\NavigationItem;
use App\Observers\AdObserver;
use App\Observers\NavigationItemObserver;
use App\Services\AdService;
use App\Services\HomepageConfigService;
use App\Services\NavigationService;
use App\Services\SettingsService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(HomepageConfigService::class, function ($app) {
            return new HomepageConfigService($app->make(SettingsService::class));
        });

        $this->app->singleton(AdService::class, function ($app) {
            return new AdService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            \URL::forceScheme('https');
        }

        \Carbon\Carbon::setLocale('vi');

        // Register NavigationItem observer for automatic cache clearing
        NavigationItem::observe(NavigationItemObserver::class);

        // Register Ad observer for automatic cache clearing
        Ad::observe(AdObserver::class);

        // Share navigation items with navigation partial
        View::composer('partials.navigation', function ($view) {
            $navigationService = app(NavigationService::class);
            $view->with('navigationItems', $navigationService->getMenuTree());

            // Detect and share current category for active state highlighting
            $currentCategory = null;

            // Check if we're on a category page
            if (request()->route() && request()->route()->getName() === 'category.show') {
                $category = request()->route('category');
                // Only set if it's a Category model (not a raw string from failed route binding)
                if ($category instanceof \App\Models\Category) {
                    $currentCategory = $category;
                }
            }

            // Check if we're on an article page
            if (request()->route() && request()->route()->getName() === 'article.show') {
                $article = request()->route('article');
                // Only set if it's an Article model with a valid category
                if ($article instanceof \App\Models\Article && $article->category) {
                    $currentCategory = $article->category;
                }
            }

            $view->with('currentCategory', $currentCategory);
        });
    }
}
