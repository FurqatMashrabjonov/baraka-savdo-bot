<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Asosiy ma\'lumotlar')
                    ->inlineLabel()
                    ->schema([
                        TextEntry::make('phone')->label('Telefon raqami'),
                        TextEntry::make('full_name')->label('F.I.O'),
                        TextEntry::make('address')->label('Manzil'),
                        TextEntry::make('created_at')->label('Ro\'yxatga olingan vaqti')->dateTime(),
                        TextEntry::make('telegram.username')->label('Telegram foydalanuvchi nomi'),
                ]),
                Section::make('Telegram ma\'lumotlari')
                    ->inlineLabel()
                    ->schema([
                        TextEntry::make('telegram.chat_id')->label('Telegram Chat ID'),
                        TextEntry::make('telegram.username')->label('Telegram username'),
                        TextEntry::make('telegram.first_name')->label('Telegram ism'),
                        TextEntry::make('telegram.last_name')->label('Telegram familiyasi'),
                        TextEntry::make('telegram.created_at')->label('Ro\'yxatga olingan vaqti')->dateTime(),
                        TextEntry::make('telegram.channel_joined_at')->label('Kanalga qo\'shilgan vaqti')->dateTime(),
                    ]),
            ])
            ->columns(1)
            ->inlineLabel();
    }
}
