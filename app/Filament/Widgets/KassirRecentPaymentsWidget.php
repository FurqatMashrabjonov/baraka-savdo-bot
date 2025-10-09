<?php

namespace App\Filament\Widgets;

use App\Enums\ParcelStatus;
use App\Models\Parcel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class KassirRecentPaymentsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()->isKassir();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Parcel::query()
                    ->where('payment_processed_by', auth()->id())
                    ->whereNotNull('payment_date')
                    ->latest('payment_date')
                    ->limit(20)
            )
            ->columns([
                TextColumn::make('track_number')
                    ->label(__('filament.track_number'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('filament.status'))
                    ->badge()
                    ->color(fn ($state): string => $state instanceof ParcelStatus ? match ($state) {
                        ParcelStatus::CREATED => 'gray',
                        ParcelStatus::ARRIVED_CHINA => 'info',
                        ParcelStatus::IN_WAREHOUSE => 'warning',
                        ParcelStatus::DISPATCHED => 'primary',
                        ParcelStatus::ARRIVED_UZB => 'warning',
                        ParcelStatus::DELIVERED => 'success',
                        default => 'gray',
                    } : 'gray'),

                TextColumn::make('payment_amount_usd')
                    ->label(__('filament.payment_amount_usd'))
                    ->formatStateUsing(fn ($state) => '$'.number_format($state, 2))
                    ->sortable(),

                TextColumn::make('payment_amount_uzs')
                    ->label(__('filament.payment_amount_uzs'))
                    ->formatStateUsing(fn ($state) => number_format($state, 0, '.', ' ').' UZS')
                    ->sortable(),

                TextColumn::make('payment_date')
                    ->label(__('filament.payment_date'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

            ])
            ->heading(__('filament.my_recent_payments'))
            ->description(__('filament.last_20_payments_processed'))
            ->defaultSort('payment_date', 'desc')
            ->paginated(false);
    }
}
