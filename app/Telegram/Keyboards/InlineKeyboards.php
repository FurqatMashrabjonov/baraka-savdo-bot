<?php

namespace App\Telegram\Keyboards;

use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class InlineKeyboards
{
    public static function languages(): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('🇺🇿 '.__('telegram.lang.uz'), callback_data: 'set_lang:uz'),
                InlineKeyboardButton::make('🇷🇺 '.__('telegram.lang.ru'), callback_data: 'set_lang:ru'),
            );
    }

    public static function joinChannel(): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(__('telegram.join_channel'), url: 'https://t.me/'.config('telegram.channel_username')),
            )
            ->addRow(
                InlineKeyboardButton::make(__('telegram.confirm_join'), callback_data: 'check_joined'),
            );
    }

    public static function profile(): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(__('telegram.change_phone'), callback_data: 'change_phone'),
            )
            ->addRow(
                InlineKeyboardButton::make(__('telegram.logout'), callback_data: 'logout'),
            );
    }
}
