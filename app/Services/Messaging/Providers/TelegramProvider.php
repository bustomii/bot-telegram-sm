<?php

namespace App\Services\Messaging\Providers;

use App\Contracts\Messaging\MessagingProviderContract;
use App\Models\MessagingAccount;
use App\Services\Messaging\AutoReplyEngine;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

class TelegramProvider implements MessagingProviderContract
{
    public function key(): string
    {
        return 'telegram';
    }

    public function validateCredentials(string $accountType, array $credentials): array
    {
        return match ($accountType) {
            MessagingAccount::TYPE_BOT => $this->validateBotToken($credentials),
            MessagingAccount::TYPE_USER => throw new RuntimeException('Koneksi akun Telegram user akan segera hadir. Gunakan Bot Token untuk saat ini.'),
            default => throw new InvalidArgumentException('Unsupported Telegram account type.'),
        };
    }

    public function connect(MessagingAccount $account): void
    {
        if ($account->account_type !== MessagingAccount::TYPE_BOT) {
            throw new RuntimeException('Hanya bot Telegram yang didukung saat ini.');
        }

        $token = $account->credentials['bot_token'] ?? null;
        if (! $token) {
            throw new RuntimeException('Bot token tidak ditemukan.');
        }

        $webhookUrl = $account->webhookUrl();
        $response = Http::timeout(30)->post($this->apiUrl($token, 'setWebhook'), array_filter([
            'url' => $webhookUrl,
            'secret_token' => $account->webhook_secret,
            'allowed_updates' => ['message'],
        ]));

        $body = $response->json();
        if (! ($body['ok'] ?? false)) {
            throw new RuntimeException($body['description'] ?? 'Gagal mengatur webhook Telegram.');
        }

        $account->update([
            'status' => MessagingAccount::STATUS_CONNECTED,
            'status_message' => null,
            'connected_at' => now(),
            'metadata' => array_merge($account->metadata ?? [], [
                'webhook_url' => $webhookUrl,
            ]),
        ]);
    }

    public function disconnect(MessagingAccount $account): void
    {
        $token = $account->credentials['bot_token'] ?? null;
        if ($token) {
            Http::post($this->apiUrl($token, 'deleteWebhook'));
        }

        $account->update([
            'status' => MessagingAccount::STATUS_DISCONNECTED,
            'status_message' => 'Webhook dihapus.',
        ]);
    }

    public function handleWebhook(MessagingAccount $account, array $update): void
    {
        $message = $update['message'] ?? null;
        if (! is_array($message)) {
            return;
        }

        $text = trim((string) ($message['text'] ?? ''));
        $chatId = (string) ($message['chat']['id'] ?? '');

        if ($text === '' || $chatId === '') {
            return;
        }

        app(AutoReplyEngine::class)->processIncoming($account, $update, $chatId, $text);
    }

    public function sendText(MessagingAccount $account, string $chatId, string $text): bool
    {
        $token = $account->credentials['bot_token'] ?? null;
        if (! $token) {
            return false;
        }

        $response = Http::post($this->apiUrl($token, 'sendMessage'), [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);

        return (bool) ($response->json('ok') ?? false);
    }

    /**
     * @param  array<string, mixed>  $credentials
     * @return array{external_id: string, username: ?string, display_name: string, metadata?: array<string, mixed>}
     */
    private function validateBotToken(array $credentials): array
    {
        $token = trim((string) ($credentials['bot_token'] ?? ''));
        if ($token === '') {
            throw new InvalidArgumentException('Bot token wajib diisi.');
        }

        $response = Http::timeout(15)->get($this->apiUrl($token, 'getMe'));
        $body = $response->json();

        if (! ($body['ok'] ?? false)) {
            throw new InvalidArgumentException($body['description'] ?? 'Bot token tidak valid.');
        }

        $bot = $body['result'] ?? [];

        return [
            'external_id' => (string) ($bot['id'] ?? ''),
            'username' => $bot['username'] ?? null,
            'display_name' => trim(($bot['first_name'] ?? 'Bot').' '.($bot['last_name'] ?? '')),
            'metadata' => [
                'can_join_groups' => $bot['can_join_groups'] ?? null,
                'supports_inline_queries' => $bot['supports_inline_queries'] ?? null,
            ],
        ];
    }

    private function apiUrl(string $token, string $method): string
    {
        return "https://api.telegram.org/bot{$token}/{$method}";
    }
}
