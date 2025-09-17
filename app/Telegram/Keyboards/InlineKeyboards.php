<?php

namespace App\Telegram\Keyboards;

use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class InlineKeyboards
{
    public static function languages()
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('🇺🇿O\'zbekcha', callback_data: 'set_lang:uz'),
                InlineKeyboardButton::make('🇷🇺Русский', callback_data: 'set_lang:ru'),
            );
    }

    public static function joinChannel()
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('Kanalga obuna bo\'lish', url: 'https://t.me/' . config('telegram.channel_username')),
            )
            ->addRow(
                InlineKeyboardButton::make('Tasdiqlash✅', callback_data: 'check_joined'),
            );
    }
}
