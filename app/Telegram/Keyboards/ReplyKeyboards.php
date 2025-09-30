<?php

namespace App\Telegram\Keyboards;

use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;

class ReplyKeyboards
{
    public static function login()
    {
        return ReplyKeyboardMarkup::make(resize_keyboard: true)->addRow(
            KeyboardButton::make(__('telegram.login')),
        );
    }

    public static function home($client = null)
    {
        // Create Order Payment button with WebApp if client is available
        return ReplyKeyboardMarkup::make(resize_keyboard: true)->addRow(
            KeyboardButton::make(__('telegram.track_number')),
            KeyboardButton::make(__('telegram.order_payment'), web_app: WebAppInfo::make(url('/telegram-web-app/dashboard?uid='.$client->uid))),
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

    /**
     * Create a standalone WebApp button for order payment
     */
    public static function orderPaymentWebApp($client)
    {
        if (! $client || ! $client->uid) {
            return null;
        }

        $webAppUrl = url('/telegram-web-app/dashboard?uid='.$client->uid);

        return ReplyKeyboardMarkup::make(resize_keyboard: true)->addRow(
            KeyboardButton::make(__('telegram.order_payment'), web_app: WebAppInfo::make($webAppUrl))
        )->addRow(
            KeyboardButton::make(__('telegram.back_to_home'))
        );
    }
}
