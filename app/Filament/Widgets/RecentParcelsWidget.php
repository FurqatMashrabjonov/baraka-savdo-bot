<?php

namespace App\Filament\Widgets;

use App\Models\Parcel;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentParcelsWidget extends BaseWidget
{
    protected static ?string $heading = 'So\'nggi posilkalar';

    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

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
                    ->label('Trek raqami')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('client.full_name')
                    ->label('Mijoz')
                    ->placeholder('Tayinlanmagan')
                    ->formatStateUsing(fn ($record) => $record->client
                        ? $record->client->full_name . ' (' . $record->client->phone . ')'
                        : 'Tayinlanmagan'
                    ),

                BadgeColumn::make('status')
                    ->label('Holati')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'created' => 'Yaratilgan',
                        'arrived_china' => 'Xitoyga kelgan',
                        'arrived_uzb' => 'O\'zbekistonga kelgan',
                        'delivered' => 'Yetkazilgan',
                        default => $state,
                    })
                    ->colors([
                        'gray' => 'created',
                        'info' => 'arrived_china',
                        'warning' => 'arrived_uzb',
                        'success' => 'delivered',
                    ]),

                TextColumn::make('weight')
                    ->label('Og\'irlik')
                    ->formatStateUsing(function ($record) {
                        if (!$record->weight) {
                            return '—';
                        }

                        $ceiledWeight = ceil($record->weight * 2) / 2;
                        return number_format($ceiledWeight, 1) . ' KG';
                    }),

                TextColumn::make('updated_at')
                    ->label('Oxirgi yangilanish')
                    ->since()
                    ->dateTimeTooltip(),
            ])
            ->paginated(false)
            ->poll('30s'); // Auto-refresh every 30 seconds
    }
}
