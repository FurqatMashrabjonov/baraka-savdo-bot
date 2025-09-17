<?php

namespace App\Telegram\Conversations;

use App\Models\TelegramAccount;
use App\Telegram\Keyboards\InlineKeyboards;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

class AskLanguageConversation extends Conversation
{
    public function start(Nutgram $bot)
    {
        $bot->sendMessage(
            text: 'Iltimos tilni tanlang',
            reply_markup: InlineKeyboards::languages()
        );
        $this->next('receiveLang');
    }

    public function receiveLang(Nutgram $bot)
    {
        $callbackData = $bot->callbackQuery()->data;
        if (!$callbackData || !str_starts_with($callbackData, 'set_lang:')) {
            $this->start($bot);
        }

        $lang = str_replace('set_lang:', '', $callbackData);

        $changed = TelegramAccount::query()
            ->where('chat_id', $bot->chatId())
            ->update([
                'lang' => $lang
            ]);

        if ($changed) {
            $bot->editMessageText(
                text: 'Til muvaffaqiyatli o\'rnatildi!',
                message_id: $bot->callbackQuery()->message->message_id,
            );


        } else {
            $bot->sendMessage('Xatolik yuz berdi, iltimos qayta urinib ko\'ring!');
            $this->start($bot);
            return;
        }
        $this->end();
    }
}
