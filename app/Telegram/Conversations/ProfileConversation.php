<?php

namespace App\Telegram\Conversations;

use App\Models\Client;
use App\Services\TelegramAuthService;
use App\Telegram\Keyboards\InlineKeyboards;
use App\Telegram\Keyboards\ReplyKeyboards;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class ProfileConversation extends Conversation
{
    private function authService(): TelegramAuthService
    {
        return app(TelegramAuthService::class);
    }

    public function start(Nutgram $bot)
    {
        $client = $this->authService()->getClientByChatId($bot->chatId());

        if (! $client) {
            $bot->sendMessage(__('telegram.login'), reply_markup: ReplyKeyboards::login());

            return;
        }

        $bot->sendMessage('<blockquote>'
            .__('telegram.registration.details', [
                'id' => $client->id,
                'name' => $client->full_name,
                'phone' => $client->phone,
                'address' => $client->address,
                'date' => $client->created_at->format('d.m.Y H:i'),
            ])
            .'</blockquote>',
            parse_mode: ParseMode::HTML,
            reply_markup: InlineKeyboards::profile()
        );

        $this->next('receiveCallback');
    }

    public function receiveCallback(Nutgram $bot)
    {
        $data = $bot->callbackQuery()->data;

        match ($data) {
            'change_phone' => $this->handleChangePhone($bot),
            'logout' => $this->handleLogout($bot),
            default => $this->handleUnknownCommand($bot)
        };
    }

    private function handleChangePhone(Nutgram $bot): void
    {
        $bot->sendMessage(
            text: __('telegram.phone.enter'),
            parse_mode: ParseMode::HTML
        );
        $this->next('receivePhone');
    }

    private function handleLogout(Nutgram $bot): void
    {
        $this->authService()->logoutTelegramAccount($bot->chatId());
        $bot->callbackQuery()->message->delete();
        $bot->sendMessage(
            text: __('telegram.logged_out'),
            reply_markup: ReplyKeyboards::login()
        );
        $this->end();
    }

    private function handleUnknownCommand(Nutgram $bot): void
    {
        $bot->sendMessage(__('telegram.unknown_command'));
        $this->end();
    }

    public function receivePhone(Nutgram $bot)
    {
        $phone = $bot->message()->text;

        if (! $this->authService()->isValidPhoneFormat($phone)) {
            $bot->sendMessage(__('telegram.phone.wrong'));
            $this->next('receivePhone');

            return;
        }

        // Get or create client with new phone
        $client = $this->authService()->getOrCreateClient($phone);

        // Link current telegram account to this client
        $this->authService()->linkTelegramAccountToClient($bot->chatId(), $client);

        // Send OTP and get the code
        $otpCode = $this->authService()->sendOtp($phone);

        // Send OTP code via Telegram for testing
        $bot->sendMessage(__('telegram.otp.your_code', ['code' => $otpCode]));

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
            $this->start($bot);

            return;
        }

        if (! $this->authService()->verifyOtp($client->phone, $otp)) {
            $bot->sendMessage(__('telegram.otp.wrong'));
            $this->next('receiveOtp');

            return;
        }

        $bot->sendMessage(
            text: __('telegram.phone.changed'),
            reply_markup: ReplyKeyboards::home()
        );
        $this->end();
    }
}
