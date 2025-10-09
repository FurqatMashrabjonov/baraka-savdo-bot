<?php

namespace App\Filament\Resources\ThreeDayParcels\Pages;

use App\Filament\Resources\ThreeDayParcels\ThreeDayParcelsResource;
use Filament\Resources\Pages\ListRecords;

class ListThreeDayParcels extends ListRecords
{
    protected static string $resource = ThreeDayParcelsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action - this is a filtered view only
        ];
    }
}
