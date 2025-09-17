<?php

namespace App\Telegram\Middleware;

use App\Models\TelegramAccount;
use SergiX44\Nutgram\Nutgram;

class RegisterClient
{
    public function __invoke(Nutgram $bot, $next): void
    {
        $telegramAccount = TelegramAccount::query()
            ->where('chat_id', $bot->chatId())
            ->first();

        if (!$telegramAccount) {
            TelegramAccount::query()->create([
                'chat_id' => $bot->chatId(),
                'first_name' => $bot->user()->first_name,
                'last_name' => $bot->user()->last_name,
                'username' => $bot->user()->username,
            ]);
        }

        $next($bot);
    }
}
