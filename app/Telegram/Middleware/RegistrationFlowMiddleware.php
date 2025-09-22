<?php

namespace App\Telegram\Middleware;

use App\Services\TelegramAuthService;
use App\Telegram\Conversations\AskAddressConversation;
use App\Telegram\Conversations\AskNameConversation;
use App\Telegram\Conversations\AskPhoneConversation;
use SergiX44\Nutgram\Nutgram;

class RegistrationFlowMiddleware
{
    public function __invoke(Nutgram $bot, $next): void
    {
        // Only check on commands, not during conversations
        if ($bot->message()?->text && ! str_starts_with($bot->message()->text, '/')) {
            $next($bot);

            return;
        }

        $authService = app(TelegramAuthService::class);
        $client = $authService->getClientByChatId($bot->chatId());

        // No client linked - start phone collection
        if (! $client) {
            AskPhoneConversation::begin($bot);

            return;
        }

        // Client exists but missing data - continue from where left off
        if (! $client->full_name) {
            AskNameConversation::begin($bot);

            return;
        }

        if (! $client->address) {
            AskAddressConversation::begin($bot);

            return;
        }

        // All data complete - proceed with normal flow
        $next($bot);
    }
}
