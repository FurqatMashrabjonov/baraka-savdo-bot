<?php

namespace App\Filament\Resources\PricingSettings\Pages;

use App\Filament\Resources\PricingSettings\PricingSettingResource;
use App\Models\PricingSetting;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePricingSetting extends ManageRecords
{
    protected static string $resource = PricingSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => PricingSetting::count() === 0),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        // If there's exactly one record, redirect to edit it
        $setting = PricingSetting::first();
        if ($setting) {
            $this->redirect(PricingSettingResource::getUrl('edit', ['record' => $setting]));
        }
    }
}
