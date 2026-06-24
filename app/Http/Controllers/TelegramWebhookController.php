<?php

namespace App\Http\Controllers;

use App\Services\ConversationHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\BotSetting;

class TelegramWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $settings = BotSetting::current();
        $secret = $request->header('X-Telegram-Bot-Api-Secret-Token');

        if ($settings->telegram_webhook_secret && $secret !== $settings->telegram_webhook_secret) {
            return response('Unauthorized', 401);
        }

        $update = $request->all();
        if (! empty($update)) {
            app(ConversationHandler::class)->handle($update);
        }

        return response('OK', 200);
    }
}
