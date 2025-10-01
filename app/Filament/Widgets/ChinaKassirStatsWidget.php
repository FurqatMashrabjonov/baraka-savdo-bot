<?php

namespace App\Filament\Widgets;

use App\Models\Parcel;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ChinaKassirStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Carbon::today();

        // 1. Today's number of parcels
        $todayParcelsCount = Parcel::whereDate('created_at', $today)->count();

        // 2. Today's total weight of parcels
        $todayTotalWeight = Parcel::whereDate('created_at', $today)->sum('weight') ?: 0;

        // 3. Total number of parcels (all time)
        $totalParcelsCount = Parcel::count();

        // 4. Total weight of all parcels (all time)
        $totalWeight = Parcel::sum('weight') ?: 0;

        return [
            Stat::make(__('filament.todays_parcels'), $todayParcelsCount)
                ->description(__('filament.parcels_received_today'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make(__('filament.todays_weight'), number_format($todayTotalWeight, 2).' kg')
                ->description(__('filament.total_weight_today'))
                ->descriptionIcon('heroicon-m-scale')
                ->color('success')
                ->chart([5, 8, 12, 6, 18, 9, 22]),

            Stat::make(__('filament.total_parcels'), $totalParcelsCount)
                ->description(__('filament.all_time_parcels'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('warning')
                ->chart([15, 25, 35, 28, 45, 32, 58]),

            Stat::make(__('filament.total_weight'), number_format($totalWeight, 2).' kg')
                ->description(__('filament.all_time_weight'))
                ->descriptionIcon('heroicon-m-scale')
                ->color('info')
                ->chart([20, 35, 50, 45, 65, 55, 78]),
        ];
    }
}
