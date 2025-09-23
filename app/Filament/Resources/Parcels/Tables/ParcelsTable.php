<?php

namespace App\Filament\Resources\Parcels\Tables;

use App\Enums\ParcelStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ParcelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('client.full_name')
                    ->label(__('filament.client_name'))
                    ->searchable()
                    ->placeholder(__('filament.unassigned'))
                    ->formatStateUsing(fn ($record) => $record->client
                            ? $record->client->full_name.' ('.$record->client->phone.')'
                            : __('filament.unassigned')
                    ),

                TextColumn::make('track_number')
                    ->label(__('filament.track_number'))
                    ->searchable()
                    ->copyable()
                    ->copyMessage(__('filament.track_number_copied')),

                BadgeColumn::make('status')
                    ->label(__('filament.status'))
                    ->formatStateUsing(fn (ParcelStatus $state) => $state->getLabel())
                    ->colors([
                        'gray' => ParcelStatus::CREATED->value,
                        'info' => ParcelStatus::ARRIVED_CHINA->value,
                        'warning' => ParcelStatus::ARRIVED_UZB->value,
                        'success' => ParcelStatus::DELIVERED->value,
                    ]),

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

                TextColumn::make('uzb_uploaded_at')
                    ->label(__('filament.uzb_uploaded_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->placeholder(__('filament.not_set')),

                TextColumn::make('created_at')
                    ->label(__('filament.created_at'))
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('filament.filter_status'))
                    ->options([
                        ParcelStatus::CREATED->value => __('filament.status_created'),
                        ParcelStatus::ARRIVED_CHINA->value => __('filament.status_arrived_china'),
                        ParcelStatus::ARRIVED_UZB->value => __('filament.status_arrived_uzb'),
                        ParcelStatus::DELIVERED->value => __('filament.status_delivered'),
                    ])
                    ->multiple(),

                Filter::make('unassigned')
                    ->label(__('filament.filter_unassigned'))
                    ->query(fn (Builder $query) => $query->whereNull('client_id'))
                    ->toggle(),

                Filter::make('banned')
                    ->label(__('filament.filter_banned'))
                    ->query(fn (Builder $query) => $query->where('is_banned', true))
                    ->toggle(),

                Filter::make('older_than_3_days')
                    ->label(__('filament.filter_older_than_3_days'))
                    ->query(fn (Builder $query) => $query
                        ->whereNotNull('client_id') // Client has entered track number
                        ->where('created_at', '<', now()->subDays(3)) // More than 3 days ago
                        ->where(function ($query) {
                            $query->whereNull('china_uploaded_at') // No China import yet
                                ->orWhereNull('uzb_uploaded_at'); // Or no Uzbekistan import yet
                        })
                    )
                    ->toggle(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions([
                Action::make('import_china')
                    ->label(__('filament.import_china'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn () => auth()->user()->canImportFromChina())
                    ->form([
                        FileUpload::make('china_excel_file')
                            ->label(__('filament.china_excel_file'))
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->required()
                            ->helperText(__('filament.china_excel_help')),
                    ])
                    ->action(function (array $data) {
                        $importService = app(\App\Services\ParcelImportService::class);

                        try {
                            $filePath = storage_path('app/private/'.$data['china_excel_file']);
                            $result = $importService->importChinaExcel($filePath);

                            $summary = $importService->getImportSummary($result, 'china');

                            Notification::make()
                                ->title(__('filament.import_success_china'))
                                ->body($summary)
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(__('filament.import_error'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->modalSubmitActionLabel(__('filament.import_action'))
                    ->modalHeading(__('filament.import_china_modal_title')),

                Action::make('import_uzb')
                    ->label(__('filament.import_uzbekistan'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('warning')
                    ->visible(fn () => auth()->user()->canImportFromUzbekistan())
                    ->form([
                        FileUpload::make('uzb_excel_file')
                            ->label(__('filament.uzbekistan_excel_file'))
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->required()
                            ->helperText(__('filament.uzbekistan_excel_help')),
                    ])
                    ->action(function (array $data) {
                        $importService = app(\App\Services\ParcelImportService::class);

                        try {
                            $filePath = storage_path('app/private/'.$data['uzb_excel_file']);
                            $result = $importService->importUzbekistanExcel($filePath);

                            $summary = $importService->getImportSummary($result, 'uzbekistan');

                            Notification::make()
                                ->title(__('filament.import_success_uzbekistan'))
                                ->body($summary)
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(__('filament.import_error'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->modalSubmitActionLabel(__('filament.import_action'))
                    ->modalHeading(__('filament.import_uzbekistan_modal_title')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([25, 50, 100]);
    }
}
