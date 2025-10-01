<?php

namespace App\Filament\Resources\Parcels\Pages;

use App\Filament\Resources\Parcels\ParcelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListParcels extends ListRecords
{
    protected static string $resource = ParcelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make(__('filament.all_parcels'))
                ->query(function ($query) {
                    return $query->orderBy('created_at', 'desc'); // Show all parcels, latest first
                }),
                
            'three_days' => Tab::make(__('filament.three_day_filter'))
                ->query(function ($query) {
                    return $query
                        ->whereNull('china_uploaded_at') // Not imported from China Excel
                        ->whereNotNull('client_id') // Client has added track number
                        ->where('created_at', '>=', now()->subDays(3)) // Within last 3 days
                        ->orderBy('created_at', 'desc'); // Latest first
                }),
            
            'china_uploaded' => Tab::make(__('filament.uploaded_in_china'))
                ->query(function ($query) {
                    return $query
                        ->whereNotNull('china_uploaded_at') // Uploaded in China
                        ->whereNull('uzb_uploaded_at') // NOT imported in UZB
                        ->orderBy('china_uploaded_at', 'desc'); // Latest uploads first
                }),
            
            'uzb_uploaded' => Tab::make(__('filament.uploaded_in_uzb'))
                ->query(function ($query) {
                    return $query
                        ->whereNotNull('uzb_uploaded_at') // Uploaded in Uzbekistan
                        ->orderBy('uzb_uploaded_at', 'desc'); // Latest uploads first
                }),
        ];
    }
}
