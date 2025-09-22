<?php

namespace App\Telegram\Middleware;

use App\Services\TelegramAuthService;
use App\Telegram\Conversations\ClientDetailInfoConversation;
use SergiX44\Nutgram\Nutgram;

class CheckUserEnteredRequiredData
{
    public function __invoke(Nutgram $bot, $next): void
    {
        // Skip check for admin users
        if ($bot->user()?->id === 123456789) {
            $next($bot);

            return;
        }

        // CRITICAL FIX: Skip this middleware if user is already in any conversation
        // This prevents restarting conversations when user sends phone/otp/etc
        if ($bot->message()?->text && ! str_starts_with($bot->message()->text, '/')) {
            $next($bot);

            return;
        }

        $authService = app(TelegramAuthService::class);
        $client = $authService->getClientByChatId($bot->chatId());

        // If no client is linked or client data is incomplete, start registration
        if (! $client || ! $client->isComplete()) {
            ClientDetailInfoConversation::begin($bot);

            return;
        }

        $next($bot);
    }
}
