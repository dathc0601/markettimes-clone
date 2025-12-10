<?php

namespace App\View\Components;

use App\Models\Ad;
use App\Services\AdService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AdSlot extends Component
{
    public ?Ad $ad;

    public function __construct(
        public string $position,
        public string $page = 'all',
        public ?string $class = null,
        public bool $lazy = true
    ) {
        $this->ad = app(AdService::class)->getAd($position, $page);
    }

    public function render(): View|Closure|string
    {
        return view('components.ad-slot');
    }

    public function shouldRender(): bool
    {
        return $this->ad !== null;
    }
}
