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

        if (! $telegramAccount) {
            $next($bot);

            return;
        }

        // If language is already set, skip this middleware forever
        if (! is_null($telegramAccount->lang)) {
            $next($bot);

            return;
        }

        // Only start language conversation if this is a command, not during other conversations
        if ($bot->message()?->text && str_starts_with($bot->message()->text, '/')) {
            AskLanguageConversation::begin($bot);
        } else {
            $next($bot);
        }
    }
}
