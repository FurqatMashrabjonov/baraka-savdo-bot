<?php

namespace App\Telegram\Conversations;

use App\Models\Client;
use App\Models\Otp;
use App\Telegram\Keyboards\ReplyKeyboards;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class ClientDetailInfoConversation extends Conversation
{
    public function start(Nutgram $bot, bool $is_wrong_number_entry = false)
    {
        if ($is_wrong_number_entry) {
            $bot->sendMessage(__('telegram.phone.wrong'));
        } else {
            $bot->sendMessage(__('telegram.phone.enter'));
        }
        $this->next('receivePhone');
    }

    public function receivePhone(Nutgram $bot)
    {
        $phone = $bot->message()->text;
        if (!preg_match('/^\d{9}$/', $phone)) {
            $this->start($bot, true);
            return;
        }

        Client::query()->updateOrCreate([
            'chat_id' => $bot->chatId(),
        ], [
           'phone' => $phone
        ]);

        //generate opt and send it via EskizClient and save it to session
        $otp = rand(1000, 9999);

        Otp::query()->create([
            'phone' => $phone,
            'code' => $otp,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(5),
        ]);
        //for testing purpose we will send otp via bot message
        $bot->sendMessage(__('telegram.otp.your_code', ['code' => $otp]));
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

        if (!preg_match('/^\d{4}$/', $otp)) {
            $bot->sendMessage(__('telegram.otp.wrong'));
            $this->next('receiveOtp');
            return;
        }

        $phone = Client::query()->where('chat_id', $bot->chatId())->value('phone');
        if (!$phone) {
            $this->start($bot, true);
            return;
        }

        $otpEntry = Otp::query()
            ->where('code', $otp)
            ->where('phone', $phone)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otpEntry) {
            $bot->sendMessage(__('telegram.otp.wrong'));
            $this->next('receiveOtp');
            return;
        }

        if ($otpEntry->expires_at < now()) {
            $bot->sendMessage(__('telegram.otp.expired'));
            $this->next('receivePhone');
            return;
        }

        $otpEntry->delete();

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
        $full_name = $bot->message()->text;
        //make pattern letters also kirillic
        if (!preg_match('/^[a-zA-ZА-яёЁ\s]+$/u', $full_name)) {
            $bot->sendMessage(__('telegram.full_name.wrong'));
            $this->next('askFullName');
            return;
        }

        Client::query()->where('chat_id', $bot->chatId())->update([
            'full_name' => $full_name
        ]);

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

        Client::query()->where('chat_id', $bot->chatId())->update([
            'address' => $address
        ]);

        $client = Client::query()->where('chat_id', $bot->chatId())->first();

        $bot->sendMessage(
            __('telegram.registration.success') . "\n\n"
            . "<blockquote>"
            . __('telegram.registration.details', [
                'id' => $client->id,
                'name' => $client->full_name,
                'phone' => $client->phone,
                'address' => $client->address,
                'date' => $client->created_at->format('d.m.Y H:i'),
            ]) .
            "</blockquote>",
            parse_mode: ParseMode::HTML,
            reply_markup: ReplyKeyboards::home()
        );
        $this->end();
    }
}
