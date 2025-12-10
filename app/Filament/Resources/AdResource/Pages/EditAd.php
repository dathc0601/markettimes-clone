<?php

namespace App\Filament\Resources\AdResource\Pages;

use App\Filament\Resources\AdResource;
use App\Services\AdService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAd extends EditRecord
{
    protected static string $resource = AdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('toggle')
                ->label(fn () => $this->record->is_active ? 'Tắt quảng cáo' : 'Kích hoạt')
                ->icon(fn () => $this->record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                ->color(fn () => $this->record->is_active ? 'warning' : 'success')
                ->action(function () {
                    $this->record->update(['is_active' => !$this->record->is_active]);
                    app(AdService::class)->clearPositionCache($this->record->position);

                    $this->refreshFormData(['is_active']);
                }),

            Actions\DeleteAction::make()
                ->label('Xóa')
                ->after(function () {
                    app(AdService::class)->clearPositionCache($this->record->position);
                }),
        ];
    }

    protected function afterSave(): void
    {
        // Clear cache for the ad's position (both old and new if changed)
        app(AdService::class)->clearCache();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
