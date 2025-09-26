<?php

namespace App\Filament\Resources\Users\Widgets;

use App\Models\Parcel;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        return auth()->user()->isAdmin();
    }

    protected function getStats(): array
    {
        // Count users by role
        $totalUsers = User::count();
        $adminUsers = User::role('admin')->count();
        $chinaKassirs = User::role('kassir_china')->count();
        $uzbKassirs = User::role('kassir_uzb')->count();

        // Payment statistics
        $totalPaymentsProcessed = Parcel::whereNotNull('payment_processed_by')->count();
        $todayPayments = Parcel::whereNotNull('payment_processed_by')
            ->whereDate('payment_date', today())
            ->count();

        // Top performer this month
        $topPerformer = User::whereHas('processedPayments', function ($query) {
            $query->whereMonth('payment_date', now()->month);
        })
            ->withCount(['processedPayments' => function ($query) {
                $query->whereMonth('payment_date', now()->month);
            }])
            ->orderBy('processed_payments_count', 'desc')
            ->first();

        return [
            Stat::make(__('filament.total_users'), $totalUsers)
                ->description($adminUsers.' admins, '.($chinaKassirs + $uzbKassirs).' kassirs')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make(__('filament.china_kassirs'), $chinaKassirs)
                ->description(__('filament.active_china_kassirs'))
                ->descriptionIcon('heroicon-m-globe-asia-australia')
                ->color('warning'),

            Stat::make(__('filament.uzb_kassirs'), $uzbKassirs)
                ->description(__('filament.active_uzb_kassirs'))
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),

            Stat::make(__('filament.payments_today'), $todayPayments)
                ->description(__('filament.total_processed').': '.$totalPaymentsProcessed)
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make(__('filament.top_performer'), $topPerformer?->name ?? __('filament.none'))
                ->description($topPerformer ? $topPerformer->processed_payments_count.' '.__('filament.payments_this_month') : __('filament.no_payments_yet'))
                ->descriptionIcon('heroicon-m-trophy')
                ->color('warning'),
        ];
    }
}
