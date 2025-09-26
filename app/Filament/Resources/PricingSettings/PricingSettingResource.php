<?php

namespace App\Filament\Resources\PricingSettings;

use App\Filament\Resources\PricingSettings\Pages\CreatePricingSetting;
use App\Filament\Resources\PricingSettings\Pages\EditPricingSetting;
use App\Filament\Resources\PricingSettings\Pages\ManagePricingSetting;
use App\Filament\Resources\PricingSettings\Schemas\PricingSettingForm;
use App\Filament\Resources\PricingSettings\Tables\PricingSettingsTable;
use App\Models\PricingSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PricingSettingResource extends Resource
{
    protected static ?string $model = PricingSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $navigationLabel = 'Pricing Settings';

    protected static ?string $modelLabel = 'Pricing Setting';

    protected static ?string $pluralModelLabel = 'Pricing Settings';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return PricingSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PricingSettingsTable::configure($table);
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
            'index' => ManagePricingSetting::route('/'),
            'create' => CreatePricingSetting::route('/create'),
            'edit' => EditPricingSetting::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
}
