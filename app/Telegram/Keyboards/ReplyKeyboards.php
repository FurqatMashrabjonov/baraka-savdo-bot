<?php

namespace App\Telegram\Keyboards;

use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;

class ReplyKeyboards
{
    public static function login()
    {
        return ReplyKeyboardMarkup::make(resize_keyboard: true)->addRow(
            KeyboardButton::make(__('telegram.login')),
        );
    }

    public static function home()
    {
        return ReplyKeyboardMarkup::make(resize_keyboard: true)->addRow(
            KeyboardButton::make(__('telegram.track_number')),
            KeyboardButton::make(__('telegram.order_payment')),
        )->addRow(
            KeyboardButton::make(__('telegram.delivery_address')),
            KeyboardButton::make(__('telegram.china_address')),
        )->addRow(
            KeyboardButton::make(__('telegram.call_center')),
            KeyboardButton::make(__('telegram.profile')),
        )->addRow(
            KeyboardButton::make(__('telegram.feedback')),
        );
    }

    public static function backToHome()
    {
        return ReplyKeyboardMarkup::make(resize_keyboard: true)->addRow(
            KeyboardButton::make(__('telegram.back_to_home')),
        );
    }
}
