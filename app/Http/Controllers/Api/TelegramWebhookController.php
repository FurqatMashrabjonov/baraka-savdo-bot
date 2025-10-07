<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SergiX44\Nutgram\Nutgram;

class TelegramWebhookController extends Controller
{
    public function __invoke(Nutgram $bot)
    {
        try {
            $bot->run();
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            Log::error($e->getMessage());
        } finally {
            return response()->noContent();
        }
    }
}
