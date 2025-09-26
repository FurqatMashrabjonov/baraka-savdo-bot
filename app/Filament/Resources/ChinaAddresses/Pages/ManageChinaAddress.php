<?php

namespace App\Filament\Resources\ChinaAddresses\Pages;

use App\Filament\Resources\ChinaAddresses\ChinaAddressResource;
use App\Models\ChinaAddress;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageChinaAddress extends ManageRecords
{
    protected static string $resource = ChinaAddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => ChinaAddress::count() === 0),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        // If there's exactly one record, redirect to edit it
        $address = ChinaAddress::first();
        if ($address) {
            $this->redirect(ChinaAddressResource::getUrl('edit', ['record' => $address]));
        }
    }
}
