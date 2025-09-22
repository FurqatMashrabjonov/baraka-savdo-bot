<?php

namespace App\Telegram\Conversations;

use App\Services\TelegramAuthService;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class AskNameConversation extends Conversation
{
    private function authService(): TelegramAuthService
    {
        return app(TelegramAuthService::class);
    }

    public function start(Nutgram $bot)
    {
        $bot->sendMessage(
            text: __('telegram.full_name.enter'),
            parse_mode: ParseMode::HTML
        );
        $this->next('receiveName');
    }

    public function receiveName(Nutgram $bot)
    {
        $fullName = $bot->message()->text;

        if (! $this->authService()->isValidFullNameFormat($fullName)) {
            $bot->sendMessage(__('telegram.full_name.wrong'));
            $this->next('receiveName');

            return;
        }

        $client = $this->authService()->getClientByChatId($bot->chatId());

        if (! $client) {
            $this->end();
            AskPhoneConversation::begin($bot);

            return;
        }

        $client->update(['full_name' => $fullName]);

        // Continue to address collection
        $this->end();
        AskAddressConversation::begin($bot);
    }
}
