<?php

namespace App\Filament\Resources\PrepaymentParcels\Tables;

use App\Enums\ParcelStatus;
use App\Enums\PrepaymentStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PrepaymentParcelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('client.id')
                    ->label(__('filament.client_id'))
                    ->sortable()
                    ->searchable()
                    ->placeholder(__('filament.unassigned')),

                TextColumn::make('client.full_name')
                    ->label(__('filament.client_name'))
                    ->searchable()
                    ->placeholder(__('filament.unassigned')),

                TextColumn::make('client.phone')
                    ->label(__('filament.phone'))
                    ->searchable()
                    ->placeholder(__('filament.unassigned')),

                TextColumn::make('track_number')
                    ->label(__('filament.track_number'))
                    ->searchable()
                    ->copyable()
                    ->copyMessage(__('filament.track_number_copied')),

                TextColumn::make('weight')
                    ->label(__('filament.weight'))
                    ->numeric(decimalPlaces: 3)
                    ->placeholder(__('filament.not_set')),

                BadgeColumn::make('prepayment_status')
                    ->label("Oldindan to'lov")
                    ->formatStateUsing(fn (?PrepaymentStatus $state) => $state?->getLabel() ?? '')
                    ->placeholder('—')
                    ->color(fn (?PrepaymentStatus $state): string => match ($state) {
                        PrepaymentStatus::PENDING => 'warning',
                        PrepaymentStatus::PAID => 'success',
                        null => 'gray',
                        default => 'gray',
                    }),

                BadgeColumn::make('status')
                    ->label(__('filament.status'))
                    ->formatStateUsing(fn (ParcelStatus $state) => match ($state) {
                        ParcelStatus::CREATED => __('filament.status_created'),
                        ParcelStatus::ARRIVED_CHINA => __('filament.status_arrived_china'),
                        ParcelStatus::IN_WAREHOUSE => 'Omborda',
                        ParcelStatus::DISPATCHED => "Yo'lga chiqdi",
                        ParcelStatus::ARRIVED_UZB => __('filament.status_arrived_uzb'),
                        ParcelStatus::PAYMENT_RECEIVED => "To'lov qabul qilindi",
                        ParcelStatus::DELIVERED => __('filament.status_delivered'),
                        default => $state->getLabel(),
                    })
                    ->color(fn (ParcelStatus $state): string => match ($state) {
                        ParcelStatus::CREATED => 'gray',
                        ParcelStatus::ARRIVED_CHINA => 'info',
                        ParcelStatus::IN_WAREHOUSE => 'primary',
                        ParcelStatus::DISPATCHED => 'success',
                        ParcelStatus::ARRIVED_UZB => 'warning',
                        ParcelStatus::PAYMENT_RECEIVED => 'success',
                        ParcelStatus::DELIVERED => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('china_uploaded_at')
                    ->label(__('filament.china_uploaded_at'))
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
                //
            ])
            ->recordActions([
                Action::make('mark_paid')
                    ->label("To'landi deb belgilash")
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading("To'lov to'landi deb belgilash")
                    ->modalDescription("Ushbu parsel uchun oldindan to'lov to'langanini tasdiqlaysizmi?")
                    ->action(function ($record) {
                        $record->update([
                            'prepayment_status' => PrepaymentStatus::PAID,
                        ]);

                        Notification::make()
                            ->title('Muvaffaqiyatli yangilandi')
                            ->body("Parsel uchun oldindan to'lov to'landi deb belgilandi")
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('mark_paid_bulk')
                        ->label("To'landi deb belgilash")
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading("To'lov to'landi deb belgilash")
                        ->modalDescription("Tanlangan parsellar uchun oldindan to'lov to'langanini tasdiqlaysizmi?")
                        ->deselectRecordsAfterCompletion()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $successCount = 0;

                            foreach ($records as $parcel) {
                                $parcel->update([
                                    'prepayment_status' => PrepaymentStatus::PAID,
                                ]);
                                $successCount++;
                            }

                            Notification::make()
                                ->title('Muvaffaqiyatli yangilandi')
                                ->body("{$successCount} ta parsel uchun oldindan to'lov to'landi deb belgilandi")
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
