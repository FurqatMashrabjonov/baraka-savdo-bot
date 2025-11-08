<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class CustomDashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        // Specific widgets for China Kassir
        if (auth()->user()->isChinaKassir()) {
            return [
                \App\Filament\Widgets\ChinaKassirStatsWidget::class,
                \App\Filament\Widgets\ThreeDayParcelsWidget::class,
            ];
        }

        // General kassir widgets (for UZB kassir and other kassir roles)
        if (auth()->user()->isKassir()) {
            return [
                \App\Filament\Widgets\KassirStatsWidget::class,
                \App\Filament\Widgets\ThreeDayParcelsWidget::class,
            ];
        }

        // Admin widgets - only most essential
        return [
            \App\Filament\Widgets\ParcelStatsWidget::class,
        ];
    }
}
