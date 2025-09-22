<?php

namespace App\Telegram\Conversations;

use App\Models\Parcel;
use App\Services\TelegramAuthService;
use App\Telegram\Keyboards\ReplyKeyboards;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class TrackNumberConversation extends Conversation
{
    private function authService(): TelegramAuthService
    {
        return app(TelegramAuthService::class);
    }

    public function start(Nutgram $bot, bool $isWrongFormat = false)
    {
        $message = $isWrongFormat
            ? __('telegram.track_number_texts.wrong')
            : __('telegram.track_number_texts.enter');

        $bot->sendMessage(
            text: $message,
            parse_mode: ParseMode::HTML,
            reply_markup: ReplyKeyboards::backToHome()
        );
        $this->next('receiveTrackNumber');
    }

    public function receiveTrackNumber(Nutgram $bot)
    {
        $trackNumber = trim($bot->message()->text);

        // Check if user wants to go back to home
        if (in_array($trackNumber, lang_all('telegram.back_to_home'))) {
            $this->end();
            $bot->sendMessage(
                text: __('telegram.back_to_home_message'),
                reply_markup: ReplyKeyboards::home()
            );

            return;
        }

        // Validate track number format (basic validation)
        if (! $this->isValidTrackNumber($trackNumber)) {
            $this->start($bot, true);

            return;
        }

        // Get current client
        $client = $this->authService()->getClientByChatId($bot->chatId());

        if (! $client) {
            $this->end();
            $bot->sendMessage(
                text: __('telegram.error_try_again'),
                reply_markup: ReplyKeyboards::home()
            );

            return;
        }

        // Check if track number already exists for this client
        $existingParcel = Parcel::where('track_number', $trackNumber)
            ->where('client_id', $client->id)
            ->first();

        if ($existingParcel) {
            $this->showParcelStatus($bot, $existingParcel);

            return;
        }

        // Create or find parcel and assign to client
        $parcel = Parcel::findOrCreateByTrackNumber($trackNumber, $client->id);

        $bot->sendMessage(
            text: __('telegram.track_number_texts.saved', ['track_number' => $trackNumber]),
            parse_mode: ParseMode::HTML,
            reply_markup: ReplyKeyboards::home()
        );

        // Show current status
        $this->showParcelStatus($bot, $parcel);
        $this->end();
    }

    private function isValidTrackNumber(string $trackNumber): bool
    {
        // Basic track number validation
        // Adjust this regex based on your track number format requirements
        return preg_match('/^[A-Z0-9]{8,20}$/', strtoupper($trackNumber));
    }

    private function showParcelStatus(Nutgram $bot, Parcel $parcel): void
    {
        $progressSteps = $parcel->getProgressSteps();
        $statusText = "📦 <b>Trek raqam:</b> {$parcel->track_number}\n";
        $statusText .= "📊 <b>Holati:</b> {$parcel->getStatusLabel()}\n\n";

        if ($parcel->weight) {
            $statusText .= "⚖️ <b>Og'irligi:</b> {$parcel->weight} kg\n";
        }

        if ($parcel->is_banned) {
            $statusText .= "⚠️ <b>Diqqat:</b> Ushbu buyum taqiqlangan\n";
        }

        $statusText .= "\n<b>Yetkazib berish jarayoni:</b>\n";

        foreach ($progressSteps as $step) {
            $icon = $step['completed'] ? '✅' : '⏳';
            $statusText .= "{$icon} {$step['label']}";

            if ($step['date']) {
                $statusText .= " - {$step['date']->format('d.m.Y H:i')}";
            }

            $statusText .= "\n";
        }

        $bot->sendMessage(
            text: $statusText,
            parse_mode: ParseMode::HTML
        );
    }
}
