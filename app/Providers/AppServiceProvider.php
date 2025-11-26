<?php

namespace App\Providers;

use App\Models\NavigationItem;
use App\Observers\NavigationItemObserver;
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

        // Share navigation items with navigation partial
        View::composer('partials.navigation', function ($view) {
            $navigationService = app(NavigationService::class);
            $view->with('navigationItems', $navigationService->getMenuTree());

            // Detect and share current category for active state highlighting
            $currentCategory = null;

            // Check if we're on a category page
            if (request()->route() && request()->route()->getName() === 'category.show') {
                $currentCategory = request()->route('category');
            }

            // Check if we're on an article page
            if (request()->route() && request()->route()->getName() === 'article.show') {
                $article = request()->route('article');
                if ($article && $article->category) {
                    $currentCategory = $article->category;
                }
            }

            $view->with('currentCategory', $currentCategory);
        });
    }
}
