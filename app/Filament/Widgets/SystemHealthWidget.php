<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Parcel;
use App\Models\TelegramAccount;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SystemHealthWidget extends BaseWidget
{
    protected static ?int $sort = 7;

    protected function getStats(): array
    {
        // Performance metrics
        $problemParcels = Parcel::where('created_at', '<', now()->subDays(7))
            ->whereIn('status', ['created', 'arrived_china'])
            ->count();

        $stuckInChina = Parcel::where('china_uploaded_at', '<', now()->subDays(14))
            ->where('status', 'arrived_china')
            ->count();

        $unassignedOld = Parcel::whereNull('client_id')
            ->where('created_at', '<', now()->subDays(3))
            ->count();

        // Growth metrics
        $weeklyGrowth = [
            'parcels' => Parcel::where('created_at', '>=', now()->subWeek())->count(),
            'clients' => Client::where('created_at', '>=', now()->subWeek())->count(),
            'telegram' => TelegramAccount::where('created_at', '>=', now()->subWeek())->count(),
        ];

        // Efficiency metrics
        $deliveryRate = Parcel::count() > 0 ?
            round((Parcel::where('status', 'delivered')->count() / Parcel::count()) * 100, 1) : 0;

        $assignmentRate = Parcel::count() > 0 ?
            round((Parcel::whereNotNull('client_id')->count() / Parcel::count()) * 100, 1) : 0;

        return [
            Stat::make('Muammoli posilkalar', $problemParcels)
                ->description('7 kundan oshgan, yetkazilmagan')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($problemParcels > 50 ? 'danger' : ($problemParcels > 20 ? 'warning' : 'success')),

            Stat::make('Xitoyda qolgan', $stuckInChina)
                ->description('14 kundan oshgan')
                ->icon('heroicon-o-clock')
                ->color($stuckInChina > 100 ? 'danger' : ($stuckInChina > 50 ? 'warning' : 'success')),

            Stat::make('Eski tayinlanmaganlar', $unassignedOld)
                ->description('3 kundan oshgan, mijoz yo\'q')
                ->icon('heroicon-o-user-minus')
                ->color($unassignedOld > 20 ? 'danger' : ($unassignedOld > 10 ? 'warning' : 'success')),

            Stat::make('Yetkazish samaradorligi', $deliveryRate . '%')
                ->description('Jami posilkalardan yetkazilgan')
                ->icon('heroicon-o-chart-bar')
                ->color($deliveryRate >= 80 ? 'success' : ($deliveryRate >= 60 ? 'warning' : 'danger')),

            Stat::make('Tayinlash samaradorligi', $assignmentRate . '%')
                ->description('Mijoz tayinlangan posilkalar')
//                ->icon('heroicon-o-user-check')
                ->color($assignmentRate >= 90 ? 'success' : ($assignmentRate >= 70 ? 'warning' : 'danger')),

            Stat::make('Haftalik o\'sish', $weeklyGrowth['parcels'])
                ->description('Yangi posilkalar (7 kun)')
                ->icon('heroicon-o-arrow-trending-up')
                ->color('info'),

            Stat::make('Yangi mijozlar', $weeklyGrowth['clients'])
                ->description('Haftalik ro\'yxat (7 kun)')
                ->icon('heroicon-o-users')
                ->color('success'),

            Stat::make('Telegram o\'sishi', $weeklyGrowth['telegram'])
                ->description('Yangi Telegram foydalanuvchilari')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('info'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }

    protected static bool $isLazy = false; // Real-time monitoring
}
