<?php

/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Telegram\Commands\StartCommand;
use App\Telegram\Conversations\AskLanguageConversation;
use App\Telegram\Conversations\ClientDetailInfoConversation;
use App\Telegram\Conversations\FeedbackConversation;
use App\Telegram\Conversations\OrderPaymentConversation;
use App\Telegram\Conversations\ProfileConversation;
use App\Telegram\Conversations\TrackNumberConversation;
use App\Telegram\Middleware\CheckChannelSubscription;
use App\Telegram\Middleware\CheckLanguage;
use App\Telegram\Middleware\RegisterClient;
use App\Telegram\Middleware\RegistrationFlowMiddleware;
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

// global middelwares to check language, channel subscription.

$bot->middlewares([
    RegisterClient::class,
    SetLangMiddleware::class,
    CheckLanguage::class,
    RegistrationFlowMiddleware::class, // Back by popular demand!
]);

$bot->onCommand('start', StartCommand::class)
    ->middleware(CheckChannelSubscription::class);

$bot->onCommand('lang', function (Nutgram $bot) {
    AskLanguageConversation::begin($bot);
})->description('Tilni o\'zgartirish');

// Text Handlers
$bot->onText('^('.implode('|', array_map('preg_quote', lang_all('telegram.login'))).')$', function (Nutgram $bot) {
    ClientDetailInfoConversation::begin($bot);
});

// --- Main Menu Buttons ---
$bot->onText('^('.implode('|', array_map('preg_quote', lang_all('telegram.profile'))).')$', ProfileConversation::class);

// Track number button handler
$bot->onText('^('.implode('|', array_map('preg_quote', lang_all('telegram.track_number'))).')$', function (Nutgram $bot) {
    TrackNumberConversation::begin($bot);
});

// Order payment button handler - show all parcels
$bot->onText('^('.implode('|', array_map('preg_quote', lang_all('telegram.order_payment'))).')$', function (Nutgram $bot) {
    OrderPaymentConversation::begin($bot);
});

// Feedback button handler
$bot->onText('^('.implode('|', array_map('preg_quote', lang_all('telegram.feedback'))).')$', function (Nutgram $bot) {
    FeedbackConversation::begin($bot);
});

// Back to home button handler
$bot->onText('^('.implode('|', array_map('preg_quote', lang_all('telegram.back_to_home'))).')$', function (Nutgram $bot) {
    $bot->sendMessage(
        text: __('telegram.back_to_home_message'),
        reply_markup: \App\Telegram\Keyboards\ReplyKeyboards::home()
    );
});

$bot->onException(function (Nutgram $bot, Throwable $exception) {
    $bot->sendMessage(
        text: "Xatolik yuz berdi: {$exception->getMessage()}");
});
