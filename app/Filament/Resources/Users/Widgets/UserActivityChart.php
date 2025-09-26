<?php

namespace App\Filament\Resources\Users\Widgets;

use App\Models\Parcel;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class UserActivityChart extends ChartWidget
{
    protected ?string $heading = 'Daily Payments Activity (Last 30 Days)';

    public ?string $filter = 'all';

    public static function canView(): bool
    {
        return auth()->user()->isAdmin();
    }

    protected function getFilters(): ?array
    {
        $filters = ['all' => __('filament.all_users')];

        User::whereHas('processedPayments')->get()->each(function ($user) use (&$filters) {
            $filters[$user->id] = $user->name;
        });

        return $filters;
    }

    protected function getData(): array
    {
        $userId = $this->filter === 'all' ? null : $this->filter;

        // Get last 30 days
        $days = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days->push($date->format('Y-m-d'));
        }

        $paymentsQuery = Parcel::whereNotNull('payment_processed_by')
            ->whereDate('payment_date', '>=', Carbon::now()->subDays(29));

        if ($userId) {
            $paymentsQuery->where('payment_processed_by', $userId);
        }

        $payments = $paymentsQuery->get()->groupBy(function ($payment) {
            return Carbon::parse($payment->payment_date)->format('Y-m-d');
        });

        $datasets = [];

        if ($userId === null || $userId === 'all') {
            // Show all users
            User::whereHas('processedPayments')->get()->each(function ($user) use ($days, $payments, &$datasets) {
                $userPayments = $payments->map(function ($dayPayments) use ($user) {
                    return $dayPayments->where('payment_processed_by', $user->id)->count();
                });

                $data = $days->map(function ($day) use ($payments, $user) {
                    return $payments->get($day, collect())->where('payment_processed_by', $user->id)->count();
                });

                $datasets[] = [
                    'label' => $user->name,
                    'data' => $data->values()->toArray(),
                    'borderColor' => $this->getUserColor($user->id),
                    'backgroundColor' => $this->getUserColor($user->id, 0.1),
                    'tension' => 0.4,
                ];
            });
        } else {
            // Show specific user
            $user = User::find($userId);
            $data = $days->map(function ($day) use ($payments) {
                return $payments->get($day, collect())->count();
            });

            $datasets[] = [
                'label' => $user->name.' - '.__('filament.payments_processed'),
                'data' => $data->values()->toArray(),
                'borderColor' => '#10B981',
                'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                'tension' => 0.4,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $days->map(function ($day) {
                return Carbon::parse($day)->format('M j');
            })->toArray(),
        ];
    }

    private function getUserColor(int $userId, float $alpha = 1): string
    {
        $colors = [
            '#10B981', // green
            '#3B82F6', // blue
            '#F59E0B', // yellow
            '#EF4444', // red
            '#8B5CF6', // purple
            '#F97316', // orange
            '#06B6D4', // cyan
            '#84CC16', // lime
        ];

        $color = $colors[$userId % count($colors)];

        if ($alpha < 1) {
            // Convert hex to rgba
            $hex = str_replace('#', '', $color);
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));

            return "rgba($r, $g, $b, $alpha)";
        }

        return $color;
    }

    protected function getType(): string
    {
        return 'line';
    }
}
