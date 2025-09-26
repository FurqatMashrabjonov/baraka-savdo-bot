<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class CustomDashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        if (auth()->user()->isKassir()) {
            return [
                \App\Filament\Widgets\KassirStatsWidget::class,
                \App\Filament\Widgets\KassirRecentPaymentsWidget::class,
            ];
        }

        // Admin widgets (existing ones)
        return [
            \App\Filament\Widgets\ParcelStatsWidget::class,
            \App\Filament\Widgets\ClientStatsWidget::class,
            \App\Filament\Widgets\ParcelStatusDistributionWidget::class,
            \App\Filament\Widgets\ParcelActivityChartWidget::class,
            \App\Filament\Widgets\RecentParcelsWidget::class,
        ];
    }
}
