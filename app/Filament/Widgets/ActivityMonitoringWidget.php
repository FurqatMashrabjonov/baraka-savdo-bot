<?php

namespace App\Filament\Widgets;

use App\Models\Parcel;
use App\Models\Client;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActivityMonitoringWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected ?string $heading = 'Kunlik faoliyat va tizim holati';

    protected function getStats(): array
    {
        // Today's activity
        $todayParcels = Parcel::whereDate('created_at', today())->count();
        $todayClients = Client::whereDate('created_at', today())->count();
        $todayDelivered = Parcel::whereDate('updated_at', today())
            ->where('status', 'delivered')->count();

        // Yesterday for comparison
        $yesterdayParcels = Parcel::whereDate('created_at', now()->subDay())->count();
        $yesterdayDelivered = Parcel::whereDate('updated_at', now()->subDay())
            ->where('status', 'delivered')->count();

        // Calculate trends
        $parcelTrend = $yesterdayParcels > 0 ?
            round((($todayParcels - $yesterdayParcels) / $yesterdayParcels) * 100, 1) :
            ($todayParcels > 0 ? 100 : 0);

        $deliveryTrend = $yesterdayDelivered > 0 ?
            round((($todayDelivered - $yesterdayDelivered) / $yesterdayDelivered) * 100, 1) :
            ($todayDelivered > 0 ? 100 : 0);

        // System health indicators
        $pendingParcels = Parcel::whereIn('status', ['created', 'arrived_china'])->count();
        $delayedParcels = Parcel::where('created_at', '<', now()->subDays(7))
            ->whereNotIn('status', ['delivered'])->count();

        // Recent imports (last 24 hours)
        $recentChinaImports = Parcel::where('china_uploaded_at', '>=', now()->subDay())->count();
        $recentUzbImports = Parcel::where('uzb_uploaded_at', '>=', now()->subDay())->count();

        return [
            Stat::make('Bugungi posilkalar', $todayParcels)
                ->description($parcelTrend >= 0 ? "+{$parcelTrend}% kechaga nisbatan" : "{$parcelTrend}% kechaga nisbatan")
                ->descriptionIcon($parcelTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->descriptionColor($parcelTrend >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-calendar-days')
                ->color('primary'),

            Stat::make('Bugungi mijozlar', $todayClients)
                ->description('Yangi ro\'yxatdan o\'tganlar')
                ->icon('heroicon-o-user-plus')
                ->color('success'),

            Stat::make('Bugun yetkazilgan', $todayDelivered)
                ->description($deliveryTrend >= 0 ? "+{$deliveryTrend}% kechaga nisbatan" : "{$deliveryTrend}% kechaga nisbatan")
                ->descriptionIcon($deliveryTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->descriptionColor($deliveryTrend >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-truck')
                ->color('success'),

            Stat::make('Kutilayotgan posilkalar', $pendingParcels)
                ->description('Hali yetkazilmagan')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Kechiktirilgan posilkalar', $delayedParcels)
                ->description('7 kundan oshgan')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('danger'),

            Stat::make('Xitoy importi (24s)', $recentChinaImports)
                ->description('So\'nggi 24 soat ichida')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info'),

            Stat::make('O\'zbekiston importi (24s)', $recentUzbImports)
                ->description('So\'nggi 24 soat ichida')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }

    protected static bool $isLazy = false; // Real-time updates
}
