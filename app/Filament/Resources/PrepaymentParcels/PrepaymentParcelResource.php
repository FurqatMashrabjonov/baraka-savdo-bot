<?php

namespace App\Filament\Resources\PrepaymentParcels;

use App\Filament\Resources\PrepaymentParcels\Pages\ListPrepaymentParcels;
use App\Filament\Resources\PrepaymentParcels\Tables\PrepaymentParcelsTable;
use App\Models\Parcel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrepaymentParcelResource extends Resource
{
    protected static ?string $model = Parcel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = null;

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return "Oldindan to'lov";
    }

    public static function getPluralModelLabel(): string
    {
        return "Oldindan to'lov";
    }

    public static function getModelLabel(): string
    {
        return "Oldindan to'lov";
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('prepayment_status', \App\Enums\PrepaymentStatus::PENDING->value)
            ->count();
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        // Only show for UZB kassirs
        $hasKassirUzbRole = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $user->id)
            ->where('model_has_roles.model_type', get_class($user))
            ->where('roles.name', 'kassir_uzb')
            ->exists();

        return $hasKassirUzbRole;
    }

    public static function table(Table $table): Table
    {
        return PrepaymentParcelsTable::configure($table)
            ->modifyQueryUsing(function ($query) {
                $query->with('client')
//                    ->whereNotNull('client_id')
                    ->where('prepayment_status', \App\Enums\PrepaymentStatus::PENDING->value)
                    ->orderBy('created_at', 'desc');

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
            'index' => ListPrepaymentParcels::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
