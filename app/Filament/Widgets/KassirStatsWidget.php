<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KassirStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()->isKassir();
    }

    protected function getStats(): array
    {
        $user = auth()->user();

        // My total payments processed
        $totalPayments = $user->processedPayments()->count();

        // This month payments
        $thisMonthPayments = $user->processedPayments()
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->count();

        // Today's payments
        $todayPayments = $user->processedPayments()
            ->whereDate('payment_date', today())
            ->count();

        // Total amount processed (USD)
        $totalUsdAmount = $user->processedPayments()->sum('payment_amount_usd');

        // Total amount processed (UZS)
        $totalUzsAmount = $user->processedPayments()->sum('payment_amount_uzs');

        // This week payments
        $thisWeekPayments = $user->processedPayments()
            ->whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        return [
            Stat::make(__('filament.my_total_payments'), $totalPayments)
                ->description(__('filament.all_time_total'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make(__('filament.this_month'), $thisMonthPayments)
                ->description(__('filament.payments_this_month'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),

            Stat::make(__('filament.today'), $todayPayments)
                ->description(__('filament.payments_today'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('filament.this_week'), $thisWeekPayments)
                ->description(__('filament.payments_this_week'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make(__('filament.total_usd_processed'), '$'.number_format($totalUsdAmount, 2))
                ->description(__('filament.lifetime_usd'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make(__('filament.total_uzs_processed'), number_format($totalUzsAmount, 0, '.', ' ').' UZS')
                ->description(__('filament.lifetime_uzs'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
        ];
    }
}
