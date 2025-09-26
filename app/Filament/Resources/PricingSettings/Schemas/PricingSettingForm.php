<?php

namespace App\Filament\Resources\PricingSettings\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PricingSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pricing Information')
                    ->description('Set the default pricing per kilogram in USD. UZS equivalent will be calculated using current exchange rates.')
                    ->schema([
                        TextInput::make('price_per_kg_usd')
                            ->label('Price per KG (USD)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->prefix('$')
                            ->minValue(0)
                            ->default(0)
                            ->helperText('UZS pricing will be calculated automatically using current exchange rates'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Only one pricing setting can be active at a time')
                            ->default(true),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->placeholder('Optional notes about this pricing setting...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
