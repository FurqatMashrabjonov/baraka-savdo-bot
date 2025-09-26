<?php

namespace App\Filament\Resources\PricingSettings\Pages;

use App\Filament\Resources\PricingSettings\PricingSettingResource;
use App\Models\PricingSetting;
use Filament\Resources\Pages\CreateRecord;

class CreatePricingSetting extends CreateRecord
{
    protected static string $resource = PricingSettingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['updated_by'] = auth()->id();

        // If this pricing setting is being set as active, deactivate all others
        if ($data['is_active'] ?? false) {
            PricingSetting::where('is_active', true)->update(['is_active' => false]);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
