<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('full_name')
                    ->required(),
                TextInput::make('address')
                    ->required(),
            ]);
    }
}
