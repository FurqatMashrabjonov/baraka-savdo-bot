<?php

namespace App\Filament\Widgets;

use App\Enums\ParcelStatus;
use App\Models\Parcel;
use Filament\Widgets\ChartWidget;

class ParcelStatusDistributionWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string
    {
        return __('filament.status_distribution_title');
    }

    protected function getData(): array
    {
        $statusCounts = [
            'created' => Parcel::where('status', ParcelStatus::CREATED)->count(),
            'arrived_china' => Parcel::where('status', ParcelStatus::ARRIVED_CHINA)->count(),
            'arrived_uzb' => Parcel::where('status', ParcelStatus::ARRIVED_UZB)->count(),
            'delivered' => Parcel::where('status', ParcelStatus::DELIVERED)->count(),
        ];

        return [
            'datasets' => [
                [
                    'data' => array_values($statusCounts),
                    'backgroundColor' => [
                        '#6b7280', // Gray for Created
                        '#3b82f6', // Blue for Arrived China
                        '#f59e0b', // Amber for Arrived UZB
                        '#10b981', // Green for Delivered
                    ],
                    'borderColor' => [
                        '#374151',
                        '#1e40af',
                        '#d97706',
                        '#047857',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => [
                __('filament.status_created').' ('.$statusCounts['created'].')',
                __('filament.status_arrived_china').' ('.$statusCounts['arrived_china'].')',
                __('filament.status_arrived_uzb').' ('.$statusCounts['arrived_uzb'].')',
                __('filament.status_delivered').' ('.$statusCounts['delivered'].')',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
