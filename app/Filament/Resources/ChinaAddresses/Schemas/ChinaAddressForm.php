<?php

namespace App\Filament\Resources\ChinaAddresses\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ChinaAddressForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('China Address Configuration')
                    ->description('Set up the China address template that will be sent to users. Use {client_id} as a placeholder for the client ID.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Display Name')
                            ->required()
                            ->default('China Address')
                            ->helperText('Internal name for this address configuration'),

                        Textarea::make('address_template')
                            ->label('Address Template')
                            ->required()
                            ->rows(6)
                            ->placeholder("Example:\nWarehouse Address:\nBuilding A, Room 123\nShanghai, China\nRecipient ID: {client_id}")
                            ->helperText('Use {client_id} placeholder where you want the client ID to appear. For example: "CHINA ADDRESS ({client_id})"'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only one address can be active at a time. Activating this will deactivate others.'),

                        Placeholder::make('preview')
                            ->label('Preview (with example client ID 123)')
                            ->content(function ($get) {
                                $template = $get('address_template');
                                if ($template) {
                                    return str_replace('{client_id}', '123', $template);
                                }

                                return 'Enter template above to see preview';
                            })
                            ->extraAttributes(['class' => 'bg-gray-50 p-3 rounded border font-mono text-sm']),
                    ]),
            ]);
    }
}
