<?php

namespace App\Telegram\Commands;

use App\Models\Client;
use App\Telegram\Keyboards\ReplyKeyboards;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Handlers\Type\Command;

class StartCommand extends Command
{
    protected string $command = 'start';

    protected ?string $description = 'Botni ishga tushirish';

    public function handle(Nutgram $bot): void
    {
        $bot->sendMessage(
            text: __('telegram.greeting'),
            reply_markup: !$this->isUserLoggedIn($bot) ? ReplyKeyboards::login() : ReplyKeyboards::home()
        );
    }

    protected function isUserLoggedIn($bot): bool
    {
        $client = Client::query()->where('chat_id', $bot->chatId())->first();
        if (!$client) return false;

        return $client->phone != null && $client->full_name != null && $client->address != null;
    }
}
