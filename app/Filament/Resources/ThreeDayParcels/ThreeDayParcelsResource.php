<?php

namespace App\Filament\Resources\ThreeDayParcels;

use App\Filament\Resources\Parcels\Tables\SimpleTable;
use App\Filament\Resources\ThreeDayParcels\Pages\ListThreeDayParcels;
use App\Models\Parcel;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

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
        return SimpleTable::configure($table)
            ->modifyQueryUsing(fn ($query) => $query
                ->whereNull('china_uploaded_at') // Not imported from China Excel
                ->whereNotNull('client_id') // Client has added track number
                ->where('created_at', '>=', now()->subDays(3)) // Within last 3 days
                ->with('client')
            );
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
