<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament.user_information'))
                    ->description(__('filament.user_information_description'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('filament.name'))
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('email')
                                    ->label(__('filament.email'))
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('password')
                                    ->label(__('filament.password'))
                                    ->password()
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->minLength(6)
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->dehydrateStateUsing(fn ($state) => bcrypt($state)),

                                Select::make('location')
                                    ->label(__('filament.location'))
                                    ->options([
                                        'china' => __('filament.china'),
                                        'uzbekistan' => __('filament.uzbekistan'),
                                    ])
                                    ->placeholder(__('filament.select_location')),
                            ]),
                    ]),

                Section::make(__('filament.roles_permissions'))
                    ->description(__('filament.roles_permissions_description'))
                    ->schema([
                        Select::make('roles')
                            ->label(__('filament.roles'))
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->options(Role::all()->pluck('name', 'id'))
                            ->preload(),
                    ]),
            ]);
    }
}
