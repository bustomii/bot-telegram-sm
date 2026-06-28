<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class TelegramLoginService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function verify(array $payload): void
    {
        $token = config('messaging.telegram.login_bot_token');
        if (! $token) {
            throw new InvalidArgumentException('Telegram login bot belum dikonfigurasi.');
        }

        $hash = (string) ($payload['hash'] ?? '');
        if ($hash === '') {
            throw new InvalidArgumentException('Hash Telegram tidak valid.');
        }

        $authDate = (int) ($payload['auth_date'] ?? 0);
        if ($authDate <= 0 || (time() - $authDate) > 86400) {
            throw new InvalidArgumentException('Sesi login Telegram sudah kedaluwarsa.');
        }

        $checkData = collect($payload)
            ->except('hash')
            ->map(fn ($value, $key) => "{$key}={$value}")
            ->sort()
            ->implode("\n");

        $secretKey = hash('sha256', $token, true);
        $calculated = hash_hmac('sha256', $checkData, $secretKey);

        if (! hash_equals($calculated, $hash)) {
            Log::warning('Telegram login hash mismatch');
            throw new InvalidArgumentException('Verifikasi Telegram gagal.');
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{id: int, first_name: string, last_name: ?string, username: ?string, photo_url: ?string}
     */
    public function extractUser(array $payload): array
    {
        return [
            'id' => (int) $payload['id'],
            'first_name' => (string) ($payload['first_name'] ?? 'Telegram User'),
            'last_name' => isset($payload['last_name']) ? (string) $payload['last_name'] : null,
            'username' => isset($payload['username']) ? (string) $payload['username'] : null,
            'photo_url' => isset($payload['photo_url']) ? (string) $payload['photo_url'] : null,
        ];
    }
}
