<?php

namespace App\Filament\Resources\AdResource\Pages;

use App\Filament\Resources\AdResource;
use App\Models\Ad;
use App\Services\AdService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListAds extends ListRecords
{
    protected static string $resource = AdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('clear_cache')
                ->label('Xóa cache')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    app(AdService::class)->clearCache();

                    Notification::make()
                        ->title('Đã xóa cache quảng cáo')
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make()
                ->label('Thêm quảng cáo'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Can add stats widgets here if needed
        ];
    }
}
