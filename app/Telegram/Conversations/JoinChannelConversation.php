<?php

namespace App\Telegram\Conversations;

use App\Models\TelegramAccount;
use App\Telegram\Keyboards\InlineKeyboards;
use Psr\SimpleCache\InvalidArgumentException;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

class JoinChannelConversation extends Conversation
{
    protected string $channel;

    public function __construct()
    {
        $this->channel = '@' . config('telegram.channel_username');
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
        if ($bot->callbackQuery()?->data !== 'check_joined') {
            return;
        }

        $userId = $bot->userId();
        $chatMember = $bot->getChatMember(chat_id: $this->channel, user_id: $userId);
        $status = $chatMember->status->value ?? $chatMember->status;

        if (in_array($status, ['member', 'administrator', 'creator'])) {
            $this->markUserAsJoined($userId);
            $this->cleanUpMessage($bot);
            $this->end();

            if (is_null($this->getUser($userId)->lang)) {
                AskLanguageConversation::begin($bot);
            }
        } else {
            $this->sendJoinMessage(
                $bot,
                '❌ Siz kanalga obuna bo\'lmagansiz. Iltimos, obuna bo‘ling va keyin "Tasdiqlash" tugmasini bosing.'
            );
        }
    }

    protected function sendJoinMessage(Nutgram $bot, string $text = null): void
    {
        $text ??= '👋 Assalom alaykum! Botdan foydalanish uchun iltimos kanalimizga obuna bo‘ling!';
        $bot->sendMessage(text: $text, reply_markup: InlineKeyboards::joinChannel());
    }

    protected function markUserAsJoined(int $userId): void
    {
        TelegramAccount::updateOrCreate(
            ['chat_id' => $userId],
            ['channel_joined_at' => now()]
        );
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
