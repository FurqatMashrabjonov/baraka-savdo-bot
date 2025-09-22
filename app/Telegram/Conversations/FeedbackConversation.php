<?php

namespace App\Telegram\Conversations;

use App\Models\Feedback;
use App\Models\TelegramAccount;
use App\Telegram\Keyboards\ReplyKeyboards;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class FeedbackConversation extends Conversation
{
    public function start(Nutgram $bot)
    {
        $bot->sendMessage(
            text: __('telegram.feedback_texts.instructions'),
            parse_mode: ParseMode::HTML,
            reply_markup: ReplyKeyboards::backToHome()
        );
        $this->next('receiveFeedback');
    }

    public function receiveFeedback(Nutgram $bot)
    {
        $message = $bot->message()->text;

        // Check if user wants to go back to home
        if (in_array($message, lang_all('telegram.back_to_home'))) {
            $this->end();
            $bot->sendMessage(
                text: __('telegram.back_to_home_message'),
                reply_markup: ReplyKeyboards::home()
            );

            return;
        }

        // Store feedback in database
        $telegramAccount = TelegramAccount::where('chat_id', $bot->chatId())->first();

        if ($telegramAccount) {
            Feedback::create([
                'telegram_account_id' => $telegramAccount->id,
                'message' => $message,
                'status' => 'pending',
            ]);

            $this->end();
            $bot->sendMessage(
                text: __('telegram.feedback_texts.received'),
                reply_markup: ReplyKeyboards::home()
            );
        } else {
            $this->end();
            $bot->sendMessage(
                text: __('telegram.error_try_again'),
                reply_markup: ReplyKeyboards::home()
            );
        }
    }
}
