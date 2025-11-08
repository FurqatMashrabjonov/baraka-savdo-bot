<?php

namespace App\Filament\Resources\Parcels;

use App\Enums\ParcelStatus;
use App\Filament\Resources\Parcels\Tables\ParcelsTable;
use App\Models\Parcel;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ParcelResource extends Resource
{
    protected static ?string $model = Parcel::class;

    protected static ?string $navigationLabel = null;

    public static function getNavigationLabel(): string
    {
        return __('filament.parcels');
    }

    protected static string|null|\BackedEnum $navigationIcon = Heroicon::OutlinedArchiveBox;

    public static function getPluralModelLabel(): string
    {
        return __('filament.parcels');
    }

    public static function getModelLabel(): string
    {
        return __('filament.parcel');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function table(Table $table): Table
    {
        return ParcelsTable::configure($table)
            ->modifyQueryUsing(function ($query) {
                $query->with('client');

                $user = Auth::user();
                if ($user) {
                    // For China kassirs, show only parcels with china_uploaded_at not null
                    $hasKassirChinaRole = DB::table('model_has_roles')
                        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                        ->where('model_has_roles.model_id', $user->id)
                        ->where('model_has_roles.model_type', get_class($user))
                        ->where('roles.name', 'kassir_china')
                        ->exists();

                    if ($hasKassirChinaRole) {
                        $query->whereNotNull('china_uploaded_at')
                            ->whereNull('uzb_uploaded_at')
                            ->orderBy('china_uploaded_at', 'desc');
                    }

                    // For UZB kassirs, show only parcels with client assigned and status IN_WAREHOUSE
                    $hasKassirUzbRole = DB::table('model_has_roles')
                        ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                        ->where('model_has_roles.model_id', $user->id)
                        ->where('model_has_roles.model_type', get_class($user))
                        ->where('roles.name', 'kassir_uzb')
                        ->exists();

                    if ($hasKassirUzbRole) {
                        $query->whereNotNull('client_id')
                            ->whereIn('status', [
                                \App\Enums\ParcelStatus::ARRIVED_CHINA->value,
                                \App\Enums\ParcelStatus::IN_WAREHOUSE->value,
                                \App\Enums\ParcelStatus::DISPATCHED->value,
                                ParcelStatus::ARRIVED_UZB->value,
                                ParcelStatus::PAYMENT_RECEIVED->value,
                            ])
                            ->orderBy('created_at', 'desc');
                    }
                }

                return $query;
            });
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
