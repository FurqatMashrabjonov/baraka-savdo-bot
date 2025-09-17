<?php

use App\Http\Controllers\Api\TelegramWebhookController;
use App\Integrations\EskizSmsClient;
use Illuminate\Support\Facades\Route;

Route::post('telegram/webhook',  TelegramWebhookController::class);

Route::get('sms', function (){
    $service = new EskizSmsClient(
        email: 'mashrabjonovfurqat@gmail.com',
        password: "Z8qk7TdOScTFehkvBM3ZxrDaURK9N9zd8tFlusCW",
        tokenLifetime: 60,
        sender: 'Baraka Pochta'
    );

    return $service->send('998994843507', 'Bu Eskiz dan test');
});
