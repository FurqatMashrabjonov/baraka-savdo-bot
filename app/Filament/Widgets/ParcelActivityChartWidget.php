<?php

namespace App\Filament\Widgets;

use App\Enums\ParcelStatus;
use App\Models\Parcel;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ParcelActivityChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;
    protected ?string $heading = 'So\'nggi 30 kun ichida pochta faoliyati';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $last30Days = collect(range(29, 0))->map(function ($daysAgo) {
            $date = Carbon::now()->subDays($daysAgo);

            return [
                'date' => $date->format('M d'),
                'created' => Parcel::whereDate('created_at', $date)->count(),
                'arrived_china' => Parcel::whereDate('china_uploaded_at', $date)->count(),
                'arrived_uzb' => Parcel::whereDate('uzb_uploaded_at', $date)->count(),
                'delivered' => Parcel::where('status', ParcelStatus::DELIVERED)
                    ->whereDate('updated_at', $date)
                    ->count(),
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Yaratilgan',
                    'data' => $last30Days->pluck('created')->toArray(),
                    'borderColor' => '#6b7280',
                    'backgroundColor' => 'rgba(107, 114, 128, 0.1)',
                ],
                [
                    'label' => 'Xitoyga kelgan',
                    'data' => $last30Days->pluck('arrived_china')->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
                [
                    'label' => 'O\'zbekistonga kelgan',
                    'data' => $last30Days->pluck('arrived_uzb')->toArray(),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                ],
                [
                    'label' => 'Yetkazilgan',
                    'data' => $last30Days->pluck('delivered')->toArray(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
            ],
            'labels' => $last30Days->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
