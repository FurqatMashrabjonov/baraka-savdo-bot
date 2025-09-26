<?php

namespace App\Filament\Resources\PricingSettings\Pages;

use App\Filament\Resources\PricingSettings\PricingSettingResource;
use App\Models\PricingSetting;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPricingSetting extends EditRecord
{
    protected static string $resource = PricingSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        // If this pricing setting is being set as active, deactivate all others
        if ($data['is_active'] ?? false) {
            PricingSetting::where('is_active', true)
                ->where('id', '!=', $this->record->id)
                ->update(['is_active' => false]);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
