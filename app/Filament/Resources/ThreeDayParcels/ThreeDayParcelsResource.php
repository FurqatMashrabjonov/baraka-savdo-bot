<?php

namespace App\Filament\Resources\ThreeDayParcels;

use App\Enums\ParcelStatus;
use App\Filament\Resources\ThreeDayParcels\Pages\ListThreeDayParcels;
use App\Models\Parcel;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
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

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('filament.three_day_parcels_widget');
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
        $isUzbKassir = auth()->user()?->isUzbKassir() ?? false;

        $columns = [
            TextColumn::make('track_number')
                ->label(__('filament.track_number'))
                ->searchable()
                ->copyable()
                ->copyMessage(__('filament.track_number_copied'))
                ->sortable(),

            TextColumn::make('status')
                ->label(__('filament.status'))
                ->badge()
                ->formatStateUsing(fn (ParcelStatus $state): string => $state->getLabel())
                ->color(fn (ParcelStatus $state): string => $state->getColor())
                ->sortable(),

            TextColumn::make('created_at')
                ->label(__('filament.bot_entry_date'))
                ->dateTime('d.m.Y H:i')
                ->sortable()
                ->searchable(),

            TextColumn::make('client_id')
                ->label(__('filament.client_id'))
                ->sortable()
                ->searchable(),
        ];

        // Add detailed client info only for UZB kassir
        if ($isUzbKassir) {
            $columns[] = TextColumn::make('client.full_name')
                ->label(__('filament.full_name'))
                ->searchable()
                ->sortable()
                ->getStateUsing(fn ($record) => $record->client?->full_name ?? '—');

            $columns[] = TextColumn::make('client.phone')
                ->label(__('filament.phone'))
                ->searchable()
                ->getStateUsing(fn ($record) => $record->client?->phone ?? '—');

            $columns[] = TextColumn::make('client.address')
                ->label(__('filament.address'))
                ->searchable()
                ->wrap()
                ->getStateUsing(fn ($record) => $record->client?->address ?? '—');
        }

        return $table
            ->columns($columns)
            ->filters([
                Filter::make('track_number')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('track_number')
                            ->label(__('filament.track_number'))
                            ->placeholder(__('filament.search_by_track_number')),
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
                            $indicators['track_number'] = __('filament.track_number_filter').$data['track_number'];
                        }

                        return $indicators;
                    }),

                Filter::make('client_id')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('client_id')
                            ->label(__('filament.client_id'))
                            ->placeholder(__('filament.search_by_client_id'))
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
                            $indicators['client_id'] = __('filament.client_id_filter').$data['client_id'];
                        }

                        return $indicators;
                    }),

                Filter::make('created_at_range')
                    ->form([
                        DatePicker::make('created_from')
                            ->label(__('filament.created_from'))
                            ->placeholder(__('filament.select_date')),
                        DatePicker::make('created_until')
                            ->label(__('filament.created_until'))
                            ->placeholder(__('filament.select_date')),
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
                            $indicators['created_from'] = __('filament.from_filter').$data['created_from'];
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = __('filament.until_filter').$data['created_until'];
                        }

                        return $indicators;
                    }),
            ])
            ->filtersFormColumns(2)
            ->actions($isUzbKassir ? [
                Action::make('cancel')
                    ->label(__('filament.cancel_parcel'))
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(__('filament.cancel_parcel_confirmation'))
                    ->modalDescription(__('filament.cancel_parcel_description'))
                    ->visible(fn (Parcel $record): bool => $record->status === ParcelStatus::CREATED)
                    ->action(function (Parcel $record) {
                        // Only allow cancelling if parcel is still in CREATED status
                        if ($record->status !== ParcelStatus::CREATED) {
                            Notification::make()
                                ->title(__('filament.cannot_cancel_processed_parcel'))
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->status = ParcelStatus::CANCELLED;
                        $record->save();

                        Notification::make()
                            ->title(__('filament.parcel_cancelled_successfully'))
                            ->success()
                            ->send();
                    }),
            ] : [])
            ->modifyQueryUsing(fn ($query) => $query
                ->whereNull('china_uploaded_at') // Not imported from China Excel
                ->whereNotNull('client_id') // Client has added track number
                ->where('created_at', '<=', now()->subDays(3)) // 3 or more days ago
                ->where(function ($query) {
                    // Either not cancelled, or cancelled within the last day
                    $query->where('status', '!=', ParcelStatus::CANCELLED->value)
                        ->orWhere(function ($query) {
                            $query->where('status', '=', ParcelStatus::CANCELLED->value)
                                ->where('updated_at', '>=', now()->subDay());
                        });
                })
                ->with('client')
            )
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading(__('filament.no_three_day_parcels_found'))
            ->emptyStateDescription(__('filament.no_three_day_parcels_description'));
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
