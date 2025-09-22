<?php

namespace App\Telegram\Conversations;

use App\Services\TelegramAuthService;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

class AskOtpConversation extends Conversation
{
    private function authService(): TelegramAuthService
    {
        return app(TelegramAuthService::class);
    }

    public function start(Nutgram $bot)
    {
        $bot->sendMessage(__('telegram.otp.enter'));
        $this->next('receiveOtp');
    }

    public function receiveOtp(Nutgram $bot)
    {
        $otp = $bot->message()->text;
        $client = $this->authService()->getClientByChatId($bot->chatId());

        if (! $client) {
            $this->end();
            AskPhoneConversation::begin($bot, true);

            return;
        }

        if (! $this->authService()->verifyOtp($client->phone, $otp)) {
            $bot->sendMessage(__('telegram.otp.wrong'));
            $this->next('receiveOtp');

            return;
        }

        // Check if user data is already complete
        if ($client->isComplete()) {
            $this->end();
            $bot->sendMessage(
                text: __('telegram.greeting'),
                reply_markup: \App\Telegram\Keyboards\ReplyKeyboards::home()
            );

            return;
        }

        // Continue to name collection
        $this->end();
        AskNameConversation::begin($bot);
    }
}
