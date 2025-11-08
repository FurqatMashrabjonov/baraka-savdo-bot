<?php

namespace App\Filament\Resources\PrepaymentParcels\Pages;

use App\Filament\Resources\PrepaymentParcels\PrepaymentParcelResource;
use Filament\Resources\Pages\ListRecords;

class ListPrepaymentParcels extends ListRecords
{
    protected static string $resource = PrepaymentParcelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
