<?php

namespace App\Telegram\Conversations;

use App\Services\TelegramAuthService;
use App\Telegram\Keyboards\ReplyKeyboards;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class ClientDetailInfoConversation extends Conversation
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

        // Debug logging
        logger()->info('Received phone number', ['phone' => $phone, 'chat_id' => $bot->chatId()]);

        if (! $this->authService()->isValidPhoneFormat($phone)) {
            logger()->info('Invalid phone format', ['phone' => $phone]);
            $this->start($bot, true);

            return;
        }

        logger()->info('Phone format valid, creating client');

        // Get or create client with this phone
        $client = $this->authService()->getOrCreateClient($phone);
        logger()->info('Client created/found', ['client_id' => $client->id]);

        // Link current telegram account to this client
        $this->authService()->linkTelegramAccountToClient($bot->chatId(), $client);
        logger()->info('Telegram account linked to client');

        // Send OTP and get the code
        $otpCode = $this->authService()->sendOtp($phone);
        logger()->info('OTP generated', ['code' => $otpCode]);

        // Send OTP code via Telegram for testing
        $bot->sendMessage("🔐 Tasdiqlash kodi: {$otpCode}\n\nIltimos, ushbu kodni kiriting:");

        $this->askOtp($bot);
    }

    public function askOtp(Nutgram $bot)
    {
        $bot->sendMessage(__('telegram.otp.enter'));
        $this->next('receiveOtp');
    }

    public function receiveOtp(Nutgram $bot)
    {
        $otp = $bot->message()->text;
        $client = $this->authService()->getClientByChatId($bot->chatId());

        if (! $client) {
            $this->start($bot, true);

            return;
        }

        if (! $this->authService()->verifyOtp($client->phone, $otp)) {
            $bot->sendMessage(__('telegram.otp.wrong'));
            $this->next('receiveOtp');

            return;
        }

        // Check if user is already complete
        if ($client->isComplete()) {
            $this->end();
            $bot->sendMessage(
                text: __('telegram.greeting'),
                reply_markup: ReplyKeyboards::home()
            );

            return;
        }

        $this->askFullName($bot);
    }

    public function askFullName(Nutgram $bot)
    {
        $bot->sendMessage(
            text: __('telegram.full_name.enter'),
            parse_mode: ParseMode::HTML
        );
        $this->next('receiveFullName');
    }

    public function receiveFullName(Nutgram $bot)
    {
        $fullName = $bot->message()->text;

        if (! $this->authService()->isValidFullNameFormat($fullName)) {
            $bot->sendMessage(__('telegram.full_name.wrong'));
            $this->next('receiveFullName');

            return;
        }

        $client = $this->authService()->getClientByChatId($bot->chatId());
        $client->update(['full_name' => $fullName]);

        $this->askAddress($bot);
    }

    public function askAddress(Nutgram $bot)
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
        $client->update(['address' => $address]);

        $bot->sendMessage(
            __('telegram.registration.success')."\n\n"
            .'<blockquote>'
            .__('telegram.registration.details', [
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
