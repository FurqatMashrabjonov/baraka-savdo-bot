<?php

namespace App\Filament\Resources\ChinaAddresses\Pages;

use App\Filament\Resources\ChinaAddresses\ChinaAddressResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChinaAddress extends EditRecord
{
    protected static string $resource = ChinaAddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
