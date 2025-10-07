<?php

namespace App\Filament\Resources\ThreeDayParcels;

use App\Filament\Resources\ThreeDayParcels\Pages\ListThreeDayParcels;
use App\Models\Parcel;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ThreeDayParcelsResource extends Resource
{
    protected static ?string $model = Parcel::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static ?string $navigationLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('filament.three_day_parcels_widget');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::query()
            ->whereNull('china_uploaded_at') // Not imported from China Excel
            ->whereNotNull('client_id') // Client has added track number
            ->where('created_at', '>=', now()->subDays(3)) // Within last 3 days
            ->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $count = static::getNavigationBadge();

        if ($count > 10) {
            return 'danger'; // Red for high numbers
        } elseif ($count > 5) {
            return 'warning'; // Yellow for medium numbers
        } elseif ($count > 0) {
            return 'primary'; // Blue for low numbers
        }

        return 'gray'; // Gray for zero
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.three_day_parcels_widget');
    }

    public static function getModelLabel(): string
    {
        return __('filament.parcel');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('track_number')
                    ->label(__('filament.track_number'))
                    ->searchable()
                    ->copyable()
                    ->copyMessage(__('filament.track_number_copied'))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Botga kiritilgan sana')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('client_id')
                    ->label('Klient IDsi')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Filter::make('track_number')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('track_number')
                            ->label(__('filament.track_number'))
                            ->placeholder('Trek raqami bo\'yicha qidirish'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['track_number'],
                                fn (Builder $query, $trackNumber): Builder => $query->where('track_number', 'like', "%{$trackNumber}%"),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['track_number'] ?? null) {
                            $indicators['track_number'] = 'Trek raqami: ' . $data['track_number'];
                        }
                        return $indicators;
                    }),

                Filter::make('client_id')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('client_id')
                            ->label('Klient IDsi')
                            ->placeholder('Klient ID bo\'yicha qidirish')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['client_id'],
                                fn (Builder $query, $clientId): Builder => $query->where('client_id', $clientId),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['client_id'] ?? null) {
                            $indicators['client_id'] = 'Klient IDsi: ' . $data['client_id'];
                        }
                        return $indicators;
                    }),

                Filter::make('created_at_range')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Boshlanish sanasi')
                            ->placeholder('Sanani tanlang'),
                        DatePicker::make('created_until')
                            ->label('Tugash sanasi')
                            ->placeholder('Sanani tanlang'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Dan: ' . $data['created_from'];
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Gacha: ' . $data['created_until'];
                        }
                        return $indicators;
                    }),
            ])
            ->filtersFormColumns(2)
            ->modifyQueryUsing(fn ($query) => $query
                ->whereNull('china_uploaded_at') // Not imported from China Excel
                ->whereNotNull('client_id') // Client has added track number
                ->where('created_at', '>=', now()->subDays(3)) // Within last 3 days
                ->with('client')
            )
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Hech qanday 3 kunlik pochta topilmadi')
            ->emptyStateDescription('Oxirgi 3 kun ichida kiritilgan va Xitoydan import qilinmagan pochtalar yo\'q.');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListThreeDayParcels::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Disable create action for everyone
    }
}
