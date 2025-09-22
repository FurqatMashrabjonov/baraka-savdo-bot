<?php

namespace App\Filament\Resources\Parcels\Schemas;

use App\Enums\ParcelStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ParcelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('client_id')
                    ->relationship('client', 'id')
                    ->searchable()
                    ->label('Mijoz')
                    ->required(),
                TextInput::make('track_number')
                    ->label('Trek raqami')
                    ->required(),
                TextInput::make('weight')
                    ->label('Og\'irligi (kg)')
                    ->numeric(),
                Toggle::make('is_banned')
                    ->label('Ta\'qiqlangan')
                    ->required(),
                DateTimePicker::make('china_uploaded_at')
                    ->label('Xitoyga kelgan vaqti'),
                DateTimePicker::make('uzb_uploaded_at')
                    ->label('O\'zbekistonga kelgan vaqti'),
                Select::make('status')
                    ->label('Holati')
                    ->options(ParcelStatus::class)
                    ->default('created')
                    ->required(),
            ]);
    }
}
