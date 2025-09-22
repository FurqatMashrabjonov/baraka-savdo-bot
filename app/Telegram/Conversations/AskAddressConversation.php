<?php

namespace App\Telegram\Conversations;

use App\Services\TelegramAuthService;
use App\Telegram\Keyboards\ReplyKeyboards;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class AskAddressConversation extends Conversation
{
    private function authService(): TelegramAuthService
    {
        return app(TelegramAuthService::class);
    }

    public function start(Nutgram $bot)
    {
        $bot->sendMessage(
            text: __('telegram.address.enter'),
            parse_mode: ParseMode::HTML
        );
        $this->next('receiveAddress');
    }

    public function receiveAddress(Nutgram $bot)
    {
        $address = $bot->message()->text;

        $client = $this->authService()->getClientByChatId($bot->chatId());

        if (! $client) {
            $this->end();
            AskPhoneConversation::begin($bot);

            return;
        }

        $client->update(['address' => $address]);

        // Registration complete - show success message
        $bot->sendMessage(
            __('telegram.registration.success')."\n\n".
            '<blockquote>'.
            __('telegram.registration.details', [
                'id' => $client->id,
                'name' => $client->full_name,
                'phone' => $client->phone,
                'address' => $client->address,
                'date' => $client->created_at->format('d.m.Y H:i'),
            ]).
            '</blockquote>',
            parse_mode: ParseMode::HTML,
            reply_markup: ReplyKeyboards::home()
        );

        $this->end();
    }
}
