<?php

namespace App\Telegram\Middleware;

use App\Models\TelegramAccount;
use App\Telegram\Conversations\JoinChannelConversation;
use SergiX44\Nutgram\Nutgram;

class CheckChannelSubscription
{
    public function __invoke(Nutgram $bot, $next): void
    {
        $telegramAccount = TelegramAccount::query()
            ->where('chat_id', $bot->chatId())
            ->first();

        // Skip if no telegram account exists
        if (! $telegramAccount) {
            $next($bot);

            return;
        }

        // If already checked channel subscription (has timestamp), skip this middleware forever
        if (! is_null($telegramAccount->channel_joined_at)) {
            $next($bot);

            return;
        }

        // Only start channel conversation if this is a command, not during other conversations
        if ($bot->message()?->text && str_starts_with($bot->message()->text, '/')) {
            JoinChannelConversation::begin($bot);
        } else {
            $next($bot);
        }
    }
}
