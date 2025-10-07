<?php

namespace App\Filament\Widgets;

use App\Models\Parcel;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ThreeDayParcelsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        // Count parcels older than 3 days that match the tab criteria
        $threeDayOldParcels = Parcel::whereNull('china_uploaded_at') // Not imported from China Excel
            ->whereNotNull('client_id') // Client has added track number
            ->where('created_at', '>=', now()->subDays(3)) // Within last 3 days
            ->count();

        return [
            Stat::make(__('filament.three_day_filter'), $threeDayOldParcels)
                ->description(__('filament.three_day_parcels_description'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
