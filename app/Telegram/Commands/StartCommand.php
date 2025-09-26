<?php

namespace App\Telegram\Commands;

use App\Models\TelegramAccount;
use App\Services\TelegramAuthService;
use App\Telegram\Conversations\AskLanguageConversation;
use App\Telegram\Conversations\AskPhoneConversation;
use App\Telegram\Conversations\JoinChannelConversation;
use App\Telegram\Keyboards\ReplyKeyboards;
use SergiX44\Nutgram\Handlers\Type\Command;
use SergiX44\Nutgram\Nutgram;

class StartCommand extends Command
{
    protected string $command = 'start';

    protected ?string $description = 'Botni ishga tushirish';

    public function handle(Nutgram $bot): void
    {
        logger()->info('StartCommand - begin', ['chat_id' => $bot->chatId()]);

        // Check if user is already completely registered
        if ($this->isUserLoggedIn($bot)) {
            logger()->info('StartCommand - user already logged in');
            $authService = app(TelegramAuthService::class);
            $client = $authService->getClientByChatId($bot->chatId());

            $bot->sendMessage(
                text: __('telegram.greeting'),
                reply_markup: ReplyKeyboards::home($client)
            );

            return;
        }

        // Check channel subscription first
        if (! $this->hasJoinedChannel($bot)) {
            logger()->info('StartCommand - user has not joined channel');
            JoinChannelConversation::begin($bot);

            return;
        }

        // Check language selection second
        if (! $this->hasSelectedLanguage($bot)) {
            logger()->info('StartCommand - user has not selected language');
            AskLanguageConversation::begin($bot);

            return;
        }

        // Finally, start phone registration
        logger()->info('StartCommand - starting phone conversation');
        $bot->sendMessage(text: __('telegram.greeting'));
        AskPhoneConversation::begin($bot);
    }

    protected function isUserLoggedIn(Nutgram $bot): bool
    {
        $authService = app(TelegramAuthService::class);
        $client = $authService->getClientByChatId($bot->chatId());

        return $client && $client->isComplete();
    }

    protected function hasJoinedChannel(Nutgram $bot): bool
    {
        $telegramAccount = TelegramAccount::where('chat_id', $bot->chatId())->first();

        return $telegramAccount && ! is_null($telegramAccount->channel_joined_at);
    }

    protected function hasSelectedLanguage(Nutgram $bot): bool
    {
        $telegramAccount = TelegramAccount::where('chat_id', $bot->chatId())->first();

        return $telegramAccount && ! is_null($telegramAccount->lang);
    }
}
