<?php

namespace App\Telegram\Conversations;

use App\Services\TelegramAuthService;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class AskPhoneConversation extends Conversation
{
    private function authService(): TelegramAuthService
    {
        return app(TelegramAuthService::class);
    }

    public function start(Nutgram $bot, bool $isWrongNumberEntry = false)
    {
        $message = $isWrongNumberEntry ? __('telegram.phone.wrong') : __('telegram.phone.enter');

        $bot->sendMessage(
            text: $message,
            parse_mode: ParseMode::HTML
        );
        $this->next('receivePhone');
    }

    public function receivePhone(Nutgram $bot)
    {
        $phone = $bot->message()->text;

        logger()->info('AskPhoneConversation - receivePhone called', [
            'phone' => $phone,
            'chat_id' => $bot->chatId(),
            'message_id' => $bot->message()->message_id,
        ]);

        if (! $this->authService()->isValidPhoneFormat($phone)) {
            logger()->info('AskPhoneConversation - invalid phone format');
            $this->start($bot, true);

            return;
        }

        logger()->info('AskPhoneConversation - phone format valid, processing...');

        // Get or create client with this phone
        $client = $this->authService()->getOrCreateClient($phone);
        logger()->info('AskPhoneConversation - client created/found', ['client_id' => $client->id]);

        // Link current telegram account to this client
        $this->authService()->linkTelegramAccountToClient($bot->chatId(), $client);
        logger()->info('AskPhoneConversation - telegram account linked');

        // Send OTP and get the code
        $otpCode = $this->authService()->sendOtp($phone);
        logger()->info('AskPhoneConversation - OTP generated', ['otp_code' => $otpCode]);

        // Send OTP code via Telegram using localization
        $bot->sendMessage(__('telegram.otp.your_code', ['code' => $otpCode]));
        logger()->info('AskPhoneConversation - OTP message sent');

        // Start OTP conversation
        $this->end();
        logger()->info('AskPhoneConversation - ended, starting OTP conversation');
        AskOtpConversation::begin($bot);
    }
}
