<?php

namespace App\Telegram\Conversations;

use App\Models\TelegramAccount;
use App\Telegram\Keyboards\InlineKeyboards;
use App\Telegram\Keyboards\ReplyKeyboards;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

class JoinChannelConversation extends Conversation
{
    protected string $channel;

    public function __construct()
    {
        $this->channel = '@'.config('telegram.channel_username');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function start(Nutgram $bot): void
    {
        $this->sendJoinMessage($bot);
        $this->next('checkJoined');
    }

    public function checkJoined(Nutgram $bot): void
    {
        if ($bot->callbackQuery() == null) {
            $this->next('checkJoined');

            return;
        }

        if ($bot->callbackQuery()?->data !== 'check_joined') {
            return;
        }

        $userId = $bot->userId();
        $chatMember = $bot->getChatMember(chat_id: $this->channel, user_id: $userId);
        $status = $chatMember->status->value ?? $chatMember->status;

        if (in_array($status, ['member', 'administrator', 'creator'])) {
            $this->markUserAsJoined($userId);
            $this->cleanUpMessage($bot);

            // Check if user is already completely registered
            if ($this->isUserLoggedIn($bot)) {
                $bot->sendMessage(
                    text: __('telegram.greeting'),
                    reply_markup: ReplyKeyboards::home()
                );
                $this->end(); // End conversation properly

                return;
            }

            // User joined channel but not fully registered
            $telegramAccount = $this->getUser($userId);
            if (! $this->isUserLoggedIn($bot)) {
                $this->end(); // End current conversation before starting new one
                ClientDetailInfoConversation::begin($bot);

                return;
            }
            // Check if user needs language selection
            $telegramAccount = $this->getUser($userId);
            if (is_null($telegramAccount->lang)) {
                $this->end();
                AskLanguageConversation::begin($bot);

                return;
            }
            // RegistrationFlowMiddleware will handle phone/name/address flow automatically

        } else {
            $this->sendJoinMessage(
                $bot,
                __('telegram.not_joined')
            );
        }
    }

    protected function sendJoinMessage(Nutgram $bot, ?string $text = null): void
    {
        $text ??= __('telegram.must_join');
        $bot->sendMessage(text: $text, reply_markup: InlineKeyboards::joinChannel());
    }

    protected function markUserAsJoined(int $userId): void
    {
        TelegramAccount::updateOrCreate(
            ['chat_id' => $userId],
            ['channel_joined_at' => now()]
        );
    }

    protected function isUserLoggedIn($bot): bool
    {
        $authService = app(\App\Services\TelegramAuthService::class);
        $client = $authService->getClientByChatId($bot->chatId());

        return $client && $client->isComplete();
    }

    protected function cleanUpMessage(Nutgram $bot): void
    {
        $bot->deleteMessage(
            chat_id: $bot->chatId(),
            message_id: $bot->callbackQuery()->message->message_id
        );
    }

    protected function getUser(int $userId): TelegramAccount
    {
        return TelegramAccount::firstOrNew(['chat_id' => $userId]);
    }
}
