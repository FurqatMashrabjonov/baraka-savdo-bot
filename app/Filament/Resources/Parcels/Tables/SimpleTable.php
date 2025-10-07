<?php

namespace App\Filament\Resources\Parcels\Tables;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SimpleTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('track_number')
                    ->label(__('filament.track_number'))
                    ->searchable()
                    ->copyable()
                    ->copyMessage(__('filament.track_number_copied')),

                TextColumn::make('weight')
                    ->label(__('filament.weight'))
                    ->numeric(decimalPlaces: 3)
                    ->placeholder(__('filament.not_set')),

                IconColumn::make('is_banned')
                    ->label(__('filament.is_banned'))
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),

                TextColumn::make('china_uploaded_at')
                    ->label(__('filament.china_uploaded_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->placeholder(__('filament.not_set')),
            ])
            ->filters([
                Filter::make('single_date')
                    ->form([
                        DatePicker::make('date')
                            ->label(__('filament.filter_by_date'))
                            ->placeholder(__('filament.select_date'))
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '=', $date),
                            );
                    }),

                SelectFilter::make('is_banned')
                    ->label(__('filament.filter_banned_status'))
                    ->options([
                        1 => __('filament.banned'),
                        0 => __('filament.not_banned'),
                    ])
                    ->placeholder(__('filament.all_parcels')),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
