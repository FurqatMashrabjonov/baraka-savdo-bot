<?php

namespace App\Filament\Widgets;

use App\Models\Parcel;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WeightStatsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        $totalWeight = Parcel::whereNotNull('weight')->sum('weight');
        $averageWeight = Parcel::whereNotNull('weight')->avg('weight');
        $maxWeight = Parcel::whereNotNull('weight')->max('weight');
        $minWeight = Parcel::whereNotNull('weight')->where('weight', '>', 0)->min('weight');

        $heavyParcels = Parcel::where('weight', '>', 10)->count(); // Over 10kg
        $lightParcels = Parcel::whereBetween('weight', [0.1, 2])->count(); // 0.1-2kg

        $bannedWeight = Parcel::where('is_banned', true)->whereNotNull('weight')->sum('weight');

        // Recent weight trends (last 7 days)
        $recentTotalWeight = Parcel::whereNotNull('weight')
            ->where('created_at', '>=', now()->subDays(7))
            ->sum('weight');

        return [
            Stat::make('Jami og\'irlik', number_format($totalWeight, 1) . ' KG')
                ->description('Barcha posilkalar og\'irligi')
                ->icon('heroicon-o-scale')
                ->color('primary'),

            Stat::make('O\'rtacha og\'irlik', $averageWeight ? number_format($averageWeight, 2) . ' KG' : '—')
                ->description('Bitta posilka o\'rtacha og\'irligi')
                ->icon('heroicon-o-calculator')
                ->color('info'),

            Stat::make('Eng og\'ir posilka', $maxWeight ? number_format($maxWeight, 1) . ' KG' : '—')
                ->description('Maksimal og\'irlik')
                ->icon('heroicon-o-arrow-up')
                ->color('warning'),

            Stat::make('Eng yengil posilka', $minWeight ? number_format($minWeight, 1) . ' KG' : '—')
                ->description('Minimal og\'irlik')
                ->icon('heroicon-o-arrow-down')
                ->color('success'),

            Stat::make('Og\'ir posilkalar', $heavyParcels)
                ->description('10 KG dan og\'ir')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning'),

            Stat::make('Yengil posilkalar', $lightParcels)
                ->description('0.1-2 KG orasida')
//                ->icon('heroicon-o-feather')
                ->color('success'),

            Stat::make('Ta\'qiqlangan og\'irlik', $bannedWeight ? number_format($bannedWeight, 1) . ' KG' : '0 KG')
                ->description('Ta\'qiqlangan tovarlar og\'irligi')
                ->icon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Haftalik og\'irlik', number_format($recentTotalWeight, 1) . ' KG')
                ->description('So\'nggi 7 kun ichida')
                ->icon('heroicon-o-calendar-days')
                ->color('info'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
