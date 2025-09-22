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

        // Check if track number already exists globally (unique constraint)
        $existingParcel = Parcel::where('track_number', $trackNumber)->first();

        if ($existingParcel) {
            // If parcel has no client assigned, assign it to current client
            if (is_null($existingParcel->client_id)) {
                $existingParcel->update(['client_id' => $client->id]);

                $bot->sendMessage(
                    text: __('telegram.track_number_texts.order_created', ['track_number' => $trackNumber]),
                    parse_mode: ParseMode::HTML,
                    reply_markup: ReplyKeyboards::home()
                );
            }
            // If parcel belongs to current client, show already exists message
            elseif ($existingParcel->client_id === $client->id) {
                $bot->sendMessage(
                    text: __('telegram.track_number_texts.already_exists'),
                    parse_mode: ParseMode::HTML,
                    reply_markup: ReplyKeyboards::home()
                );
            } else {
                // If parcel belongs to another client, show error message
                $bot->sendMessage(
                    text: __('telegram.track_number_texts.taken'),
                    parse_mode: ParseMode::HTML,
                    reply_markup: ReplyKeyboards::home()
                );
            }
            $this->end();

            return;
        }

        // Create new parcel and assign to client
        $parcel = Parcel::create([
            'track_number' => $trackNumber,
            'client_id' => $client->id,
            'status' => \App\Enums\ParcelStatus::CREATED,
        ]);

        $bot->sendMessage(
            text: __('telegram.track_number_texts.order_created', ['track_number' => $trackNumber]),
            parse_mode: ParseMode::HTML,
            reply_markup: ReplyKeyboards::home()
        );

        $this->end();
    }

    private function isValidTrackNumber(string $trackNumber): bool
    {
        // Basic track number validation
        // Adjust this regex based on your track number format requirements
        return preg_match('/^[A-Z0-9]{8,20}$/', strtoupper($trackNumber));
    }
}
