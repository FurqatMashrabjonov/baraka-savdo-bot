<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\TelegramAccount;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TelegramStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalTelegramUsers = TelegramAccount::count();
        $totalClients = Client::count();
        $activeClients = Client::whereHas('parcels')->count();

        // Recent activity (last 7 days)
        $recentClients = Client::where('created_at', '>=', now()->subDays(7))->count();
        $recentTelegramUsers = TelegramAccount::where('created_at', '>=', now()->subDays(7))->count();

        // Clients with parcels statistics
        $clientsWithParcels = Client::whereHas('parcels')->count();
        $clientsWithoutParcels = Client::whereDoesntHave('parcels')->count();

        // Active users percentage
        $activePercentage = $totalClients > 0 ? round(($activeClients / $totalClients) * 100, 1) : 0;

        return [
            Stat::make('Telegram foydalanuvchilari', $totalTelegramUsers)
                ->description($recentClients > 0 ? "+{$recentTelegramUsers} yangi (7 kun)" : 'Yangi foydalanuvchilar yo\'q')
                ->descriptionIcon($recentTelegramUsers > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-minus')
                ->descriptionColor($recentTelegramUsers > 0 ? 'success' : 'gray')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('info'),

            Stat::make('Jami mijozlar', $totalClients)
                ->description($recentClients > 0 ? "+{$recentClients} yangi (7 kun)" : 'Yangi mijozlar yo\'q')
                ->descriptionIcon($recentClients > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-minus')
                ->descriptionColor($recentClients > 0 ? 'success' : 'gray')
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Faol mijozlar', $activeClients)
                ->description("{$activePercentage}% jami mijozlardan")
                ->icon('heroicon-o-user-group')
                ->color('success'),

            Stat::make('Posilkasiz mijozlar', $clientsWithoutParcels)
                ->description('Hali posilka yo\'q')
                ->icon('heroicon-o-user-minus')
                ->color('warning'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
