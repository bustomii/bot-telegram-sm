<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\MessagingAccount;
use App\Models\MessagingProvider;
use App\Services\Messaging\MessagingManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MessagingWebhookController extends Controller
{
    public function __construct(
        private readonly MessagingManager $messagingManager,
    ) {}

    public function __invoke(Request $request, string $provider, MessagingAccount $account): Response
    {
        $providerModel = MessagingProvider::query()->where('key', $provider)->first();
        if (! $providerModel || $account->messaging_provider_id !== $providerModel->id) {
            return response('Not Found', 404);
        }

        if ($account->status !== MessagingAccount::STATUS_CONNECTED) {
            return response('Account inactive', 422);
        }

        $secret = $request->header('X-Telegram-Bot-Api-Secret-Token');
        if ($account->webhook_secret && $secret !== $account->webhook_secret) {
            return response('Unauthorized', 401);
        }

        $update = $request->all();
        if ($update !== []) {
            $this->messagingManager->forAccount($account)->handleWebhook($account, $update);
        }

        return response('OK', 200);
    }
}
