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
                    ->label('Mijoz')
                    ->searchable()
                    ->placeholder('Tayinlanmagan')
                    ->formatStateUsing(fn ($record) => $record->client
                            ? $record->client->full_name.' ('.$record->client->phone.')'
                            : 'Tayinlanmagan'
                    ),

                TextColumn::make('track_number')
                    ->label('Trek raqami')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Trek raqami nusxalandi'),

                BadgeColumn::make('status')
                    ->label('Holati')
                    ->formatStateUsing(fn (ParcelStatus $state) => $state->getLabel())
                    ->colors([
                        'gray' => ParcelStatus::CREATED->value,
                        'info' => ParcelStatus::ARRIVED_CHINA->value,
                        'warning' => ParcelStatus::ARRIVED_UZB->value,
                        'success' => ParcelStatus::DELIVERED->value,
                    ]),

                TextColumn::make('weight')
                    ->label('Og\'irligi (kg)')
                    ->numeric(decimalPlaces: 3)
                    ->placeholder('—'),

                IconColumn::make('is_banned')
                    ->label('Ta\'qiqlangan')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),

                TextColumn::make('china_uploaded_at')
                    ->label('Xitoyga kelgan vaqti')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('uzb_uploaded_at')
                    ->label('O\'zbekistonga kelgan vaqti')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Yaratilgan')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Holati')
                    ->options([
                        ParcelStatus::CREATED->value => 'Yaratilgan',
                        ParcelStatus::ARRIVED_CHINA->value => 'Xitoyga kelgan',
                        ParcelStatus::ARRIVED_UZB->value => 'O\'zbekistonga kelgan',
                        ParcelStatus::DELIVERED->value => 'Yetkazilgan',
                    ])
                    ->multiple(),

                Filter::make('unassigned')
                    ->label('Tayinlanmagan mijozlar')
                    ->query(fn (Builder $query) => $query->whereNull('client_id'))
                    ->toggle(),

                Filter::make('banned')
                    ->label('Taqiqlangan tovarlar')
                    ->query(fn (Builder $query) => $query->where('is_banned', true))
                    ->toggle(),

                Filter::make('older_than_3_days')
                    ->label('3 kundan oshganlar')
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
                    ->label('Import from China')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn () => auth()->user()->canImportFromChina())
                    ->form([
                        FileUpload::make('china_excel_file')
                            ->label('Excel fayli (Xitoy)')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->required()
                            ->helperText('Excel fayli: track_number, weight(KG), is_banned ustunlari bo\'lishi kerak'),
                    ])
                    ->action(function (array $data) {
                        $importService = app(\App\Services\ParcelImportService::class);

                        try {
                            $filePath = storage_path('app/private/' . $data['china_excel_file']);
                            $result = $importService->importChinaExcel($filePath);

                            $summary = $importService->getImportSummary($result, 'china');

                            Notification::make()
                                ->title('Xitoy Excel fayli muvaffaqiyatli import qilindi!')
                                ->body($summary)
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Import xatoligi')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->modalSubmitActionLabel('Import qilish')
                    ->modalHeading('Xitoydan Excel import qilish'),

                Action::make('import_uzb')
                    ->label('Import from Uzbekistan')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('warning')
                    ->visible(fn () => auth()->user()->canImportFromUzbekistan())
                    ->form([
                        FileUpload::make('uzb_excel_file')
                            ->label('Excel fayli (O\'zbekiston)')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->required()
                            ->helperText('Excel fayli: track_number ustuni bo\'lishi kerak'),
                    ])
                    ->action(function (array $data) {
                        $importService = app(\App\Services\ParcelImportService::class);

                        try {
                            $filePath = storage_path('app/private/' . $data['uzb_excel_file']);
                            $result = $importService->importUzbekistanExcel($filePath);

                            $summary = $importService->getImportSummary($result, 'uzbekistan');

                            Notification::make()
                                ->title('O\'zbekiston Excel fayli muvaffaqiyatli import qilindi!')
                                ->body($summary)
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Import xatoligi')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->modalSubmitActionLabel('Import qilish')
                    ->modalHeading('O\'zbekistondan Excel import qilish'),
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
