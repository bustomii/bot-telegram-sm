<?php

namespace App\Services\Messaging\Providers;

use App\Contracts\Messaging\MessagingProviderContract;
use App\Models\MessagingAccount;
use RuntimeException;

class WhatsAppProvider implements MessagingProviderContract
{
    public function key(): string
    {
        return 'whatsapp';
    }

    public function validateCredentials(string $accountType, array $credentials): array
    {
        throw new RuntimeException('Provider WhatsApp belum tersedia.');
    }

    public function connect(MessagingAccount $account): void
    {
        throw new RuntimeException('Provider WhatsApp belum tersedia.');
    }

    public function disconnect(MessagingAccount $account): void {}

    public function handleWebhook(MessagingAccount $account, array $update): void {}

    public function sendText(MessagingAccount $account, string $chatId, string $text): bool
    {
        return false;
    }
}
