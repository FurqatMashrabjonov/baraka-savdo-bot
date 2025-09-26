<?php

namespace App\Filament\Resources\PricingSettings\Pages;

use App\Filament\Resources\PricingSettings\PricingSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPricingSettings extends ListRecords
{
    protected static string $resource = PricingSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
