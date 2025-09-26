<?php

namespace App\Filament\Resources\Parcels\Tables;

use App\Enums\ParcelStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
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

                TextColumn::make('payment_status')
                    ->label(__('filament.payment_status'))
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => __('filament.payment_pending'),
                        'paid' => __('filament.payment_paid'),
                        'cancelled' => __('filament.payment_cancelled'),
                        null => __('filament.payment_not_started'),
                        default => $state ?: __('filament.payment_not_started'),
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        null => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('payment_amount_usd')
                    ->label(__('filament.payment_amount_usd'))
                    ->formatStateUsing(fn ($state) => $state > 0 ? '$'.number_format($state, 2) : '—')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('payment_amount_uzs')
                    ->label(__('filament.payment_amount_uzs'))
                    ->formatStateUsing(fn ($state) => $state > 0 ? number_format($state, 0, '.', ' ').' UZS' : '—')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('payment_date')
                    ->label(__('filament.payment_date'))
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

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

                SelectFilter::make('client')
                    ->label(__('filament.filter_by_client'))
                    ->relationship('client', 'full_name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->optionsLimit(50),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn () => ! auth()->user()->isKassir()),

                Action::make('process_payment')
                    ->label(__('filament.process_payment'))
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->visible(fn ($record) => $record->isReadyForPayment())
                    ->form([
                        Grid::make(2)
                            ->schema([
                                // Left Column: What Customer Should Pay
                                Section::make(__('filament.delivery_cost'))
                                    ->description(__('filament.delivery_cost_explanation'))
                                    ->icon('heroicon-o-calculator')
                                    ->schema([
                                        TextInput::make('parcel_info')
                                            ->label(__('filament.parcel_info'))
                                            ->disabled()
                                            ->default(function ($record) {
                                                return $record->track_number.' ('.($record->weight ?? '0').' kg)';
                                            }),

                                        TextInput::make('delivery_rate')
                                            ->label(__('filament.delivery_rate'))
                                            ->disabled()
                                            ->prefix('$')
                                            ->suffix('per kg')
                                            ->default(function () {
                                                $pricing = \App\Models\PricingSetting::getActivePricing();

                                                return $pricing ? number_format($pricing->price_per_kg_usd, 2) : '0.00';
                                            }),

                                        TextInput::make('calculation_formula')
                                            ->label(__('filament.calculation'))
                                            ->disabled()
                                            ->default(function ($record) {
                                                $pricing = \App\Models\PricingSetting::getActivePricing();
                                                if (! $pricing || ! $record->weight) {
                                                    return '0 kg × $0.00 = $0.00';
                                                }
                                                $calculatedWeight = $record->getCalculatedWeight();
                                                $expected = $pricing->calculateExpectedAmountUsd($calculatedWeight);

                                                return $record->weight.' kg (→ '.$calculatedWeight.' kg) × $'.number_format($pricing->price_per_kg_usd, 2).' = $'.number_format($expected, 2);
                                            }),

                                        TextInput::make('total_cost_usd')
                                            ->label(__('filament.total_cost_usd'))
                                            ->disabled()
                                            ->prefix('$')
                                            ->extraAttributes(['class' => 'text-xl font-bold text-blue-600'])
                                            ->default(function ($record) {
                                                $pricing = \App\Models\PricingSetting::getActivePricing();
                                                if (! $pricing || ! $record->weight) {
                                                    return '0.00';
                                                }

                                                return number_format($pricing->calculateExpectedAmountUsd($record->getCalculatedWeight()), 2);
                                            }),

                                        TextInput::make('total_cost_uzs')
                                            ->label(__('filament.total_cost_uzs'))
                                            ->disabled()
                                            ->suffix('UZS')
                                            ->extraAttributes(['class' => 'text-xl font-bold text-blue-600'])
                                            ->default(function ($record) {
                                                $pricing = \App\Models\PricingSetting::getActivePricing();
                                                if (! $pricing || ! $record->weight) {
                                                    return '0';
                                                }

                                                return number_format($pricing->calculateExpectedAmountUzs($record->getCalculatedWeight()), 0, '.', ' ');
                                            }),

                                        TextInput::make('exchange_rate_info')
                                            ->label(__('filament.exchange_rate_used'))
                                            ->disabled()
                                            ->suffix('UZS per $1')
                                            ->default(fn () => number_format(\App\Models\CurrencyExchangeRate::getCurrentUsdToUzsRate(), 0)),
                                    ]),

                                // Right Column: What Customer Actually Paid
                                Section::make(__('filament.payment_received'))
                                    ->description(__('filament.payment_received_explanation'))
                                    ->icon('heroicon-o-banknotes')
                                    ->schema([
                                        TextInput::make('usd_amount')
                                            ->label(__('filament.usd_received'))
                                            ->placeholder('0.00')
                                            ->numeric()
                                            ->step(0.01)
                                            ->minValue(0)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get, $record) {
                                                $usdAmount = (float) $state ?: 0;
                                                $uzsAmount = (float) $get('uzs_amount') ?: 0;
                                                $rate = \App\Models\CurrencyExchangeRate::getCurrentUsdToUzsRate();

                                                // Calculate total in UZS
                                                $totalUzs = ($usdAmount * $rate) + $uzsAmount;
                                                $set('payment_total_uzs', number_format($totalUzs, 0, '.', ' '));

                                                // Calculate remaining amount needed
                                                $pricing = \App\Models\PricingSetting::getActivePricing();
                                                if ($pricing && $record->weight) {
                                                    $expectedTotal = $pricing->calculateExpectedAmountUzs($record->getCalculatedWeight());
                                                    $remaining = max(0, $expectedTotal - $totalUzs);
                                                    $set('remaining_amount', number_format($remaining, 0, '.', ' '));

                                                    // Validation status
                                                    if ($totalUzs >= $expectedTotal) {
                                                        $set('payment_status', '✅ '.__('filament.payment_sufficient'));
                                                    } else {
                                                        $set('payment_status', '⚠️ '.__('filament.payment_insufficient'));
                                                    }
                                                }
                                            }),

                                        TextInput::make('uzs_amount')
                                            ->label(__('filament.uzs_received'))
                                            ->placeholder('0')
                                            ->numeric()
                                            ->step(1000)
                                            ->minValue(0)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get, $record) {
                                                $usdAmount = (float) $get('usd_amount') ?: 0;
                                                $uzsAmount = (float) $state ?: 0;
                                                $rate = \App\Models\CurrencyExchangeRate::getCurrentUsdToUzsRate();

                                                // Calculate total in UZS
                                                $totalUzs = ($usdAmount * $rate) + $uzsAmount;
                                                $set('payment_total_uzs', number_format($totalUzs, 0, '.', ' '));

                                                // Calculate remaining amount needed
                                                $pricing = \App\Models\PricingSetting::getActivePricing();
                                                if ($pricing && $record->weight) {
                                                    $expectedTotal = $pricing->calculateExpectedAmountUzs($record->getCalculatedWeight());
                                                    $remaining = max(0, $expectedTotal - $totalUzs);
                                                    $set('remaining_amount', number_format($remaining, 0, '.', ' '));

                                                    // Validation status
                                                    if ($totalUzs >= $expectedTotal) {
                                                        $set('payment_status', '✅ '.__('filament.payment_sufficient'));
                                                    } else {
                                                        $set('payment_status', '⚠️ '.__('filament.payment_insufficient'));
                                                    }
                                                }
                                            }),

                                        TextInput::make('payment_total_uzs')
                                            ->label(__('filament.total_payment_uzs'))
                                            ->disabled()
                                            ->suffix('UZS')
                                            ->extraAttributes(['class' => 'text-lg font-semibold text-green-600'])
                                            ->default('0'),

                                        TextInput::make('remaining_amount')
                                            ->label(__('filament.remaining_needed'))
                                            ->disabled()
                                            ->suffix('UZS')
                                            ->extraAttributes(['class' => 'text-lg font-semibold text-red-600'])
                                            ->default('0'),

                                        TextInput::make('payment_status')
                                            ->label(__('filament.payment_validation'))
                                            ->disabled()
                                            ->extraAttributes(['class' => 'text-lg font-bold'])
                                            ->default('⚠️ '.__('filament.enter_payment_amounts')),

                                        Textarea::make('payment_notes')
                                            ->label(__('filament.payment_notes'))
                                            ->placeholder(__('filament.payment_notes_placeholder'))
                                            ->rows(2)
                                            ->maxLength(300),
                                    ]),
                            ]),
                    ])
                    ->action(function (array $data, $record) {
                        $usdAmount = (float) ($data['usd_amount'] ?? 0);
                        $uzsAmount = (float) ($data['uzs_amount'] ?? 0);

                        // Basic validation: at least one payment amount must be entered
                        if ($usdAmount <= 0 && $uzsAmount <= 0) {
                            Notification::make()
                                ->title(__('filament.payment_error'))
                                ->body(__('filament.enter_at_least_one_amount'))
                                ->danger()
                                ->send();

                            return;
                        }

                        // Get expected amounts for validation
                        $pricing = \App\Models\PricingSetting::getActivePricing();
                        if (! $pricing || ! $record->weight) {
                            Notification::make()
                                ->title(__('filament.payment_error'))
                                ->body(__('filament.pricing_not_available'))
                                ->danger()
                                ->send();

                            return;
                        }

                        $expectedUzs = $pricing->calculateExpectedAmountUzs($record->getCalculatedWeight());
                        $rate = \App\Models\CurrencyExchangeRate::getCurrentUsdToUzsRate();
                        $totalReceivedUzs = ($usdAmount * $rate) + $uzsAmount;

                        // Validation: Total received must equal or exceed expected delivery cost
                        if ($totalReceivedUzs < $expectedUzs) {
                            $shortfall = $expectedUzs - $totalReceivedUzs;
                            Notification::make()
                                ->title(__('filament.payment_insufficient'))
                                ->body(__('filament.payment_shortfall', [
                                    'expected' => number_format($expectedUzs, 0, '.', ' '),
                                    'received' => number_format($totalReceivedUzs, 0, '.', ' '),
                                    'shortfall' => number_format($shortfall, 0, '.', ' '),
                                ]))
                                ->warning()
                                ->persistent()
                                ->send();

                            return;
                        }

                        // Process the payment
                        $success = $record->processPayment(
                            $usdAmount,
                            $uzsAmount,
                            'offline' // Default to offline payment
                        );

                        if ($success) {
                            if (! empty($data['payment_notes'])) {
                                $record->update(['payment_notes' => $data['payment_notes']]);
                            }

                            $overpayment = $totalReceivedUzs - $expectedUzs;
                            $message = __('filament.payment_processed_successfully');
                            if ($overpayment > 0) {
                                $message .= ' '.__('filament.overpayment_notice', [
                                    'amount' => number_format($overpayment, 0, '.', ' '),
                                ]);
                            }

                            Notification::make()
                                ->title(__('filament.payment_success'))
                                ->body($message)
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title(__('filament.payment_error'))
                                ->body(__('filament.payment_processing_failed'))
                                ->danger()
                                ->send();
                        }
                    })
                    ->modalSubmitActionLabel(__('filament.process_payment'))
                    ->modalHeading(__('filament.process_payment_modal_title'))
                    ->modalWidth('4xl'),
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
                    BulkAction::make('bulk_payment')
                        ->label(__('filament.bulk_payment'))
                        ->icon('heroicon-o-currency-dollar')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading(__('filament.bulk_payment_modal_title'))
                        ->modalDescription(__('filament.bulk_payment_modal_description'))
                        ->modalWidth('5xl')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data) {
                            $successCount = 0;
                            $errorCount = 0;
                            $pricing = \App\Models\PricingSetting::getActivePricing();

                            if (! $pricing) {
                                Notification::make()
                                    ->title(__('filament.payment_error'))
                                    ->body(__('filament.pricing_not_available'))
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $usdAmount = (float) ($data['total_usd_amount'] ?? 0);
                            $uzsAmount = (float) ($data['total_uzs_amount'] ?? 0);
                            $paymentType = 'offline'; // Default to offline payment
                            $notes = $data['payment_notes'] ?? '';

                            // Calculate total expected amount
                            $totalExpectedUsd = 0;
                            $validParcels = $records->filter(function ($parcel) use (&$totalExpectedUsd, $pricing) {
                                if ($parcel->isReadyForPayment() && $parcel->weight) {
                                    $totalExpectedUsd += $pricing->calculateExpectedAmountUsd($parcel->getCalculatedWeight());

                                    return true;
                                }

                                return false;
                            });

                            if ($validParcels->isEmpty()) {
                                Notification::make()
                                    ->title(__('filament.payment_error'))
                                    ->body(__('filament.no_parcels_ready_for_payment'))
                                    ->warning()
                                    ->send();

                                return;
                            }

                            $totalExpectedUzs = \App\Models\CurrencyExchangeRate::convertUsdToUzs($totalExpectedUsd);
                            $rate = \App\Models\CurrencyExchangeRate::getCurrentUsdToUzsRate();
                            $totalReceivedUzs = ($usdAmount * $rate) + $uzsAmount;

                            // Validate total payment
                            if ($totalReceivedUzs < $totalExpectedUzs) {
                                $shortfall = $totalExpectedUzs - $totalReceivedUzs;
                                Notification::make()
                                    ->title(__('filament.payment_insufficient'))
                                    ->body(__('filament.bulk_payment_shortfall', [
                                        'expected' => number_format($totalExpectedUzs, 0, '.', ' '),
                                        'received' => number_format($totalReceivedUzs, 0, '.', ' '),
                                        'shortfall' => number_format($shortfall, 0, '.', ' '),
                                    ]))
                                    ->warning()
                                    ->persistent()
                                    ->send();

                                return;
                            }

                            // Distribute payment proportionally among parcels
                            foreach ($validParcels as $parcel) {
                                $parcelExpectedUsd = $pricing->calculateExpectedAmountUsd($parcel->getCalculatedWeight());
                                $proportion = $parcelExpectedUsd / $totalExpectedUsd;

                                $parcelUsdAmount = $usdAmount * $proportion;
                                $parcelUzsAmount = $uzsAmount * $proportion;

                                $success = $parcel->processPayment($parcelUsdAmount, $parcelUzsAmount, $paymentType);

                                if ($success) {
                                    if ($notes) {
                                        $parcel->update(['payment_notes' => $notes.' (Bulk payment)']);
                                    }
                                    $successCount++;
                                } else {
                                    $errorCount++;
                                }
                            }

                            // Show results
                            if ($successCount > 0) {
                                $overpayment = $totalReceivedUzs - $totalExpectedUzs;
                                $message = __('filament.bulk_payment_success', ['count' => $successCount]);
                                if ($overpayment > 0) {
                                    $message .= ' '.__('filament.overpayment_notice', [
                                        'amount' => number_format($overpayment, 0, '.', ' '),
                                    ]);
                                }

                                Notification::make()
                                    ->title(__('filament.payment_success'))
                                    ->body($message)
                                    ->success()
                                    ->send();
                            }

                            if ($errorCount > 0) {
                                Notification::make()
                                    ->title(__('filament.partial_payment_error'))
                                    ->body(__('filament.bulk_payment_errors', ['count' => $errorCount]))
                                    ->warning()
                                    ->send();
                            }
                        })
                        ->form(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $pricing = \App\Models\PricingSetting::getActivePricing();

                            // Calculate total expected amounts for validation
                            $totalExpectedUsd = 0;
                            foreach ($records as $parcel) {
                                if ($parcel->isReadyForPayment() && $parcel->weight && $pricing) {
                                    $totalExpectedUsd += $pricing->calculateExpectedAmountUsd($parcel->getCalculatedWeight());
                                }
                            }
                            $totalExpectedUzs = \App\Models\CurrencyExchangeRate::convertUsdToUzs($totalExpectedUsd);

                            return [
                                Section::make(__('filament.selected_parcels_details'))
                                    ->description(__('filament.review_parcels_before_payment').' ('.count($records).' '.__('filament.parcels').')')
                                    ->icon('heroicon-o-cube')
                                    ->collapsible()
                                    ->collapsed(false)
                                    ->collapsed(false)
                                    ->schema([
                                        Repeater::make('parcels_details')
                                            ->label('')
                                            ->default(function () use ($records, $pricing) {
                                                $parcelsData = [];
                                                foreach ($records as $parcel) {
                                                    $isReady = $parcel->isReadyForPayment() && $parcel->weight && $pricing;
                                                    $expectedUsd = $isReady ? $pricing->calculateExpectedAmountUsd($parcel->getCalculatedWeight()) : 0;
                                                    $expectedUzs = $isReady ? \App\Models\CurrencyExchangeRate::convertUsdToUzs($expectedUsd) : 0;

                                                    $parcelsData[] = [
                                                        'track_number' => $parcel->track_number,
                                                        'client_name' => $parcel->client ? $parcel->client->full_name : __('filament.unassigned'),
                                                        'phone' => $parcel->client ? $parcel->client->phone : '-',
                                                        'actual_weight' => $parcel->weight ? number_format($parcel->weight, 2) : '0',
                                                        'calculated_weight' => $parcel->weight ? number_format($parcel->getCalculatedWeight(), 1) : '0',
                                                        'status' => $parcel->getStatusLabel(),
                                                        'is_ready' => $isReady,
                                                        'expected_usd' => $expectedUsd,
                                                        'expected_uzs' => $expectedUzs,
                                                        'ready_status' => $isReady ? '✅ '.__('filament.ready_for_payment') : '⚠️ '.__('filament.not_ready_for_payment'),
                                                    ];
                                                }

                                                return $parcelsData;
                                            })
                                            ->disabled()
                                            ->addActionLabel('')
                                            ->deletable(false)
                                            ->reorderable(false)
                                            ->collapsible()
                                            ->collapsed(false)
                                            ->itemLabel(function (array $state): ?string {
                                                if (empty($state) || ! isset($state['track_number'])) {
                                                    return $state['track_number'] ?? __('filament.parcel_details');
                                                }
                                                $icon = ($state['is_ready'] ?? false) ? '✅' : '⚠️';
                                                $clientName = $state['client_name'] ?? __('filament.unassigned');

                                                return $icon.' '.$state['track_number'].' - '.$clientName;
                                            })
                                            ->schema([
                                                Grid::make(4)
                                                    ->schema([
                                                        TextInput::make('track_number')
                                                            ->label(__('filament.parcel_track_number'))
                                                            ->disabled(),

                                                        TextInput::make('client_name')
                                                            ->label(__('filament.client_name'))
                                                            ->disabled(),

                                                        TextInput::make('calculated_weight')
                                                            ->label(__('filament.calculated_weight'))
                                                            ->disabled()
                                                            ->suffix('kg')
                                                            ->extraAttributes(['class' => 'font-bold text-blue-600']),

                                                        TextInput::make('ready_status')
                                                            ->label(__('filament.status'))
                                                            ->disabled()
                                                            ->extraAttributes(function ($get) {
                                                                $isReady = $get('is_ready') ?? false;

                                                                return $isReady ?
                                                                    ['class' => 'font-semibold text-green-600'] :
                                                                    ['class' => 'font-semibold text-yellow-600'];
                                                            }),
                                                    ]),

                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('expected_usd')
                                                            ->label(__('filament.expected_cost_usd'))
                                                            ->disabled()
                                                            ->prefix('$')
                                                            ->formatStateUsing(fn ($state) => number_format($state, 2))
                                                            ->extraAttributes(['class' => 'font-bold text-green-600']),

                                                        TextInput::make('expected_uzs')
                                                            ->label(__('filament.expected_cost_uzs'))
                                                            ->disabled()
                                                            ->suffix('UZS')
                                                            ->formatStateUsing(fn ($state) => number_format($state, 0, '.', ' '))
                                                            ->extraAttributes(['class' => 'font-bold text-green-600']),
                                                    ]),
                                            ]),
                                    ]),

                                Section::make(__('filament.payment_summary'))
                                    ->description(__('filament.total_amounts_to_collect'))
                                    ->icon('heroicon-o-banknotes')
                                    ->compact()
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('total_expected_usd_display')
                                                    ->label(__('filament.total_expected_usd'))
                                                    ->disabled()
                                                    ->prefix('$')
                                                    ->default(number_format($totalExpectedUsd, 2))
                                                    ->extraAttributes(['class' => 'text-xl font-bold text-green-600']),

                                                TextInput::make('total_expected_uzs_display')
                                                    ->label(__('filament.total_expected_uzs'))
                                                    ->disabled()
                                                    ->suffix('UZS')
                                                    ->default(number_format($totalExpectedUzs, 0, '.', ' '))
                                                    ->extraAttributes(['class' => 'text-xl font-bold text-green-600']),
                                            ]),
                                    ]),

                                Section::make(__('filament.payment_details'))
                                    ->description(__('filament.enter_total_payment_received'))
                                    ->icon('heroicon-o-banknotes')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('total_usd_amount')
                                                    ->label(__('filament.total_usd_received'))
                                                    ->placeholder('0.00')
                                                    ->numeric()
                                                    ->step(0.01)
                                                    ->minValue(0)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($state, callable $set, callable $get) use ($totalExpectedUzs) {
                                                        $usdAmount = (float) $state ?: 0;
                                                        $uzsAmount = (float) $get('total_uzs_amount') ?: 0;
                                                        $rate = \App\Models\CurrencyExchangeRate::getCurrentUsdToUzsRate();

                                                        $totalUzs = ($usdAmount * $rate) + $uzsAmount;
                                                        $set('payment_total_display', number_format($totalUzs, 0, '.', ' '));

                                                        $remaining = max(0, $totalExpectedUzs - $totalUzs);
                                                        $set('remaining_display', number_format($remaining, 0, '.', ' '));

                                                        if ($totalUzs >= $totalExpectedUzs) {
                                                            $set('payment_status_display', '✅ '.__('filament.payment_sufficient'));
                                                        } else {
                                                            $set('payment_status_display', '⚠️ '.__('filament.payment_insufficient'));
                                                        }
                                                    }),

                                                TextInput::make('total_uzs_amount')
                                                    ->label(__('filament.total_uzs_received'))
                                                    ->placeholder('0')
                                                    ->numeric()
                                                    ->step(1000)
                                                    ->minValue(0)
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function ($state, callable $set, callable $get) use ($totalExpectedUzs) {
                                                        $usdAmount = (float) $get('total_usd_amount') ?: 0;
                                                        $uzsAmount = (float) $state ?: 0;
                                                        $rate = \App\Models\CurrencyExchangeRate::getCurrentUsdToUzsRate();

                                                        $totalUzs = ($usdAmount * $rate) + $uzsAmount;
                                                        $set('payment_total_display', number_format($totalUzs, 0, '.', ' '));

                                                        $remaining = max(0, $totalExpectedUzs - $totalUzs);
                                                        $set('remaining_display', number_format($remaining, 0, '.', ' '));

                                                        if ($totalUzs >= $totalExpectedUzs) {
                                                            $set('payment_status_display', '✅ '.__('filament.payment_sufficient'));
                                                        } else {
                                                            $set('payment_status_display', '⚠️ '.__('filament.payment_insufficient'));
                                                        }
                                                    }),
                                            ]),

                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('payment_total_display')
                                                    ->label(__('filament.total_payment_uzs'))
                                                    ->disabled()
                                                    ->suffix('UZS')
                                                    ->extraAttributes(['class' => 'text-lg font-semibold text-green-600'])
                                                    ->default('0'),

                                                TextInput::make('remaining_display')
                                                    ->label(__('filament.remaining_needed'))
                                                    ->disabled()
                                                    ->suffix('UZS')
                                                    ->extraAttributes(['class' => 'text-lg font-semibold text-red-600'])
                                                    ->default(number_format($totalExpectedUzs, 0, '.', ' ')),

                                                TextInput::make('payment_status_display')
                                                    ->label(__('filament.payment_validation'))
                                                    ->disabled()
                                                    ->extraAttributes(['class' => 'text-lg font-bold'])
                                                    ->default('⚠️ '.__('filament.enter_payment_amounts')),
                                            ]),

                                        Textarea::make('payment_notes')
                                            ->label(__('filament.payment_notes'))
                                            ->placeholder(__('filament.bulk_payment_notes_placeholder'))
                                            ->rows(2)
                                            ->maxLength(300),
                                    ]),
                            ];
                        }),

                    DeleteBulkAction::make()
                        ->visible(fn () => ! auth()->user()->isKassir()),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([25, 50, 100]);
    }
}
