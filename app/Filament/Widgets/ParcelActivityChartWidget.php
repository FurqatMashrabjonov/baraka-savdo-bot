<?php

namespace App\Filament\Widgets;

use App\Enums\ParcelStatus;
use App\Models\Parcel;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ParcelActivityChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return __('filament.activity_chart_title');
    }

    protected function getData(): array
    {
        // Show last 14 days instead of 30 for better readability
        $last14Days = collect(range(13, 0))->map(function ($daysAgo) {
            $date = Carbon::now()->subDays($daysAgo);

            return [
                'date' => $date->format('M d'),
                'created' => Parcel::whereDate('created_at', $date)->count(),
                'delivered' => Parcel::where('status', ParcelStatus::DELIVERED)
                    ->whereDate('updated_at', $date)
                    ->count(),
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => __('filament.status_created'),
                    'data' => $last14Days->pluck('created')->toArray(),
                    'borderColor' => '#6b7280',
                    'backgroundColor' => 'rgba(107, 114, 128, 0.1)',
                    'tension' => 0.3,
                ],
                [
                    'label' => __('filament.status_delivered'),
                    'data' => $last14Days->pluck('delivered')->toArray(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $last14Days->pluck('date')->toArray(),
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
            'elements' => [
                'point' => [
                    'radius' => 3,
                ],
            ],
        ];
    }
}
