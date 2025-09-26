<?php

namespace App\Filament\Widgets;

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
                TextColumn::make('parcel_number')
                    ->label(__('filament.parcel_number'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client_name')
                    ->label(__('filament.client_name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('client_phone')
                    ->label(__('filament.phone'))
                    ->searchable(),

                TextColumn::make('status')
                    ->label(__('filament.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'received' => 'danger',
                        'storage' => 'warning',
                        'paid' => 'success',
                        'delivered' => 'primary',
                        default => 'gray',
                    }),

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
