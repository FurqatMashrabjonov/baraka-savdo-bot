<?php

namespace App\Filament\Widgets;

use App\Models\Parcel;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentParcelsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return ! auth()->user()->isKassir();
    }

    public function getHeading(): string
    {
        return __('filament.recent_parcels_title');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Parcel::query()
                    ->with('client')
                    ->latest('updated_at')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('track_number')
                    ->label(__('filament.track_number'))
                    ->searchable()
                    ->copyable(),

                TextColumn::make('client.full_name')
                    ->label(__('filament.client'))
                    ->placeholder(__('filament.unassigned'))
                    ->formatStateUsing(fn ($record) => $record->client
                        ? $record->client->full_name.' ('.$record->client->phone.')'
                        : __('filament.unassigned')
                    ),

                BadgeColumn::make('status')
                    ->label(__('filament.status'))
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'created' => __('filament.status_created'),
                        'arrived_china' => __('filament.status_arrived_china'),
                        'arrived_uzb' => __('filament.status_arrived_uzb'),
                        'delivered' => __('filament.status_delivered'),
                        default => $state,
                    })
                    ->colors([
                        'gray' => 'created',
                        'info' => 'arrived_china',
                        'warning' => 'arrived_uzb',
                        'success' => 'delivered',
                    ]),

                TextColumn::make('weight')
                    ->label(__('filament.weight'))
                    ->formatStateUsing(function ($record) {
                        if (! $record->weight) {
                            return '—';
                        }

                        $ceiledWeight = ceil($record->weight * 2) / 2;

                        return number_format($ceiledWeight, 1).' KG';
                    }),

                TextColumn::make('updated_at')
                    ->label(__('filament.last_updated'))
                    ->since()
                    ->dateTimeTooltip(),
            ])
            ->paginated(false)
            ->poll('30s'); // Auto-refresh every 30 seconds
    }
}
