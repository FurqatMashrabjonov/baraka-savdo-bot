<?php

namespace App\Telegram\Conversations;

use App\Models\Client;
use App\Models\TelegramAccount;
use App\Telegram\Keyboards\InlineKeyboards;
use App\Telegram\Keyboards\ReplyKeyboards;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;

class AskLanguageConversation extends Conversation
{
    public function start(Nutgram $bot)
    {
        $bot->sendMessage(
            text: __('telegram.choose_language'),
            reply_markup: InlineKeyboards::languages()
        );
        $this->next('receiveLang');
    }

    public function receiveLang(Nutgram $bot)
    {
        $callbackData = $bot->callbackQuery()->data;
        if (! $callbackData || ! str_starts_with($callbackData, 'set_lang:')) {
            $this->start($bot);

            return;
        }

        $lang = str_replace('set_lang:', '', $callbackData);
        $telegramAccount = TelegramAccount::query()
            ->where('chat_id', $bot->chatId())->first();

        $changed = $telegramAccount->update([
            'lang' => $lang,
        ]);

        \App::setLocale($lang);

        // Use the new auth service to get client properly
        $authService = app(\App\Services\TelegramAuthService::class);
        $client = $authService->getClientByChatId($bot->chatId());

        if ($changed) {
            $bot->message()->delete();

            if (is_null($telegramAccount->channel_joined_at)) {
                $this->end();
                JoinChannelConversation::begin($bot);

                return;
            }

            $bot->sendMessage(
                text: __('telegram.language_changed_successfully'),
                reply_markup: ($client && $client->isComplete()) ? ReplyKeyboards::home() : ReplyKeyboards::login()
            );
        } else {
            $bot->sendMessage(__('telegram.error_try_again'));
            $this->start($bot);

            return;
        }

        $this->end();
    }
}
