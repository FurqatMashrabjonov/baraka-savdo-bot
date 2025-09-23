<?php

namespace App\Filament\Widgets;

use App\Enums\ParcelStatus;
use App\Models\Client;
use App\Models\Parcel;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ParcelStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Essential parcel metrics
        $totalParcels = Parcel::count();
        $createdParcels = Parcel::where('status', ParcelStatus::CREATED)->count();
        $arrivedChinaParcels = Parcel::where('status', ParcelStatus::ARRIVED_CHINA)->count();
        $arrivedUzbParcels = Parcel::where('status', ParcelStatus::ARRIVED_UZB)->count();
        $deliveredParcels = Parcel::where('status', ParcelStatus::DELIVERED)->count();

        // Essential client metrics
        $totalClients = Client::count();
        $activeClients = Client::whereHas('parcels')->count();

        // Key performance indicators
        $deliveryRate = $totalParcels > 0 ? round(($deliveredParcels / $totalParcels) * 100, 1) : 0;
        $bannedParcels = Parcel::where('is_banned', true)->count();

        // Recent activity (7 days)
        $recentParcels = Parcel::where('created_at', '>=', now()->subDays(7))->count();

        return [
            Stat::make(__('filament.total_parcels'), $totalParcels)
                ->description($recentParcels > 0 ? "+{$recentParcels} ".__('filament.last_7_days') : __('filament.no_new_parcels'))
                ->descriptionIcon($recentParcels > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-minus')
                ->descriptionColor($recentParcels > 0 ? 'success' : 'gray')
                ->icon('heroicon-o-cube')
                ->color('primary'),

            Stat::make(__('filament.status_created'), $createdParcels)
                ->description(__('filament.new_parcels'))
                ->icon('heroicon-o-plus-circle')
                ->color('gray'),

            Stat::make(__('filament.status_arrived_china'), $arrivedChinaParcels)
                ->description(__('filament.in_china'))
                ->icon('heroicon-o-globe-asia-australia')
                ->color('info'),

            Stat::make(__('filament.status_arrived_uzb'), $arrivedUzbParcels)
                ->description(__('filament.in_uzbekistan'))
                ->icon('heroicon-o-building-office')
                ->color('warning'),

            Stat::make(__('filament.status_delivered'), $deliveredParcels)
                ->description("{$deliveryRate}% ".__('filament.delivery_rate'))
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make(__('filament.total_clients'), $totalClients)
                ->description("{$activeClients} ".__('filament.active_clients'))
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make(__('filament.banned_parcels'), $bannedParcels)
                ->description(__('filament.restricted_items'))
                ->icon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
