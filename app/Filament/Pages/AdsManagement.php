<?php

namespace App\Filament\Pages;

use App\Models\Ad;
use App\Services\AdService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class AdsManagement extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static string $view = 'filament.pages.ads-management';

    protected static ?string $navigationLabel = 'Các vị trí quảng cáo';

    protected static ?string $title = 'Quản lý Quảng cáo';

    protected static ?string $navigationGroup = 'Quảng cáo';

    protected static ?int $navigationSort = 0;

    public ?string $selectedPosition = null;
    public ?string $selectedPage = 'all';
    public array $adData = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public function mount(): void
    {
        $this->loadAds();
    }

    public function loadAds(): void
    {
        $this->adData = [];

        $ads = Ad::orderBy('position')
            ->orderByDesc('priority')
            ->get()
            ->groupBy('position');

        foreach ($ads as $position => $positionAds) {
            $this->adData[$position] = $positionAds->map(function ($ad) {
                return [
                    'id' => $ad->id,
                    'name' => $ad->name,
                    'type' => $ad->type,
                    'is_active' => $ad->is_active,
                    'priority' => $ad->priority,
                    'image_url' => $ad->image_url,
                    'page' => $ad->page,
                ];
            })->toArray();
        }
    }

    public function getPositionsProperty(): array
    {
        return Ad::getPositions();
    }

    public function getAdsCountProperty(): int
    {
        return Ad::count();
    }

    public function getActiveAdsCountProperty(): int
    {
        return Ad::where('is_active', true)->count();
    }

    public function selectPosition(string $position): void
    {
        $this->selectedPosition = $position;
    }

    public function toggleAd(int $adId): void
    {
        $ad = Ad::find($adId);
        if ($ad) {
            $ad->update(['is_active' => !$ad->is_active]);
            app(AdService::class)->clearPositionCache($ad->position);

            Notification::make()
                ->title($ad->is_active ? 'Quảng cáo đã được kích hoạt' : 'Quảng cáo đã được tắt')
                ->success()
                ->send();

            $this->loadAds();
        }
    }

    public function deleteAd(int $adId): void
    {
        $ad = Ad::find($adId);
        if ($ad) {
            $position = $ad->position;
            $ad->delete();
            app(AdService::class)->clearPositionCache($position);

            Notification::make()
                ->title('Đã xóa quảng cáo')
                ->success()
                ->send();

            $this->loadAds();
        }
    }

    public function clearAllCache(): void
    {
        app(AdService::class)->clearCache();

        Notification::make()
            ->title('Đã xóa toàn bộ cache quảng cáo')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clear_cache')
                ->label('Xóa cache')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn () => $this->clearAllCache()),

            Action::make('create_ad')
                ->label('Thêm quảng cáo')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(route('filament.admin.resources.ads.create')),
        ];
    }
}
