<?php

namespace App\Filament\Resources\Parcels;

use App\Filament\Resources\Parcels\Tables\ParcelsTable;
use App\Models\Parcel;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ParcelResource extends Resource
{
    protected static ?string $model = Parcel::class;

    protected static ?string $navigationLabel = 'Pochtalar';
    protected static string|null|\BackedEnum $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $pluralModelLabel = 'Pochtalar';

    protected static ?string $modelLabel = 'Pochta';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function table(Table $table): Table
    {
        return ParcelsTable::configure($table);
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
            'index' => Pages\ListParcels::route('/'),
            'edit' => Pages\EditParcel::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Disable create action for everyone
    }
}
