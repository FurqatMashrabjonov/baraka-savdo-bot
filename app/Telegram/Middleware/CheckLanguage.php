<?php

namespace App\Telegram\Middleware;

use App\Models\TelegramAccount;
use App\Telegram\Conversations\AskLanguageConversation;
use SergiX44\Nutgram\Nutgram;

class CheckLanguage
{
    public function __invoke(Nutgram $bot, $next): void
    {
        $telegramAccount = TelegramAccount::query()
            ->where('chat_id', $bot->chatId())
            ->first();

        if (!$telegramAccount) return;

        if (is_null($telegramAccount->lang)) {
            AskLanguageConversation::begin($bot);
        } else {
            $next($bot);
        }
    }
}
