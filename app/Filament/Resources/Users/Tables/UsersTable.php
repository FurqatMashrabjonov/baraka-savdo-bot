<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\Parcel;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('filament.email'))
                    ->searchable()
                    ->copyable(),

                BadgeColumn::make('roles.name')
                    ->label(__('filament.roles'))
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->colors([
                        'success' => 'admin',
                        'warning' => 'kassir_china',
                        'info' => 'kassir_uzb',
                    ]),

                TextColumn::make('location')
                    ->label(__('filament.location'))
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'china' => __('filament.china'),
                        'uzbekistan' => __('filament.uzbekistan'),
                        default => $state ?: '—'
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'china' => 'warning',
                        'uzbekistan' => 'info',
                        default => 'gray'
                    }),

                TextColumn::make('parcels_processed')
                    ->label(__('filament.parcels_processed'))
                    ->getStateUsing(function ($record) {
                        // Count parcels where this user made payments
                        return Parcel::where('payment_processed_by', $record->id)->count();
                    })
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('total_payments')
                    ->label(__('filament.total_payments'))
                    ->getStateUsing(function ($record) {
                        // Sum total payment amounts processed by this user
                        $totalUsd = Parcel::where('payment_processed_by', $record->id)
                            ->sum('payment_amount_usd');
                        $totalUzs = Parcel::where('payment_processed_by', $record->id)
                            ->sum('payment_amount_uzs');

                        if ($totalUsd > 0 || $totalUzs > 0) {
                            return '$'.number_format($totalUsd, 2).' / '.number_format($totalUzs, 0).' UZS';
                        }

                        return '—';
                    })
                    ->badge()
                    ->color('info'),

                TextColumn::make('created_at')
                    ->label(__('filament.created_at'))
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('last_login')
                    ->label(__('filament.last_login'))
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label(__('filament.filter_by_role'))
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),

                SelectFilter::make('location')
                    ->label(__('filament.filter_by_location'))
                    ->options([
                        'china' => __('filament.china'),
                        'uzbekistan' => __('filament.uzbekistan'),
                    ])
                    ->multiple(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('view_stats')
                    ->label(__('filament.view_statistics'))
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->modalHeading(fn ($record) => __('filament.user_statistics').': '.$record->name)
                    ->modalWidth('7xl')
                    ->modalContent(fn ($record) => view('filament.user-stats-modal', ['user' => $record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->isAdmin()),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
