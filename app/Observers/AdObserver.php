<?php

namespace App\Observers;

use App\Models\Ad;
use App\Services\AdService;

class AdObserver
{
    protected AdService $adService;

    public function __construct(AdService $adService)
    {
        $this->adService = $adService;
    }

    /**
     * Handle the Ad "created" event.
     */
    public function created(Ad $ad): void
    {
        $this->adService->clearPositionCache($ad->position);
    }

    /**
     * Handle the Ad "updated" event.
     */
    public function updated(Ad $ad): void
    {
        // Clear cache for both old and new positions if position changed
        if ($ad->isDirty('position')) {
            $this->adService->clearPositionCache($ad->getOriginal('position'));
        }
        $this->adService->clearPositionCache($ad->position);
    }

    /**
     * Handle the Ad "deleted" event.
     */
    public function deleted(Ad $ad): void
    {
        $this->adService->clearPositionCache($ad->position);
    }
}
