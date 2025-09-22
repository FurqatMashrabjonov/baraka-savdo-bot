<?php

namespace App\Filament\Widgets;

use App\Enums\ParcelStatus;
use App\Models\Parcel;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ParcelStatsWidget extends BaseWidget
{
    protected ?string $heading = 'Pochta Statistikasi';
    protected function getStats(): array
    {
        $totalParcels = Parcel::count();
        $createdParcels = Parcel::where('status', ParcelStatus::CREATED)->count();
        $arrivedChinaParcels = Parcel::where('status', ParcelStatus::ARRIVED_CHINA)->count();
        $arrivedUzbParcels = Parcel::where('status', ParcelStatus::ARRIVED_UZB)->count();
        $deliveredParcels = Parcel::where('status', ParcelStatus::DELIVERED)->count();
        $bannedParcels = Parcel::where('is_banned', true)->count();
        $unassignedParcels = Parcel::whereNull('client_id')->count();

        // Get recent trends (last 30 days vs previous 30 days)
        $recentDelivered = Parcel::where('status', ParcelStatus::DELIVERED)
            ->where('updated_at', '>=', now()->subDays(30))
            ->count();
        $previousDelivered = Parcel::where('status', ParcelStatus::DELIVERED)
            ->whereBetween('updated_at', [now()->subDays(60), now()->subDays(30)])
            ->count();
        $deliveredTrend = $previousDelivered > 0 ?
            round((($recentDelivered - $previousDelivered) / $previousDelivered) * 100, 1) : 0;

        $totalWeight = Parcel::whereNotNull('weight')->sum('weight');

        return [
            Stat::make('Jami posilkalar', $totalParcels)
                ->description('Barcha posilkalar soni')
                ->icon('heroicon-o-cube')
                ->color('primary'),

            Stat::make('Yaratilgan', $createdParcels)
                ->description('Yangi yaratilgan posilkalar')
                ->icon('heroicon-o-plus-circle')
                ->color('gray'),

            Stat::make('Xitoyga kelgan', $arrivedChinaParcels)
                ->description('Xitoyda joylashgan posilkalar')
                ->icon('heroicon-o-globe-asia-australia')
                ->color('info'),

            Stat::make('O\'zbekistonga kelgan', $arrivedUzbParcels)
                ->description('O\'zbekistonda joylashgan posilkalar')
                ->icon('heroicon-o-building-office')
                ->color('warning'),

            Stat::make('Yetkazilgan', $deliveredParcels)
                ->description($deliveredTrend >= 0 ? "+{$deliveredTrend}% (30 kun)" : "{$deliveredTrend}% (30 kun)")
                ->descriptionIcon($deliveredTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->descriptionColor($deliveredTrend >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Ta\'qiqlangan', $bannedParcels)
                ->description('Ta\'qiqlangan tovarlar')
                ->icon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Tayinlanmagan', $unassignedParcels)
                ->description('Mijoz tayinlanmagan posilkalar')
                ->icon('heroicon-o-user-minus')
                ->color('warning'),

            Stat::make('Jami og\'irlik', number_format($totalWeight, 1) . ' KG')
                ->description('Barcha posilkalar og\'irligi')
                ->icon('heroicon-o-scale')
                ->color('info'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
