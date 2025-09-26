<?php

namespace App\Filament\Resources\ChinaAddresses\Pages;

use App\Filament\Resources\ChinaAddresses\ChinaAddressResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChinaAddresses extends ListRecords
{
    protected static string $resource = ChinaAddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
