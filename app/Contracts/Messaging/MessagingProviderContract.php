<?php

namespace App\Contracts\Messaging;

use App\Models\MessagingAccount;

interface MessagingProviderContract
{
    public function key(): string;

    /**
     * @param  array<string, mixed>  $credentials
     * @return array{external_id: string, username: ?string, display_name: string, metadata?: array<string, mixed>}
     */
    public function validateCredentials(string $accountType, array $credentials): array;

    public function connect(MessagingAccount $account): void;

    public function disconnect(MessagingAccount $account): void;

    /**
     * @param  array<string, mixed>  $update
     */
    public function handleWebhook(MessagingAccount $account, array $update): void;

    public function sendText(MessagingAccount $account, string $chatId, string $text): bool;
}
