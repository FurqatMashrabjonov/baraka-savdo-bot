<?php

namespace App\Filament\Resources\ChinaAddresses;

use App\Filament\Resources\ChinaAddresses\Pages\CreateChinaAddress;
use App\Filament\Resources\ChinaAddresses\Pages\EditChinaAddress;
use App\Filament\Resources\ChinaAddresses\Pages\ManageChinaAddress;
use App\Filament\Resources\ChinaAddresses\Schemas\ChinaAddressForm;
use App\Filament\Resources\ChinaAddresses\Tables\ChinaAddressesTable;
use App\Models\ChinaAddress;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ChinaAddressResource extends Resource
{
    protected static ?string $model = ChinaAddress::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'China Addresses';

    protected static UnitEnum|string|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 4;

    public static function canViewAny(): bool
    {
        return auth()->user()->isAdmin();
    }

    public static function form(Schema $schema): Schema
    {
        return ChinaAddressForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChinaAddressesTable::configure($table);
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
            'index' => ManageChinaAddress::route('/'),
            'create' => CreateChinaAddress::route('/create'),
            'edit' => EditChinaAddress::route('/{record}/edit'),
        ];
    }
}
