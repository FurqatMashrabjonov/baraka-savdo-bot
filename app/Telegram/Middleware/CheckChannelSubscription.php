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

        if (is_null($telegramAccount->channel_joined_at)){
            JoinChannelConversation::begin($bot);
        } else {
            $next($bot);
        }
    }
}
