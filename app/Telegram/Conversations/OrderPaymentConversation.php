<?php

namespace App\Telegram\Conversations;

use App\Services\TelegramAuthService;
use App\Telegram\Keyboards\ReplyKeyboards;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;

class OrderPaymentConversation extends Conversation
{
    private function authService(): TelegramAuthService
    {
        return app(TelegramAuthService::class);
    }

    public function start(Nutgram $bot): void
    {
        $client = $this->authService()->getClientByChatId($bot->chatId());

        if (! $client) {
            $bot->sendMessage(
                text: __('telegram.error_try_again'),
                reply_markup: ReplyKeyboards::home()
            );
            $this->end();

            return;
        }

        $parcels = $client->parcels()->get();

        if ($parcels->isEmpty()) {
            $bot->sendMessage(
                text: __('telegram.no_parcels'),
                reply_markup: ReplyKeyboards::home($client)
            );
            $this->end();

            return;
        }

        $message = __('telegram.your_parcels')."\n\n";

        foreach ($parcels as $parcel) {
            $message .= '📦 <b>'.__('telegram.track_number_label').":</b> {$parcel->track_number}\n";
            $message .= '⚖️ <b>'.__('telegram.weight_label').":</b> {$parcel->getFormattedWeightAttribute()}\n";
            $message .= '💰 <b>'.__('telegram.delivery_cost_label').":</b> $6\n";
            $message .= '📊 <b>'.__('telegram.status_label').":</b> {$parcel->getStatusLabel()}\n\n";
        }

        // Determine the base URL - use ngrok URL if available, otherwise use app URL
        $baseUrl = config('app.ngrok_url') ?: config('app.url');
        $webAppUrl = $baseUrl.'/telegram-web-app/dashboard?uid='.$client->uid;

        // Create inline keyboard with Web App button
        $inlineKeyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    text: __('telegram.open_web_app'),
                    web_app: WebAppInfo::make(url: $webAppUrl)
                )
            );

        $bot->sendMessage(
            text: trim($message),
            parse_mode: ParseMode::HTML,
            reply_markup: $inlineKeyboard
        );

        $this->end();
    }
}
