<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\TelegramAccount;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClientStatsWidget extends BaseWidget
{
    public static function canView(): bool
    {
        return ! auth()->user()->isKassir();
    }

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        // Total counts
        $totalClients = Client::count();
        $totalTelegramUsers = TelegramAccount::count();
        $activeClients = Client::whereHas('parcels')->count();

        // Daily stats (today vs yesterday)
        $todayClients = Client::whereDate('created_at', today())->count();
        $yesterdayClients = Client::whereDate('created_at', today()->subDay())->count();
        $dailyTrend = $yesterdayClients > 0 ?
            round((($todayClients - $yesterdayClients) / $yesterdayClients) * 100, 1) :
            ($todayClients > 0 ? 100 : 0);

        // Weekly stats (last 7 days vs previous 7 days)
        $weeklyClients = Client::where('created_at', '>=', now()->subDays(7))->count();
        $previousWeekClients = Client::whereBetween('created_at', [
            now()->subDays(14),
            now()->subDays(7),
        ])->count();
        $weeklyTrend = $previousWeekClients > 0 ?
            round((($weeklyClients - $previousWeekClients) / $previousWeekClients) * 100, 1) :
            ($weeklyClients > 0 ? 100 : 0);

        // Monthly stats (last 30 days vs previous 30 days)
        $monthlyClients = Client::where('created_at', '>=', now()->subDays(30))->count();
        $previousMonthClients = Client::whereBetween('created_at', [
            now()->subDays(60),
            now()->subDays(30),
        ])->count();
        $monthlyTrend = $previousMonthClients > 0 ?
            round((($monthlyClients - $previousMonthClients) / $previousMonthClients) * 100, 1) :
            ($monthlyClients > 0 ? 100 : 0);

        // Activity percentage
        $activityRate = $totalClients > 0 ? round(($activeClients / $totalClients) * 100, 1) : 0;

        return [
            Stat::make(__('filament.total_clients'), $totalClients)
                ->description($activityRate.'% '.__('filament.active_rate'))
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make(__('filament.today_clients'), $todayClients)
                ->description($dailyTrend >= 0 ?
                    '+'.$dailyTrend.'% '.__('filament.vs_yesterday') :
                    $dailyTrend.'% '.__('filament.vs_yesterday'))
                ->descriptionIcon($dailyTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->descriptionColor($dailyTrend >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-calendar-days')
                ->color('info'),

            Stat::make(__('filament.weekly_clients'), $weeklyClients)
                ->description($weeklyTrend >= 0 ?
                    '+'.$weeklyTrend.'% '.__('filament.vs_last_week') :
                    $weeklyTrend.'% '.__('filament.vs_last_week'))
                ->descriptionIcon($weeklyTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->descriptionColor($weeklyTrend >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-chart-bar')
                ->color('success'),

            Stat::make(__('filament.monthly_clients'), $monthlyClients)
                ->description($monthlyTrend >= 0 ?
                    '+'.$monthlyTrend.'% '.__('filament.vs_last_month') :
                    $monthlyTrend.'% '.__('filament.vs_last_month'))
                ->descriptionIcon($monthlyTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->descriptionColor($monthlyTrend >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-presentation-chart-line')
                ->color('warning'),

            Stat::make(__('filament.telegram_users'), $totalTelegramUsers)
                ->description(__('filament.telegram_integration'))
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('info'),

            Stat::make(__('filament.active_clients'), $activeClients)
                ->description(__('filament.clients_with_parcels'))
                ->icon('heroicon-o-user-group')
                ->color('success'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
