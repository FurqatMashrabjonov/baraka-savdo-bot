<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Telegram\Commands\StartCommand;
use App\Telegram\Conversations\AskLanguageConversation;
use App\Telegram\Conversations\ClientDetailInfoConversation;
use App\Telegram\Middleware\CheckChannelSubscription;
use App\Telegram\Middleware\CheckLanguage;
use App\Telegram\Middleware\RegisterClient;
use App\Telegram\Middleware\SetLangMiddleware;
use SergiX44\Nutgram\Nutgram;

/*
|--------------------------------------------------------------------------
| Nutgram Handlers
|--------------------------------------------------------------------------
|
| Here is where you can register telegram handlers for Nutgram. These
| handlers are loaded by the NutgramServiceProvider. Enjoy!
|
*/

//global middelwares to check language, channel subscription.

$bot->middlewares([
    RegisterClient::class,
    SetLangMiddleware::class
]);

$bot->onCommand('start', StartCommand::class)
    ->middleware(CheckLanguage::class)
    ->middleware(CheckChannelSubscription::class);
$bot->onCommand('lang', function (Nutgram $bot) {
    AskLanguageConversation::begin($bot);
})->description('Tilni o\'zgartirish');

$bot->onText('^(' . implode('|', array_map('preg_quote', lang_all('telegram.login'))) . ')$', ClientDetailInfoConversation::class);

$bot->onException(function (Nutgram $bot, Throwable $exception) {
    if ($bot->chatId() == 778912691)
        $bot->sendMessage(
            text: "Xatolik yuz berdi: {$exception->getMessage()}");
});
