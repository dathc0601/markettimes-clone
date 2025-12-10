<?php

namespace App\Services;

use App\Models\Ad;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AdService
{
    /**
     * Cache duration in seconds (5 minutes)
     */
    protected int $cacheDuration = 300;

    /**
     * Cache key prefix
     */
    protected string $cachePrefix = 'ad.';

    /**
     * Get a single ad for a position with weighted rotation
     */
    public function getAd(string $position, string $page = 'all'): ?Ad
    {
        $ads = $this->getAdsForPosition($position, $page);

        if ($ads->isEmpty()) {
            return null;
        }

        // If only one ad, return it
        if ($ads->count() === 1) {
            return $ads->first();
        }

        // Weighted random selection based on priority
        return $this->selectByPriority($ads);
    }

    /**
     * Get all active ads for a specific position
     */
    public function getAdsForPosition(string $position, string $page = 'all'): Collection
    {
        $cacheKey = $this->cachePrefix . "{$position}.{$page}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($position, $page) {
            return Ad::active()
                ->forPosition($position)
                ->forPage($page)
                ->orderByDesc('priority')
                ->get();
        });
    }

    /**
     * Select an ad using weighted random selection based on priority
     * Higher priority = higher chance of being selected
     */
    protected function selectByPriority(Collection $ads): Ad
    {
        // Calculate total weight (priority + 1 to avoid zero weights)
        $totalWeight = $ads->sum(fn($ad) => $ad->priority + 1);

        // Generate random number
        $random = mt_rand(1, $totalWeight);

        // Find the ad that falls within the random range
        $cumulative = 0;
        foreach ($ads as $ad) {
            $cumulative += $ad->priority + 1;
            if ($random <= $cumulative) {
                return $ad;
            }
        }

        // Fallback to first ad
        return $ads->first();
    }

    /**
     * Get all ads grouped by position
     */
    public function getAllGroupedByPosition(): array
    {
        $cacheKey = $this->cachePrefix . 'grouped';

        return Cache::remember($cacheKey, $this->cacheDuration, function () {
            return Ad::active()
                ->orderByDesc('priority')
                ->get()
                ->groupBy('position')
                ->toArray();
        });
    }

    /**
     * Check if there are any ads for a position
     */
    public function hasAdsForPosition(string $position, string $page = 'all'): bool
    {
        return $this->getAdsForPosition($position, $page)->isNotEmpty();
    }

    /**
     * Clear all ad-related cache
     */
    public function clearCache(): void
    {
        // Get all positions and clear their caches
        $positions = Ad::getPositions();

        foreach ($positions as $pagePositions) {
            foreach (array_keys($pagePositions) as $position) {
                foreach (['all', 'homepage', 'article', 'category'] as $page) {
                    Cache::forget($this->cachePrefix . "{$position}.{$page}");
                }
            }
        }

        Cache::forget($this->cachePrefix . 'grouped');
    }

    /**
     * Clear cache for a specific position
     */
    public function clearPositionCache(string $position): void
    {
        foreach (['all', 'homepage', 'article', 'category'] as $page) {
            Cache::forget($this->cachePrefix . "{$position}.{$page}");
        }
        Cache::forget($this->cachePrefix . 'grouped');
    }
}
