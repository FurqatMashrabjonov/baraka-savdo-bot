<?php

namespace App\Telegram\Middleware;

use App\Models\TelegramAccount;
use Illuminate\Support\Facades\App;
use SergiX44\Nutgram\Nutgram;

class SetLangMiddleware
{
    public function __invoke(Nutgram $bot, $next): void
    {
        $telegramAccount = TelegramAccount::query()
            ->where('chat_id', $bot->chatId())
            ->first();

        if (is_null($telegramAccount)) {
            // default language is uz
            App::setLocale('uz');
        } else {
            App::setLocale($telegramAccount->lang ?? 'uz');
        }

        $next($bot);
    }
}
